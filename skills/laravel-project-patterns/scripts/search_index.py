"""Incremental local index for the benchmarked whole-document hybrid search."""

from __future__ import annotations

from collections import Counter
from collections.abc import Callable, Mapping
from contextlib import closing, contextmanager
import fcntl
import hashlib
import json
from pathlib import Path
import re
import sqlite3
from typing import Any

import numpy as np


SCHEMA_VERSION = 1
DIMENSIONS = 1024
BATCH_SIZE = 8
NAVIGATION_EXCLUSIONS = (
    "SKILL.md",
    "references/MAP.md",
    "references/README.md",
    "references/context-resolver.md",
    "references/core/README.md",
    "references/maps/core.md",
    "references/maps/database-and-resources.md",
    "references/maps/project-and-application.md",
    "references/maps/tests.md",
)
QUERY_PREFIX = (
    "Instruct: Retrieve applicable Laravel convention rules and examples for the "
    "supplied task and code.\nQuery: "
)
STOP_WORDS = frozenset(
    "the a an and or for to of in on with this that is are be should when it its as "
    "at from by not only using use el la los las un una y o de del al en con por "
    "para que se es son debe deben como cuando sin su sus este esta esto".split()
)
FTS_SCHEMA = (
    'CREATE VIRTUAL TABLE docs USING fts5(title,body,'
    'tokenize="porter unicode61 remove_diacritics 2")'
)


class SearchIndexError(RuntimeError):
    """The index cannot provide a complete, consistent hybrid search."""


def compact_json(value: Any) -> str:
    return json.dumps(value, ensure_ascii=False, separators=(",", ":"))


def _title(raw: str, path: str) -> str:
    fenced = False
    for line in raw.splitlines():
        if line.lstrip().startswith("```"):
            fenced = not fenced
        match = re.match(r"^(#{1,6})\s+(.+?)\s*$", line) if not fenced else None
        if match and len(match[1]) == 1:
            return match[2]
    raise SearchIndexError(f"Reference has no top-level Markdown heading: {path}")


def corpus(skill_root: str | Path) -> list[dict[str, Any]]:
    """Read reference Markdown, preserving the benchmark's paths and preprocessing."""
    root = Path(skill_root).resolve()
    references = root / "references"
    if not references.is_dir():
        raise SearchIndexError(f"Reference directory does not exist: {references}")
    documents = []
    for source in sorted(references.rglob("*.md"), key=lambda path: path.relative_to(root).as_posix()):
        path = source.relative_to(root).as_posix()
        if path in NAVIGATION_EXCLUSIONS:
            continue
        if not source.resolve().is_relative_to(root):
            raise SearchIndexError(f"Reference resolves outside the skill directory: {path}")
        try:
            content = source.read_bytes()
            raw = content.decode("utf-8")
        except (OSError, UnicodeError) as error:
            raise SearchIndexError(f"Cannot read reference {path}: {error}") from error
        body = raw.split("\n## Related References")[0].strip()
        searchable = re.sub(r"\[([^\]]+)\]\([^)]+\)", r"\1", body)
        searchable = re.sub(r"([a-z])([A-Z])", r"\1 \2", searchable)
        documents.append(
            {
                "id": len(documents),
                "path": path,
                "title": _title(raw, path),
                "raw": raw,
                "searchable": searchable,
                "sha256": hashlib.sha256(content).hexdigest(),
            }
        )
    if not documents:
        raise SearchIndexError("No searchable Markdown references were found")
    return documents


def terms(query: str) -> list[str]:
    query = re.sub(r"([a-z])([A-Z])", r"\1 \2", query)
    return list(
        dict.fromkeys(
            word.lower()
            for word in re.findall(r"[^\W_]+", query, flags=re.UNICODE)
            if word.lower() not in STOP_WORDS
        )
    )


def _vectors(values: Any, count: int, *, cached: bool = False) -> np.ndarray:
    try:
        array = np.asarray(values, dtype=np.float32)
    except (TypeError, ValueError, OverflowError) as error:
        raise SearchIndexError("Embedding vectors must be numeric float32 values") from error
    if array.shape != (count, DIMENSIONS):
        raise SearchIndexError(
            f"Expected embedding shape ({count}, {DIMENSIONS}), got {array.shape}"
        )
    if not np.isfinite(array).all():
        raise SearchIndexError("Embedding vectors contain non-finite values")
    norms = np.linalg.norm(array, axis=1, keepdims=True)
    if not np.isfinite(norms).all() or (norms <= 0).any():
        raise SearchIndexError("Embedding vectors have zero or invalid norms")
    if cached:
        if not np.allclose(norms, 1.0, atol=1e-5, rtol=1e-5):
            raise SearchIndexError("Cached embedding vectors are not normalized; rebuild the index")
        return array
    return array / np.maximum(norms, 1e-12)


