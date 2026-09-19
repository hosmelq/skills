"""Consumer-visible retrieval receipts, source selection and CLI read isolation."""

from contextlib import redirect_stdout
import copy
import io
import json
import os
from pathlib import Path
import sys
import tempfile
import unittest
from unittest import mock

import tiktoken

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

import reference_session
from reference_session import ReferenceSession
import search
from search_index import SearchIndexError, corpus


class ReferenceSessionTests(unittest.TestCase):
    def setUp(self):
        temporary = tempfile.TemporaryDirectory()
        self.addCleanup(temporary.cleanup)
        self.work = Path(temporary.name)
        self.skill = self.work / "skill"
        (self.skill / "references").mkdir(parents=True)
        self.receipt = self.work / "consumer.json"
        self.encoding = tiktoken.get_encoding("o200k_base")
        self.alpha = self.write("alpha.md", "Authentication", "ALPHA_BODY")
        self.beta = self.write("beta.md", "Dependent options", "BETA_BODY")

    def write(self, name, title, body):
        fence = chr(96) * 3
        raw = (
            f"# {title}\n\n"
            f"Use for the {title.lower()} behavior on an HTTP create page.\n\n"
            f"{fence}php\n<?php\n// {body}\n{fence}\n"
        )
        path = self.skill / "references" / name
        path.write_text(raw)
        return path

    def shortlist(self, ranking=None):
        documents = corpus(self.skill)
        with ReferenceSession(self.skill, self.receipt).locked() as session:
            result = session.shortlist(
                documents, list(range(len(documents))) if ranking is None else ranking,
                limit=10,
            )
            session.save()
        return result

    def read(self, identifiers, **options):
        with ReferenceSession(self.skill, self.receipt).locked() as session:
            result = session.read(identifiers, **options)
            session.save()
        return result

    @staticmethod
    def cards_by_title(result):
        return {card["title"]: card for card in result["candidates"]}

    def cost(self, reference):
        serialized = json.dumps(reference, ensure_ascii=False, separators=(",", ":"))
        return len(self.encoding.encode(serialized))

    def test_shortlist_emits_descriptions_without_source_bodies(self):
        result = self.shortlist([1, 1, 0])
        self.assertEqual(
            [card["title"] for card in result["candidates"]],
            ["Dependent options", "Authentication"],
        )
        self.assertEqual(result["remaining_candidates"], 0)
        for card in result["candidates"]:
            self.assertEqual(set(card), {"id", "title", "summary", "tokens", "read"})
            self.assertFalse(card["read"])
            self.assertGreater(card["tokens"], 0)
        for content in (json.dumps(result), self.receipt.read_text()):
            self.assertNotIn("ALPHA_BODY", content)
            self.assertNotIn("BETA_BODY", content)
            self.assertNotIn(chr(96) * 3, content)

    def test_read_returns_only_selected_whole_sources_without_following_links(self):
        self.alpha.write_text(self.alpha.read_text() + "\n[Other example](beta.md)\n")
        cards = self.cards_by_title(self.shortlist())
        result = self.read([cards["Authentication"]["id"]])
        self.assertEqual(len(result["references"]), 1)
        reference = result["references"][0]
        self.assertEqual(reference["path"], "references/alpha.md")
        self.assertEqual(reference["content"], self.alpha.read_text())
        self.assertNotIn("BETA_BODY", json.dumps(result))
        self.assertEqual(result["source_tokens"], self.cost(reference))
        self.assertEqual(result["source_tokens"], cards["Authentication"]["tokens"])

    def test_an_invalid_unselected_reference_does_not_block_a_selected_read(self):
        cards = self.cards_by_title(self.shortlist())
        alpha_id = cards["Authentication"]["id"]
        for invalid in (b"An unrelated reference without a heading.\n", b"\xff"):
            with self.subTest(invalid=invalid):
                self.beta.write_bytes(invalid)
                result = self.read([alpha_id], repeat=True)
                self.assertEqual(result["references"][0]["content"], self.alpha.read_text())
                self.assertEqual(result["source_tokens"], cards["Authentication"]["tokens"])

    def test_selected_symlinks_cannot_escape_the_references_directory(self):
        cards = self.cards_by_title(self.shortlist())
        previous = self.receipt.read_bytes()
        targets = (
            (self.alpha, self.work / "outside-skill.md", cards["Authentication"]["id"]),
            (self.beta, self.skill / "outside-references.md", cards["Dependent options"]["id"]),
        )
        for selected, target, identifier in targets:
            with self.subTest(target=target):
                selected.rename(target)
                selected.symlink_to(target)
                with self.assertRaisesRegex(SearchIndexError, "outside the references directory.*search again"):
                    self.read([identifier])
                self.assertEqual(self.receipt.read_bytes(), previous)

    def test_selected_invalid_utf8_does_not_save_read_receipts(self):
        cards = self.cards_by_title(self.shortlist())
        previous = self.receipt.read_bytes()
        self.alpha.write_bytes(b"\xff")
        with self.assertRaisesRegex(SearchIndexError, "UTF-8.*search again"):
            self.read([cards["Authentication"]["id"]])
        self.assertEqual(self.receipt.read_bytes(), previous)

    def test_followup_reads_deduplicate_and_charge_only_content_actually_emitted(self):
        cards = self.cards_by_title(self.shortlist())
        alpha_id = cards["Authentication"]["id"]
        beta_id = cards["Dependent options"]["id"]
        first = self.read([alpha_id, alpha_id])
        self.assertEqual(len(first["references"]), 1)
        self.assertEqual(first["session_source_tokens"], first["source_tokens"])

        refreshed = self.cards_by_title(self.shortlist())
        self.assertTrue(refreshed["Authentication"]["read"])
        self.assertFalse(refreshed["Dependent options"]["read"])
        duplicate = self.read([alpha_id])
        self.assertEqual(duplicate["references"], [])
        self.assertEqual(duplicate["already_read"], [alpha_id])
        self.assertEqual(duplicate["source_tokens"], 0)
        self.assertEqual(duplicate["session_source_tokens"], first["source_tokens"])

        second = self.read([alpha_id, beta_id])
        self.assertEqual([item["id"] for item in second["references"]], [beta_id])
        self.assertEqual(second["already_read"], [alpha_id])
        cumulative = first["source_tokens"] + second["source_tokens"]
        self.assertEqual(second["session_source_tokens"], cumulative)
        repeated = self.read([alpha_id], repeat=True)
        self.assertEqual(repeated["references"], first["references"])
        self.assertEqual(repeated["already_read"], [])
        self.assertEqual(repeated["session_source_tokens"], cumulative + first["source_tokens"])

    def test_changed_and_deleted_references_reject_old_ids_without_corrupting_receipts(self):
        cards = self.cards_by_title(self.shortlist())
        old_id = cards["Authentication"]["id"]
        first = self.read([old_id])
        self.alpha.write_text(self.alpha.read_text().replace("ALPHA_BODY", "UPDATED_ALPHA_BODY"))
        previous = self.receipt.read_bytes()
        with self.assertRaisesRegex(SearchIndexError, "changed or was deleted"):
            self.read([old_id])
        self.assertEqual(self.receipt.read_bytes(), previous)

        refreshed = self.cards_by_title(self.shortlist())
        new_id = refreshed["Authentication"]["id"]
        self.assertNotEqual(new_id, old_id)
        self.assertFalse(refreshed["Authentication"]["read"])
        result = self.read([new_id])
        self.assertEqual(result["references"][0]["content"], self.alpha.read_text())
        self.assertEqual(result["session_source_tokens"], first["source_tokens"] + result["source_tokens"])

        self.beta.rename(self.work / "removed-reference.md")
        previous = self.receipt.read_bytes()
        with self.assertRaisesRegex(SearchIndexError, "changed or was deleted"):
            self.read([cards["Dependent options"]["id"]])
        self.assertEqual(self.receipt.read_bytes(), previous)

    def test_ids_survive_an_unrelated_reference_changing_corpus_positions(self):
        original = self.cards_by_title(self.shortlist())
        self.write("00-new.md", "New reference", "NEW_BODY")
        updated = self.cards_by_title(self.shortlist())
        for title in original:
            self.assertEqual(updated[title]["id"], original[title]["id"])
        result = self.read([original["Dependent options"]["id"]])
        self.assertEqual(result["references"][0]["content"], self.beta.read_text())

    def test_oversized_selection_is_never_truncated_replaced_or_marked_read(self):
        large = self.write("large.md", "Large example", "padding " * 2000)
        cards = self.cards_by_title(self.shortlist())
        large_id = cards["Large example"]["id"]
        beta_id = cards["Dependent options"]["id"]
        budget = cards["Dependent options"]["tokens"]
        blocked = self.read([large_id], budget=budget)
        self.assertEqual(blocked["references"], [])
        self.assertEqual(blocked["blocked"], [{"id": large_id, "tokens": cards["Large example"]["tokens"]}])
        self.assertEqual(blocked["session_source_tokens"], 0)
        self.assertFalse(self.cards_by_title(self.shortlist())["Large example"]["read"])

        partial = self.read([large_id, beta_id], budget=budget)
        self.assertEqual([item["id"] for item in partial["references"]], [beta_id])
        self.assertEqual(partial["blocked"], blocked["blocked"])
        self.assertFalse(self.cards_by_title(self.shortlist())["Large example"]["read"])
        result = self.read([large_id], budget=cards["Large example"]["tokens"])
        self.assertEqual(result["references"][0]["content"], large.read_text())
        self.assertEqual(result["blocked"], [])
        self.assertEqual(result["session_source_tokens"], partial["source_tokens"] + result["source_tokens"])

    def test_a_failed_selection_does_not_acknowledge_other_requested_sources(self):
        cards = self.cards_by_title(self.shortlist())
        alpha_id = cards["Authentication"]["id"]
        previous = self.receipt.read_bytes()
        with self.assertRaisesRegex(ValueError, "Unknown candidate"):
            self.read([alpha_id, "not-a-candidate"])
        self.assertEqual(self.receipt.read_bytes(), previous)
        self.assertFalse(self.cards_by_title(self.shortlist())["Authentication"]["read"])

    def test_an_edit_during_reading_does_not_save_read_receipts(self):
        cards = self.cards_by_title(self.shortlist())
        previous = self.receipt.read_bytes()
        original_token_count = reference_session.token_count

        def count_and_edit(value):
            tokens = original_token_count(value)
            self.alpha.write_text(self.alpha.read_text() + "\nChanged during reading.\n")
            return tokens

        with mock.patch.object(reference_session, "token_count", side_effect=count_and_edit):
            with self.assertRaisesRegex(SearchIndexError, "changed during reading"):
                self.read([cards["Authentication"]["id"]])
        self.assertEqual(self.receipt.read_bytes(), previous)

    def test_a_deletion_during_reading_does_not_save_read_receipts(self):
        cards = self.cards_by_title(self.shortlist())
        previous = self.receipt.read_bytes()
        original_token_count = reference_session.token_count

        def count_and_remove(value):
            tokens = original_token_count(value)
            self.alpha.rename(self.work / "removed-during-read.md")
            return tokens

        with mock.patch.object(reference_session, "token_count", side_effect=count_and_remove):
            with self.assertRaisesRegex(SearchIndexError, "changed or was deleted.*search again"):
                self.read([cards["Authentication"]["id"]])
        self.assertEqual(self.receipt.read_bytes(), previous)

    def test_invalid_or_foreign_sessions_are_rejected_without_overwriting_them(self):
        self.shortlist()
        valid = json.loads(self.receipt.read_text())
        invalid = ["{", "[]"]
        changes = [
            {"catalog": str(self.work / "another-skill")},
            {"version": True},
            {"version": 1.0},
            {"source_tokens": True},
            {"source_tokens": -1},
            {"seen": {"references/../outside.md": "a" * 64}},
            {"candidates": {"forged-id": {"path": "references/alpha.md", "sha256": "a" * 64}}},
        ]
        for change in changes:
            value = copy.deepcopy(valid)
            value.update(change)
            invalid.append(json.dumps(value))
        for serialized in invalid:
            with self.subTest(serialized=serialized):
                self.receipt.write_text(serialized)
                with self.assertRaises(ValueError):
                    with ReferenceSession(self.skill, self.receipt).locked():
                        self.fail("Invalid session was accepted")
                self.assertEqual(self.receipt.read_text(), serialized)

    def test_session_location_rejects_skill_content_and_symlink_targets(self):
        with self.assertRaisesRegex(ValueError, "outside the skill directory"):
            ReferenceSession(self.skill, self.alpha)
        self.shortlist()
        previous = self.receipt.read_bytes()
        link = self.work / "alias.json"
        link.symlink_to(self.receipt)
        with self.assertRaisesRegex(ValueError, "regular session file"):
            ReferenceSession(self.skill, link)
        self.assertEqual(self.receipt.read_bytes(), previous)

    def test_a_fence_without_a_blank_line_cannot_leak_php_into_a_card(self):
        self.shortlist()
        previous = self.receipt.read_bytes()
        fence = chr(96) * 3
        self.alpha.write_text(f"# Authentication\n\nShort summary.\n{fence}php\nPRIVATE_BODY\n{fence}\n")
        with self.assertRaises(SearchIndexError):
            self.shortlist()
        self.assertEqual(self.receipt.read_bytes(), previous)

    def test_cli_read_never_enters_the_model_or_index_path(self):
        cards = self.cards_by_title(self.shortlist())
        output = io.StringIO()
        with (
            mock.patch.object(search, "__file__", str(self.skill / "scripts" / "search.py")),
            mock.patch.object(search, "indexed_command", side_effect=AssertionError("Model path used")) as indexed,
            mock.patch.dict(os.environ),
            redirect_stdout(output),
        ):
            search.main([
                "--cache-dir", str(self.work / "cache"), "read",
                "--session", str(self.receipt), "--ids", cards["Authentication"]["id"],
            ])
        indexed.assert_not_called()
        result = json.loads(output.getvalue())
        self.assertEqual(result["references"][0]["content"], self.alpha.read_text())
        self.assertEqual(result["session_source_tokens"], result["source_tokens"])


if __name__ == "__main__":
    unittest.main()
