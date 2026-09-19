---
name: laravel-project-patterns
description: "Apply adopted Laravel code and test conventions through bounded hybrid search. Use when project guidance or the task calls for this catalog's patterns."
---

# Laravel Project Patterns

Use this catalog to support the current project's conventions. Apply an example
only when the live code supports its architecture, library, preconditions and
behavior ownership. Keep synthetic examples and reference-project identities
confidential.

The reference catalog is currently empty while it is rebuilt incrementally.
The search workflow below applies once new references exist; until then use the
project's own code and instructions.

## Find Applicable Guidance

The [hybrid search](docs/search.md) is the required catalog
entrypoint. Before loading pattern references:

1. Inspect project guidance, the affected code and the closest comparable test.
   Use Composer autoloading and the active test configuration to locate code;
   preserve actual namespaces, module boundaries, suite roots and test commands.
2. Supply the literal task, actual paths and concise inspected code or contract
   facts in a JSON file with `request`, `paths` and `code_context`.
3. Run the search and read its returned source content:

   ```shell
   uv run <skill-directory>/scripts/search.py search --task-file=/path/to/task.json
   ```

The first search downloads and verifies the embedding model and builds the local
index automatically; later searches reuse it. For a read-only installation,
configure a writable `--cache-dir` as described
in the search guide and use that same option for subsequent commands.

Search reads and ranks the Markdown outside model context, then returns complete
candidate references within 4,000 source tokens. Paths such as `tests-new/**`
and `src/<Domain>/**` do not require aliases. Catalog reference paths never
prescribe where the current project's files belong.

Check each candidate's conditions against the live contract. A high rank or a
nonempty result does not establish applicability or complete coverage. If a
specific requirement remains uncovered, search that requirement with its relevant
code facts; reuse evidence already read. Do not repeatedly load the same packet,
raise the budget, browse the catalog with globs, or follow unread reference links
to bypass retrieval. Search live project code independently as needed.

On a search error, report the cause and fix the search setup or narrow an
oversized input. Continue independent work that does not require the missing
guidance. Do not silently substitute manual catalog browsing or lexical search.

Complete the task using the project's required checks. When maintaining this
skill itself, inspect the necessary files directly and use the Python validator. The
[benchmark note](docs/benchmark.md) records why this retrieval method was chosen.