@contextmanager
def _lock(path: Path):
    with path.open("a+b") as handle:
        fcntl.flock(handle, fcntl.LOCK_EX)
        try:
            yield
        finally:
            fcntl.flock(handle, fcntl.LOCK_UN)


class SearchIndex:
    """A synchronized, immutable search snapshot backed by one atomic SQLite cache."""

    def __init__(
        self,
        skill_root: str | Path,
        cache_dir: str | Path,
        embedder: Callable[[list[str]], np.ndarray],
        *,
        fingerprint: Mapping[str, Any] | None = None,
    ):
        self.skill_root = Path(skill_root).resolve()
        self.cache_dir = Path(cache_dir)
        self.embedder = embedder
        model = fingerprint if fingerprint is not None else getattr(embedder, "fingerprint", None)
        if not isinstance(model, Mapping) or not model:
            raise SearchIndexError("A stable, non-empty model/configuration fingerprint is required")
        try:
            self.fingerprint = json.dumps(
                {
                    "model": dict(model),
                    "schema": SCHEMA_VERSION,
                    "dimensions": DIMENSIONS,
                    "normalization": "float32-l2-v1",
                    "preprocessing": "whole-markdown-related-links-camel-v1",
                    "navigation_exclusions": NAVIGATION_EXCLUSIONS,
                    "query_prefix": QUERY_PREFIX,
                },
                sort_keys=True,
                ensure_ascii=False,
                allow_nan=False,
                separators=(",", ":"),
            )
        except (TypeError, ValueError) as error:
            raise SearchIndexError("Model fingerprint must be finite JSON data") from error
        self.documents: list[dict[str, Any]] = []
        self._vectors: np.ndarray | None = None
        self._lexical: sqlite3.Connection | None = None

    @property
    def database_path(self) -> Path:
        return self.cache_dir / "search.sqlite"

    def _cached(self, db: sqlite3.Connection) -> tuple[str | None, dict[str, tuple[str, bytes]]]:
        tables = {row[0] for row in db.execute("SELECT name FROM sqlite_master WHERE type='table'")}
        if not tables:
            return None, {}
        version = db.execute("PRAGMA user_version").fetchone()[0]
        if version != SCHEMA_VERSION or not {"metadata", "documents", "docs"} <= tables:
            raise SearchIndexError(
                f"Unsupported search index schema {version}; rebuild {self.database_path}"
            )
        record = db.execute("SELECT value FROM metadata WHERE key='fingerprint'").fetchone()
        if record is None:
            raise SearchIndexError("Search index has no fingerprint; rebuild the index")
        rows = db.execute("SELECT path,sha256,vector FROM documents ORDER BY path")
        return record[0], {path: (digest, vector) for path, digest, vector in rows}

    def synchronize(self) -> dict[str, int | bool]:
        """Embed only additions/changes; publish all rows together after every input succeeds."""
        self.cache_dir.mkdir(parents=True, exist_ok=True)
        with _lock(self.cache_dir / "search.lock"):
            documents = corpus(self.skill_root)
            try:
                with closing(sqlite3.connect(self.database_path, timeout=60)) as db:
                    old_fingerprint, previous = self._cached(db)
                    invalidated = old_fingerprint is not None and old_fingerprint != self.fingerprint
                    reusable = previous if old_fingerprint == self.fingerprint else {}
                    changed = [
                        doc for doc in documents
                        if doc["path"] not in reusable or reusable[doc["path"]][0] != doc["sha256"]
                    ]
                    by_path = {}
                    for doc in documents:
                        old = reusable.get(doc["path"])
                        if old and old[0] == doc["sha256"]:
                            try:
                                vector = np.frombuffer(old[1], dtype="<f4")
                            except (TypeError, ValueError) as error:
                                raise SearchIndexError("Cached embedding vector is malformed; rebuild the index") from error
                            by_path[doc["path"]] = _vectors(vector.reshape(1, -1), 1, cached=True)[0]
                    for start in range(0, len(changed), BATCH_SIZE):
                        batch = changed[start : start + BATCH_SIZE]
                        vectors = _vectors(self.embedder([doc["searchable"] for doc in batch]), len(batch))
                        by_path.update((doc["path"], vector) for doc, vector in zip(batch, vectors))
                    latest = corpus(self.skill_root)
                    if [(d["path"], d["sha256"]) for d in documents] != [
                        (d["path"], d["sha256"]) for d in latest
                    ]:
                        raise SearchIndexError("References changed during indexing; run the search again")
                    vectors = np.stack([by_path[doc["path"]] for doc in documents])
                    deleted = len(set(previous) - {doc["path"] for doc in documents})
                    if changed or deleted or old_fingerprint != self.fingerprint:
                        db.execute("BEGIN IMMEDIATE")
                        if old_fingerprint is None:
                            db.execute("CREATE TABLE metadata(key TEXT PRIMARY KEY,value TEXT NOT NULL)")
                            db.execute(
                                "CREATE TABLE documents(path TEXT PRIMARY KEY,sha256 TEXT NOT NULL,"
                                "vector BLOB NOT NULL)"
                            )
                            db.execute(FTS_SCHEMA)
                            db.execute(f"PRAGMA user_version={SCHEMA_VERSION}")
                        db.execute("DELETE FROM documents")
                        db.execute("DELETE FROM docs")
                        db.executemany(
                            "INSERT INTO documents(path,sha256,vector) VALUES(?,?,?)",
                            (
                                (doc["path"], doc["sha256"], vector.astype("<f4").tobytes())
                                for doc, vector in zip(documents, vectors)
                            ),
                        )
                        db.executemany(
                            "INSERT INTO docs(rowid,title,body) VALUES(?,?,?)",
                            ((doc["id"] + 1, doc["title"], doc["searchable"]) for doc in documents),
                        )
                        db.execute(
                            "INSERT OR REPLACE INTO metadata(key,value) VALUES('fingerprint',?)",
                            (self.fingerprint,),
                        )
                        db.commit()
            except sqlite3.Error as error:
                raise SearchIndexError(f"Cannot synchronize search index: {error}") from error
            lexical = sqlite3.connect(":memory:")
            try:
                lexical.execute(FTS_SCHEMA)
                lexical.executemany(
                    "INSERT INTO docs(rowid,title,body) VALUES(?,?,?)",
                    ((doc["id"] + 1, doc["title"], doc["searchable"]) for doc in documents),
                )
            except sqlite3.Error as error:
                lexical.close()
                raise SearchIndexError(f"SQLite FTS5 is required for hybrid search: {error}") from error
            if self._lexical is not None:
                self._lexical.close()
            self.documents, self._vectors, self._lexical = documents, vectors, lexical
            return {
                "documents": len(documents),
                "embedded": len(changed),
                "reused": len(documents) - len(changed),
                "deleted": deleted,
                "invalidated": invalidated,
            }

    def _assert_current(self) -> None:
        current = corpus(self.skill_root)
        if [(doc["path"], doc["sha256"]) for doc in current] != [
            (doc["path"], doc["sha256"]) for doc in self.documents
        ]:
            raise SearchIndexError("References changed after synchronization; run the search again")

    def search(self, query: str) -> list[int]:
        """Return RRF(k=60) over the top 100 lexical and semantic results each."""
        if self._lexical is None or self._vectors is None:
            raise SearchIndexError("Call synchronize() before searching")
        if not isinstance(query, str) or not query.strip():
            raise SearchIndexError("Search query must be a non-empty string")
        self._assert_current()
        vector = _vectors(self.embedder([QUERY_PREFIX + query]), 1)[0]
        self._assert_current()
        scores = self._vectors @ vector
        semantic = sorted(
            range(len(self.documents)),
            key=lambda index: (-float(scores[index]), self.documents[index]["path"]),
        )
        query_terms = terms(query)
        lexical = []
        if query_terms:
            expression = " OR ".join('"' + term.replace('"', '""') + '"' for term in query_terms)
            try:
                lexical = [
                    row[0] - 1
                    for row in self._lexical.execute(
                        "SELECT rowid FROM docs WHERE docs MATCH ? ORDER BY bm25(docs,3.0,1.0),rowid",
                        (expression,),
                    )
                ]
            except sqlite3.Error as error:
                raise SearchIndexError(f"Cannot search the SQLite FTS5 index: {error}") from error
        fused: Counter[int] = Counter()
        for ranking in (lexical, semantic):
            for rank, index in enumerate(ranking[:100]):
                fused[index] += 1 / (60 + rank + 1)
        return sorted(fused, key=lambda index: (-fused[index], self.documents[index]["path"]))

    def close(self) -> None:
        if self._lexical is not None:
            self._lexical.close()
        self._lexical = None
        self._vectors = None
        self.documents = []
