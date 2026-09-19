"""Shortlist metadata, then read only explicitly selected, unchanged sources."""

from contextlib import contextmanager
import fcntl
from functools import lru_cache
import hashlib
import json
from pathlib import Path
import re
import tempfile

import tiktoken

from search_index import SearchIndexError, compact_json


def reference_id(document):
    value = document["path"] + "\0" + document["sha256"]
    return hashlib.sha256(value.encode()).hexdigest()[:12]


@lru_cache(maxsize=1)
def encoding():
    return tiktoken.get_encoding("o200k_base")


def source(document):
    return {"id": reference_id(document), "path": document["path"], "content": document["raw"]}


def token_count(value):
    return len(encoding().encode(compact_json(value)))


def description(document):
    """References declare their applicability in the first paragraph after the H1."""
    body = re.split(r"^# .+\n", document["raw"], maxsplit=1, flags=re.MULTILINE)[-1].lstrip()
    paragraph = body.split("\n\n", 1)[0]
    if not paragraph or any(line.lstrip().startswith(("#", "```", "~~~")) for line in paragraph.splitlines()):
        raise SearchIndexError(f"Reference needs an applicability paragraph after its H1: {document['path']}")
    summary = " ".join(paragraph.split())
    if len(encoding().encode(summary)) > 80:
        raise SearchIndexError(f"Applicability paragraph exceeds 80 tokens: {document['path']}")
    return summary


class ReferenceSession:
    """Receipts belong to one consumer context; no task text or source bodies are saved."""

    def __init__(self, root, path):
        self.root = Path(root).resolve()
        self.path = Path(path).expanduser().absolute()
        if self.path.is_symlink() or self.path.resolve().is_relative_to(self.root):
            raise ValueError("Use a regular session file outside the skill directory")
        self.state = {
            "version": 1, "catalog": str(self.root), "candidates": {},
            "seen": {}, "source_tokens": 0,
        }

    @contextmanager
    def locked(self):
        self.path.parent.mkdir(parents=True, exist_ok=True)
        with self.path.with_name(self.path.name + ".lock").open("a+b") as lock:
            fcntl.flock(lock, fcntl.LOCK_EX)
            if self.path.exists():
                self.state = json.loads(self.path.read_text())
                self._validate()
            yield self

    def _validate(self):
        value = self.state
        if (not isinstance(value, dict) or set(value) != {
            "version", "catalog", "candidates", "seen", "source_tokens"
        } or type(value["version"]) is not int or value["version"] != 1
                or value["catalog"] != str(self.root)):
            raise ValueError("Session belongs to another catalog or has an invalid format; use a new session")
        if (not isinstance(value["source_tokens"], int) or isinstance(value["source_tokens"], bool)
                or value["source_tokens"] < 0 or not isinstance(value["candidates"], dict)
                or not isinstance(value["seen"], dict)):
            raise ValueError("Invalid session receipts")
        for identifier, item in value["candidates"].items():
            if (not isinstance(item, dict) or set(item) != {"path", "sha256"}
                    or not self._receipt(item["path"], item["sha256"])
                    or reference_id(item) != identifier):
                raise ValueError("Invalid session candidate")
        if not all(self._receipt(path, digest) for path, digest in value["seen"].items()):
            raise ValueError("Invalid session receipts")

    @staticmethod
    def _receipt(path, digest):
        return (isinstance(path, str) and path.startswith("references/")
                and path.endswith(".md") and ".." not in Path(path).parts
                and isinstance(digest, str) and re.fullmatch(r"[0-9a-f]{64}", digest))

    def save(self):
        # Replace atomically; a failed read/search leaves previous receipts intact.
        with tempfile.NamedTemporaryFile(mode="w", dir=self.path.parent,
                                         prefix=self.path.name + ".", delete=False) as handle:
            handle.write(compact_json(self.state))
        Path(handle.name).replace(self.path)

    def shortlist(self, documents, ranking, limit=5):
        if not isinstance(limit, int) or isinstance(limit, bool) or not 1 <= limit <= 10:
            raise ValueError("Candidate limit must be between 1 and 10")
        identifiers = [reference_id(doc) for doc in documents]
        if len(set(identifiers)) != len(identifiers):
            raise SearchIndexError("Reference ID collision")
        cards = []
        for index in list(dict.fromkeys(ranking))[:limit]:
            doc = documents[index]
            identifier = reference_id(doc)
            cards.append({
                "id": identifier, "title": doc["title"], "summary": description(doc),
                "tokens": token_count(source(doc)),
                "read": self.state["seen"].get(doc["path"]) == doc["sha256"],
            })
            self.state["candidates"][identifier] = {
                "path": doc["path"], "sha256": doc["sha256"],
            }
        return {"candidates": cards, "remaining_candidates": max(0, len(set(ranking)) - len(cards))}

    def _read_reference(self, path):
        try:
            target = (self.root / path).resolve(strict=True)
            if not target.is_relative_to(self.root / "references"):
                raise SearchIndexError(f"Selected reference resolves outside the references directory: {path}; search again")
            content = target.read_bytes()
            raw = content.decode("utf-8")
        except (OSError, UnicodeError) as error:
            raise SearchIndexError(f"Selected reference changed or was deleted, or cannot be read as UTF-8: {path}; search again") from error
        return {"path": path, "raw": raw, "sha256": hashlib.sha256(content).hexdigest()}

    def read(self, identifiers, budget=4000, repeat=False):
        if not isinstance(budget, int) or isinstance(budget, bool) or not 1 <= budget <= 4000:
            raise ValueError("Read budget must be between 1 and 4000")
        requested = list(dict.fromkeys(identifiers))
        if not requested:
            raise ValueError("Select at least one candidate ID")
        selected = []
        for identifier in requested:
            previous = self.state["candidates"].get(identifier)
            if previous is None:
                raise ValueError(f"Unknown candidate {identifier}; search first")
            doc = self._read_reference(previous["path"])
            if doc["sha256"] != previous["sha256"]:
                raise SearchIndexError(f"Selected reference changed or was deleted: {previous['path']}; search again")
            selected.append(doc)
        references, already_read, blocked = [], [], []
        used = 0
        for doc in selected:
            piece = source(doc)
            if not repeat and self.state["seen"].get(doc["path"]) == doc["sha256"]:
                already_read.append(piece["id"])
                continue
            tokens = token_count(piece)
            if used + tokens > budget:
                blocked.append({"id": piece["id"], "tokens": tokens})
                continue
            references.append(piece)
            used += tokens
        # Do not acknowledge reads from a snapshot edited while preparing the response.
        if any(self._read_reference(doc["path"])["sha256"] != doc["sha256"] for doc in selected):
            raise SearchIndexError("Selected references changed during reading; search again")
        documents = {doc["path"]: doc for doc in selected}
        for piece in references:
            self.state["seen"][piece["path"]] = documents[piece["path"]]["sha256"]
        self.state["source_tokens"] += used
        return {
            "references": references, "already_read": already_read, "blocked": blocked,
            "source_tokens": used, "session_source_tokens": self.state["source_tokens"],
        }
