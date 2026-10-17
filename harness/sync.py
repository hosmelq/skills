#!/usr/bin/env python3
"""Copy reference examples that fully define a fixture file into the fixture application.

Classes shown by a single example are copied as is. Models show only the members a
reference needs, so they, merged classes and migrations are maintained by hand and
checked by run.py.
"""

import re
import sys
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parent
APP = ROOT / "laravel"
SKILL = ROOT.parent / "skills" / "laravel-project-patterns"
PATHS = {"App\\": "app/", "Database\\Factories\\": "database/factories/", "Database\\Seeders\\": "database/seeders/", "Tests\\": "tests/"}

sys.path.insert(0, str(SKILL / "scripts"))

from validate import markdown  # noqa: E402

classes = defaultdict(list)
for source in sorted((SKILL / "references").rglob("*.md")):
    blocks = []
    markdown(source.read_text(), source, [], blocks)
    for code in blocks:
        namespace = re.search(r"^namespace ([\w\\]+);", code, re.M)
        name = re.search(r"^(?:final |abstract )?(?:class|enum|interface|trait) (\w+)", code, re.M)
        if namespace and name:
            classes[f"{namespace.group(1)}\\{name.group(1)}"].append(code)

for name, codes in sorted(classes.items()):
    if len(codes) != 1 or (name.startswith("App\\Models\\") and not name.startswith("App\\Models\\Concerns\\")):
        continue
    for prefix, directory in PATHS.items():
        if name.startswith(prefix):
            target = APP / directory / (name[len(prefix):].replace("\\", "/") + ".php")
            if not target.exists() or target.read_text() != codes[0]:
                target.parent.mkdir(parents=True, exist_ok=True)
                target.write_text(codes[0])
                print(f"updated {target.relative_to(APP)}")
