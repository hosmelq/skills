"""Behavioral checks for catalog links and Markdown boundaries."""

import sys
import tempfile
import unittest
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from validate import markdown, validate


class CatalogValidationTests(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory()
        self.addCleanup(self.temporary.cleanup)
        self.root = Path(self.temporary.name)

    def write(self, path, content):
        target = self.root / path
        target.parent.mkdir(parents=True, exist_ok=True)
        target.write_text(content, encoding="utf-8")

    def test_valid_links_include_encoded_paths_and_duplicate_heading_anchors(self):
        self.write(
            "SKILL.md",
            '# Skill\n[Guide](<references/HTTP%20guide.md#response-1> "HTTP guide")\n',
        )
        self.write(
            "references/HTTP guide.md",
            "# HTTP guide\n## Response\nFirst contract.\n## Response\nSecond contract.\n",
        )
        count, errors = validate(self.root)
        self.assertEqual(count, 2)
        self.assertEqual(errors, [])

    def test_fenced_examples_do_not_create_links_or_heading_anchors(self):
        self.write("SKILL.md", "# Skill\n[Guide](references/guide.md#response)\n")
        self.write(
            "references/guide.md",
            "# Guide\n````markdown\n## Fake heading\n[Example](missing.md)\n```\n"
            "[Still an example](also-missing.md)\n````\n## Response\nActual contract.\n",
        )
        _, errors = validate(self.root)
        self.assertEqual(errors, [])
        self.write("SKILL.md", "# Skill\n[Guide](references/guide.md#fake-heading)\n")
        _, errors = validate(self.root)
        self.assertEqual(len(errors), 1)
        self.assertIn("Missing anchor:", errors[0])

    def test_indexed_references_need_no_links_but_unreachable_docs_are_reported(self):
        self.write(
            "SKILL.md",
            "# Skill\n[Guide](references/guide.md#absent)\n"
            "[Missing](references/missing.md)\n[Outside](../outside.md)\n",
        )
        self.write("references/guide.md", "# Guide\nActual contract.\n")
        self.write("references/orphan.md", "# Orphan\nUnlinked contract.\n")
        self.write("docs/orphan.md", "# Orphan\nUnlinked documentation.\n")
        _, errors = validate(self.root)
        self.assertEqual(len(errors), 4)
        self.assertTrue(any("Missing anchor:" in error for error in errors))
        self.assertTrue(any("references/missing.md" in error for error in errors))
        self.assertTrue(any("../outside.md" in error for error in errors))
        self.assertTrue(any("Unreachable Markdown: docs/orphan.md" in error for error in errors))

    def test_shorter_closing_fence_does_not_hide_an_unbalanced_example(self):
        errors = []
        anchors, links = markdown(
            "~~~~markdown\n# This is only an example\n~~~\n[Example](missing.md)\n",
            "references/broken.md",
            errors,
        )
        self.assertEqual(anchors, set())
        self.assertEqual(links, [])
        self.assertTrue(any("Unbalanced code fence:" in error for error in errors))
        self.assertTrue(any("Missing top-level heading:" in error for error in errors))


if __name__ == "__main__":
    unittest.main()
