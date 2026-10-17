#!/usr/bin/env python3
"""Run the Laravel skill's PHP examples against the fixture application.

Every class example must match the fixture member by member, every migration
statement must exist in a fixture migration, every test example must pass, and the reference tests must cover at least
MINIMUM_COVERAGE percent of the lines of the methods the examples show. Each
uncovered line is reported as a warning.
"""

import json
import re
import sqlite3
import subprocess
import sys
import xml.etree.ElementTree as ElementTree
from pathlib import Path

ROOT = Path(__file__).resolve().parent
APP = ROOT / "laravel"
SKILL = ROOT.parent / "skills" / "laravel-project-patterns"
TESTS = APP / "tests" / "References"
MINIMUM_COVERAGE = 90

sys.path.insert(0, str(SKILL / "scripts"))

from validate import markdown  # noqa: E402

WORLD = {
    "countries": [(1, "NI", "Nicaragua", "505"), (2, "US", "United States", "1")],
    "states": [(1, 1, "NI", "MN", "Managua"), (2, 2, "US", "NY", "New York")],
    "cities": [(1, 1, "NI", "Managua", 1), (2, 2, "US", "New York", 2)],
}


def examples():
    found = {}
    for source in sorted((SKILL / "references").rglob("*.md")):
        blocks = []
        markdown(source.read_text(), source.relative_to(SKILL), [], blocks)
        for number, code in enumerate(blocks, 1):
            if code.startswith("<?php\n"):
                found[f"{source.relative_to(SKILL)} block {number}"] = code
    return found


def build_world_database():
    path = APP / "database" / "world.sqlite"
    path.unlink(missing_ok=True)
    with sqlite3.connect(path) as connection:
        connection.executescript(
            """
            CREATE TABLE countries (id INTEGER PRIMARY KEY, iso2 TEXT, name TEXT, phonecode TEXT,
                created_at TEXT, updated_at TEXT);
            CREATE TABLE states (id INTEGER PRIMARY KEY, country_id INTEGER, country_code TEXT, iso2 TEXT,
                name TEXT, created_at TEXT, updated_at TEXT);
            CREATE TABLE cities (id INTEGER PRIMARY KEY, country_id INTEGER, country_code TEXT, name TEXT,
                state_id INTEGER, created_at TEXT, updated_at TEXT);
            """
        )
        connection.executemany("INSERT INTO countries (id, iso2, name, phonecode) VALUES (?, ?, ?, ?)", WORLD["countries"])
        connection.executemany("INSERT INTO states (id, country_id, country_code, iso2, name) VALUES (?, ?, ?, ?, ?)", WORLD["states"])
        connection.executemany("INSERT INTO cities (id, country_id, country_code, name, state_id) VALUES (?, ?, ?, ?, ?)", WORLD["cities"])


def test_file_name(label):
    words = re.findall(r"[A-Za-z]+|\d+", label.replace("references/", "").replace(".md", ""))
    return "".join(word.capitalize() for word in words) + "Test.php"


def uncovered(members, clover):
    lines = {}
    for file in ElementTree.parse(clover).getroot().iter("file"):
        lines[Path(file.get("name")).resolve()] = {
            int(line.get("num")): int(line.get("count"))
            for line in file.iter("line")
            if line.get("type") == "stmt"
        }
    warnings = set()
    statements = {}
    for member in members:
        if member.get("plain_relation") or member["path"].startswith("app/Providers/") or not member["path"].startswith(("app/", "tests/Support/")):
            continue
        counts = lines.get((APP / member["path"]).resolve(), {})
        for line in range(member["start"], member["end"] + 1):
            if line in counts:
                statements[(member["path"], line)] = counts[line] > 0
        missed = [str(line) for line in range(member["start"], member["end"] + 1) if counts.get(line) == 0]
        if missed:
            warnings.add(f"{member['label']}: {member['member']} has untested lines {', '.join(missed)}")
    coverage = 100 * sum(statements.values()) / len(statements) if statements else 100.0
    return coverage, sorted(warnings)


def main():
    found = examples()
    compared = subprocess.run(
        ["php", "compare.php"], cwd=APP, input=json.dumps([{"label": k, "code": v} for k, v in found.items()]),
        capture_output=True, text=True,
    )
    if compared.returncode:
        sys.exit(f"compare.php failed: {compared.stderr or compared.stdout}")
    result = json.loads(compared.stdout)
    errors = list(result["errors"])

    for stale in TESTS.glob("*Test.php"):
        stale.unlink()
    TESTS.mkdir(parents=True, exist_ok=True)
    for label in result["tests"]:
        (TESTS / test_file_name(label)).write_text(found[label])

    subprocess.run(["docker", "compose", "up", "--detach", "--wait"], cwd=ROOT, check=True, capture_output=True)
    build_world_database()
    clover = APP / "storage" / "clover.xml"
    junit = APP / "storage" / "junit.xml"
    clover.unlink(missing_ok=True)
    # Only the reference tests count toward coverage; the harness suite runs without it.
    for suite, options in (("References", [f"--coverage-clover={clover}"]), ("Harness", [])):
        junit.unlink(missing_ok=True)
        tests = subprocess.run(
            ["php", "-d", "pcov.enabled=1", "-d", f"pcov.directory={APP}", "-d", "pcov.exclude=~/vendor/~", "vendor/bin/pest", "--colors=never",
             f"--testsuite={suite}", f"--log-junit={junit}", *options],
            cwd=APP, capture_output=True, text=True,
        )
        if junit.exists():
            for case in ElementTree.parse(junit).getroot().iter("testcase"):
                problem = case.find("failure")
                problem = problem if problem is not None else case.find("error")
                if problem is not None:
                    message = (problem.get("message") or problem.text or "").strip().splitlines()[0][:300]
                    errors.append(f"{case.get('file', '').replace(str(APP) + '/', '')} > {case.get('name')}: {message}")
        else:
            errors.append(f"pest did not run the {suite} suite: {(tests.stderr or tests.stdout)[-2000:]}")
    coverage, warnings = uncovered(result["members"], clover) if clover.exists() else (0.0, [])
    if coverage < MINIMUM_COVERAGE:
        errors.append(f"reference tests cover {coverage:.1f}% of the example methods, below {MINIMUM_COVERAGE}%")

    tests = sum(len(re.findall(r"^(?:it|test)\(", found[label], re.M)) for label in result["tests"])
    print(f"examples={len(found)} tests={tests} coverage={coverage:.1f}% errors={len(errors)} warnings={len(warnings)}")
    for error in errors:
        print(error)
    for warning in warnings:
        print(f"warning: {warning}")
    sys.exit(bool(errors))


if __name__ == "__main__":
    main()
