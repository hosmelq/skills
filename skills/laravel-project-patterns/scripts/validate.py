#!/usr/bin/env python3
"""Check the Markdown catalog's structure, local links and reachability."""

from collections import Counter
from pathlib import Path
import re
import sys
from urllib.parse import unquote, urlsplit


def markdown(source, name, errors):
    lines = []
    fence = None
    for line in source.splitlines():
        marker = re.match(r"^ {0,3}(`{3,}|~{3,})(.*)$", line)
        if marker:
            token, rest = marker.groups()
            if fence is None:
                fence = token
            elif token[0] == fence[0] and len(token) >= len(fence) and not rest.strip():
                fence = None
            continue
        if fence is None:
            lines.append(line)
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


def validate(root):
    root = Path(root).resolve()
    files = [root / "SKILL.md", *sorted((root / "references").rglob("*.md")), *sorted((root / "docs").rglob("*.md"))]
    errors = []
    parsed = {}
    for source in files:
        if not source.resolve().is_relative_to(root):
            errors.append(f"Markdown escapes the skill directory: {source.relative_to(root)}")
            continue
        parsed[source.resolve()] = markdown(source.read_text(), source.relative_to(root), errors)
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
    pending = [root / "SKILL.md"]
    while pending:
        source = pending.pop()
        if source not in reached:
            reached.add(source)
            pending.extend(graph.get(source, ()))
    for source in set(parsed) - reached:
        errors.append(f"Unreachable Markdown: {source.relative_to(root)}")
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
