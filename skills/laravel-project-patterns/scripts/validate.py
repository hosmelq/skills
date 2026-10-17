#!/usr/bin/env python3
"""Check catalog structure, local links, documentation reachability and PHP examples."""

from collections import Counter
import json
from pathlib import Path
import re
import subprocess
import sys
import tempfile
from urllib.parse import unquote, urlsplit


def markdown(source, name, errors, blocks=None):
    lines = []
    fence = None
    php = None
    for line in source.splitlines():
        marker = re.match(r"^ {0,3}(`{3,}|~{3,})(.*)$", line)
        if marker:
            token, rest = marker.groups()
            if fence is None:
                fence = token
                php = [] if rest.strip().split(" ")[0] == "php" else None
            elif token[0] == fence[0] and len(token) >= len(fence) and not rest.strip():
                fence = None
                if php is not None and blocks is not None:
                    blocks.append("\n".join(php) + "\n")
                php = None
            elif php is not None:
                php.append(line)
            continue
        if fence is None:
            lines.append(line)
        elif php is not None:
            php.append(line)
    if fence:
        errors.append(f"Unbalanced code fence: {name}")
    headings = re.findall(r"^(#{1,6})\s+(.+?)\s*#*\s*$", "\n".join(lines), re.MULTILINE)
    if not any(level == "#" for level, _ in headings):
        errors.append(f"Missing top-level heading: {name}")
    anchors = set()
    counts = Counter()
    for _, heading in headings:
        clean = re.sub(r"[`*_~]", "", heading.lower())
        clean = "".join(char for char in clean if char.isalnum() or char.isspace() or char == "-")
        base = re.sub(r"\s+", "-", clean.strip())
        anchors.add(base if not counts[base] else f"{base}-{counts[base]}")
        counts[base] += 1
    links = re.findall(r"\[[^\]]*\]\(([^)]+)\)", "\n".join(lines))
    return anchors, links


def tooling(root):
    """Return the nearest directory holding the ECS and Rector configuration and binaries."""
    for directory in Path(root).resolve().parents:
        if all((directory / path).is_file() for path in ("ecs.php", "rector.php", "vendor/bin/ecs", "vendor/bin/rector")):
            return directory
    return None


def run_json(command, cwd):
    result = subprocess.run(command, cwd=cwd, capture_output=True, text=True)
    try:
        return json.loads(result.stdout)
    except ValueError:
        raise ValueError(f"{Path(command[0]).name} failed: {(result.stderr or result.stdout).strip()[:500]}") from None


def php_examples(examples, tools):
    """Report examples that ECS or Rector would change. `examples` maps labels to PHP sources."""
    errors = []
    checked = {}
    for label, code in examples.items():
        if code.startswith("<?php\n"):
            checked[label] = code
        else:
            errors.append(f"PHP example must be a complete file starting with <?php: {label}")
    if not checked:
        return errors
    if tools is None:
        return [*errors, "PHP tooling missing: run `composer install` in the repository root"]
    with tempfile.TemporaryDirectory() as temporary:
        directory = Path(temporary).resolve()
        labels = {}
        for index, (label, code) in enumerate(checked.items()):
            path = directory / f"example{index}.php"
            path.write_text(code, encoding="utf-8")
            labels[str(path)] = label
        found = {}

        def record(tool, file, detail):
            label = labels.get(str((tools / file).resolve()), file)
            found.setdefault((tool, label), set()).add(detail.rsplit("\\", 1)[-1])

        ecs = run_json(["vendor/bin/ecs", "check", "--config=ecs.php", "--no-progress-bar", "--output-format=json", str(directory)], tools)
        for file, result in ecs.get("files", {}).items():
            for diff in result.get("diffs", []):
                for checker in diff.get("applied_checkers", []):
                    record("ECS", file, checker)
            for error in result.get("errors", []):
                record("ECS", file, error.get("message", "error"))
        rector = run_json(["vendor/bin/rector", "process", "--dry-run", "--config=rector.php", "--output-format=json", str(directory)], tools)
        for diff in rector.get("file_diffs", []):
            for rule in diff.get("applied_rectors", []):
                record("Rector", diff["file"], rule)
        for error in rector.get("errors", []):
            record("Rector", error.get("file", "?"), error.get("message", "error"))
    errors.extend(f"{tool} changes PHP example: {label} ({', '.join(sorted(details))})" for (tool, label), details in found.items())
    return errors


def validate(root):
    root = Path(root).resolve()
    files = [root / "SKILL.md", *sorted((root / "references").rglob("*.md")), *sorted((root / "docs").rglob("*.md"))]
    errors = []
    parsed = {}
    examples = {}
    for source in files:
        if not source.resolve().is_relative_to(root):
            errors.append(f"Markdown escapes the skill directory: {source.relative_to(root)}")
            continue
        blocks = []
        parsed[source.resolve()] = markdown(source.read_text(), source.relative_to(root), errors, blocks)
        if source.is_relative_to(root / "references"):
            examples.update((f"{source.relative_to(root)} block {number}", code) for number, code in enumerate(blocks, 1))
    graph = {source: set() for source in parsed}
    for source, (_, links) in parsed.items():
        for link in links:
            target = link.strip()
            target = target[1:target.index(">")] if target.startswith("<") and ">" in target else re.split(r"\s+[\"']", target, maxsplit=1)[0]
            url = urlsplit(target)
            if url.scheme or url.netloc:
                continue
            path = (source.parent / unquote(url.path)).resolve() if url.path else source
            if not path.is_relative_to(root) or not path.is_file():
                errors.append(f"Missing or escaping link: {source.relative_to(root)} -> {target}")
                continue
            if path in parsed:
                graph[source].add(path)
                if url.fragment and unquote(url.fragment) not in parsed[path][0]:
                    errors.append(f"Missing anchor: {source.relative_to(root)} -> {target}")
    reached = set()
    # Search indexes every reference; only other docs require a navigation link.
    pending = [root / "SKILL.md", *(source for source in parsed if source.is_relative_to(root / "references"))]
    while pending:
        source = pending.pop()
        if source not in reached:
            reached.add(source)
            pending.extend(graph.get(source, ()))
    for source in set(parsed) - reached:
        errors.append(f"Unreachable Markdown: {source.relative_to(root)}")
    errors.extend(php_examples(examples, tooling(root)))
    return len(files), sorted(errors)


if __name__ == "__main__":
    try:
        count, errors = validate(Path(__file__).resolve().parent.parent)
        print(f"markdown={count} errors={len(errors)}")
        for error in errors:
            print(error)
        sys.exit(bool(errors))
    except (OSError, ValueError) as error:
        print(f"validate: {error}", file=sys.stderr)
        sys.exit(1)
