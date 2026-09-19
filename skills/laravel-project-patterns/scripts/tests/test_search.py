"""Storage and retrieval contracts; fake vectors do not measure model quality."""

from __future__ import annotations

import hashlib
import re
import sys
import tempfile
import unittest
from pathlib import Path

import numpy as np

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from search_index import SearchIndex, SearchIndexError, corpus


class FakeEmbedder:
    """Stable, local test vectors with visible calls and injected failures."""

    def __init__(self):
        self.calls: list[list[str]] = []
        self.failure: Exception | None = None
        self.fail_at: int | None = None
        self.invalid: str | None = None

    def __call__(self, texts: list[str]) -> np.ndarray:
        self.calls.append(list(texts))
        if self.failure and (self.fail_at is None or len(self.calls) >= self.fail_at):
            raise self.failure
        vectors = np.zeros((len(texts), 1024), dtype=np.float32)
        for row, text in enumerate(texts):
            for term in re.findall(r"\w+", text.casefold()):
                digest = hashlib.sha256(term.encode()).digest()
                vectors[row, int.from_bytes(digest[:2], "big") % 1024] += 1
            vectors[row, 1023] += 0.01
        if self.invalid == "dimensions":
            return vectors[:, :32]
        if self.invalid == "rows":
            return np.concatenate((vectors, vectors[:1]))
        if self.invalid == "nan":
            vectors[0, 0] = np.nan
        if self.invalid == "infinity":
            vectors[0, 0] = np.inf
        if self.invalid == "zero":
            vectors[0] = 0
        return vectors

    @property
    def inputs(self) -> list[str]:
        return [text for call in self.calls for text in call]


class SearchIndexTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name)
        self.skill = self.root / "skill"
        self.skill.mkdir()
        self.cache = self.root / "cache"
        self.embedder = FakeEmbedder()

    def write(self, path: str, content: str) -> Path:
        target = self.skill / path
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(content, encoding="utf-8")
        return target

    def index(self, *, fingerprint: str = "fixture-model-v1", embedder=None):
        index = SearchIndex(
            self.skill,
            self.cache,
            self.embedder if embedder is None else embedder,
            fingerprint={"model": fingerprint},
        )
        self.addCleanup(index.close)
        return index

    @staticmethod
    def by_path(index) -> dict:
        return {document["path"]: document for document in index.documents}

    def test_corpus_keeps_complete_sources_and_content_hashes(self):
        raw = "# Enum validation\n\nKeep categoría errors distinct.\n```php\n$errors->toBe([]);\n```\n"
        self.write("references/http/enum.md", raw)
        self.write("scripts/helper.py", "print('not a reference')\n")
        documents = corpus(self.skill)
        self.assertEqual([item["path"] for item in documents], ["references/http/enum.md"])
        document = documents[0]
        self.assertEqual(document["raw"], raw)
        self.assertEqual(document["sha256"], hashlib.sha256(raw.encode()).hexdigest())
        self.assertIn("Enum validation", document["title"])
        self.assertIn("categoría", document["searchable"])

    def test_cold_index_keeps_full_sources_and_ranks_without_delivering_bodies(self):
        large = "# Validation details\n" + "validation " * 500
        small = "# Enum validation\nReturn early when primitive validation fails.\n"
        self.write("references/http/large.md", large)
        self.write("references/http/small.md", small)
        index = self.index()
        index.synchronize()
        self.assertEqual(len(self.embedder.inputs), 2)
        ranking = index.search("enum validation")
        self.assertEqual(set(ranking), {item["id"] for item in index.documents})
        ids = self.by_path(index)
        self.assertEqual(ids["references/http/large.md"]["raw"], large)
        self.assertEqual(ids["references/http/small.md"]["raw"], small)
        self.assertTrue(all(isinstance(item, int) for item in ranking))

    def test_reopening_an_unchanged_index_reuses_document_embeddings(self):
        self.write("references/first.md", "# First\nAn unchanged source.\n")
        self.index().synchronize()
        self.embedder.calls.clear()
        reopened = self.index()
        reopened.synchronize()
        self.assertEqual(self.embedder.calls, [])
        self.assertEqual(len(reopened.documents), 1)
        self.assertTrue(reopened.search("unchanged source"))
        self.assertEqual(len(self.embedder.calls), 1)
        self.assertEqual(len(self.embedder.calls[0]), 1)
        self.assertTrue(self.embedder.calls[0][0].endswith("unchanged source"))

    def test_incremental_add_change_delete_embeds_only_new_content(self):
        self.write("references/alpha.md", "# Alpha\nRemove this source.\n")
        self.write("references/stable.md", "# Stable\nKeep unchanged content.\n")
        self.write("references/zeta.md", "# Zeta\nPrevious behavior.\n")
        index = self.index()
        index.synchronize()
        self.embedder.calls.clear()
        (self.skill / "references/alpha.md").unlink()
        self.write("references/middle.md", "# Middle\nNewly added behavior.\n")
        self.write("references/zeta.md", "# Zeta\nUpdated behavior.\n")
        index.synchronize()
        documents = self.by_path(index)
        self.assertEqual(
            set(documents),
            {"references/middle.md", "references/stable.md", "references/zeta.md"},
        )
        self.assertCountEqual(
            self.embedder.inputs,
            [documents["references/middle.md"]["searchable"], documents["references/zeta.md"]["searchable"]],
        )
        ranked = [index.documents[item] for item in index.search("Updated behavior")]
        self.assertNotIn("references/alpha.md", [item["path"] for item in ranked])
        self.assertIn("Updated behavior.", "\n".join(item["raw"] for item in ranked))

    def test_failed_synchronization_keeps_previous_snapshot_and_retries_changes(self):
        self.write("references/stable.md", "# Stable\nUnchanged contract.\n")
        self.write("references/update.md", "# Update\nOld contract.\n")
        index = self.index()
        index.synchronize()
        previous = [dict(item) for item in index.documents]
        self.write("references/update.md", "# Update\nNew contract.\n")
        for number in range(8):
            self.write(f"references/new-{number}.md", f"# New {number}\nNew independent source {number}.\n")
        self.embedder.calls.clear()
        self.embedder.failure = RuntimeError("temporary embedding failure")
        self.embedder.fail_at = 2
        with self.assertRaisesRegex(RuntimeError, "temporary embedding failure"):
            index.synchronize()
        self.assertEqual(index.documents, previous)
        with self.assertRaises(SearchIndexError):
            index.search("new contract")
        self.embedder.failure = None
        self.embedder.calls.clear()
        reopened = self.index()
        reopened.synchronize()
        documents = self.by_path(reopened)
        self.assertCountEqual(
            self.embedder.inputs,
            [document["searchable"] for path, document in documents.items() if path != "references/stable.md"],
        )
        self.assertEqual(len(documents), 10)

    def test_changed_source_cannot_be_returned_from_an_old_ranking(self):
        self.write("references/http.md", "# HTTP\nOriginal contract.\n")
        index = self.index()
        index.synchronize()
        index.search("HTTP contract")
        self.write("references/http.md", "# HTTP\nChanged after search.\n")
        with self.assertRaises(SearchIndexError):
            index.search("HTTP contract")

    def test_deleted_source_cannot_be_returned_from_an_old_ranking(self):
        self.write("references/http.md", "# HTTP\nOriginal contract.\n")
        index = self.index()
        index.synchronize()
        index.search("HTTP contract")
        (self.skill / "references/http.md").unlink()
        with self.assertRaises(SearchIndexError):
            index.search("HTTP contract")

    def test_model_fingerprint_change_reembeds_every_document(self):
        self.write("references/one.md", "# One\nFirst contract.\n")
        self.write("references/two.md", "# Two\nSecond contract.\n")
        self.index().synchronize()
        self.embedder.calls.clear()
        replacement = self.index(fingerprint="fixture-model-v2")
        replacement.synchronize()
        self.assertCountEqual(self.embedder.inputs, [item["searchable"] for item in replacement.documents])
        self.assertEqual(len(self.embedder.inputs), 2)

    def test_invalid_document_vectors_never_publish_an_index(self):
        self.write("references/one.md", "# One\nA source contract.\n")
        for invalid in ("dimensions", "rows", "nan", "infinity", "zero"):
            with self.subTest(invalid=invalid):
                embedder = FakeEmbedder()
                embedder.invalid = invalid
                index = SearchIndex(self.skill, self.root / invalid, embedder, fingerprint={"model": "fixture"})
                self.addCleanup(index.close)
                with self.assertRaises(SearchIndexError):
                    index.synchronize()
                embedder.invalid = None
                embedder.calls.clear()
                index.synchronize()
                self.assertEqual(len(embedder.inputs), 1)
                self.assertEqual(len(index.documents), 1)

    def test_invalid_query_vectors_fail_instead_of_becoming_a_lexical_fallback(self):
        self.write("references/one.md", "# One\nA source contract.\n")
        index = self.index()
        index.synchronize()
        self.embedder.invalid = "nan"
        with self.assertRaises(SearchIndexError):
            index.search("source contract")

    def test_hybrid_retains_complementary_lexical_and_vector_results(self):
        self.write("references/lexical.md", "# Xylophone\nXylophone exact identifier.\n")
        self.write("references/semantic.md", "# Meaning\nThe semantically relevant source.\n")
        self.write("references/distractor.md", "# Distractor\nAn unrelated source.\n")

        def vectors(texts):
            result = np.zeros((len(texts), 1024), dtype=np.float32)
            for row, text in enumerate(texts):
                if text.endswith("Query: xylophone") or "semantically relevant" in text:
                    result[row, 0] = 1
                elif "unrelated source" in text:
                    result[row, :2] = [0.8, 0.6]
                else:
                    result[row, 1] = 1
            return result

        index = self.index(embedder=vectors)
        index.synchronize()
        ranking = index.search("xylophone")
        documents = {item["id"]: item for item in index.documents}
        self.assertEqual(
            {documents[identifier]["path"] for identifier in ranking[:2]},
            {"references/lexical.md", "references/semantic.md"},
        )


if __name__ == "__main__":
    unittest.main()
