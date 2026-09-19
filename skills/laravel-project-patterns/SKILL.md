---
name: laravel-project-patterns
description: "Apply adopted Laravel code and test conventions through bounded hybrid search. Use when project guidance or the task calls for this catalog's patterns."
---

# Laravel Project Patterns

Use this catalog to support the current project's conventions. Apply an example
only when the live code supports its architecture, library, preconditions and
behavior ownership. Keep synthetic examples and reference-project identities
confidential.

The catalog is being rebuilt incrementally. It currently covers the
[ordered controller `create` test block](references/tests/controllers/00-create-test-order.md).
Examples use a fictional domain; preserve the current project's names and setup.

## Find Applicable Guidance

Use `scripts/search.py` as the required catalog entrypoint. The command below is
enough for normal use; consult the [search guide](docs/search.md) for setup,
cache configuration or errors. Before loading pattern references:

1. Inspect project guidance, the affected code and the closest comparable test.
   Use Composer autoloading and the active test configuration to locate code;
   preserve actual namespaces, module boundaries, suite roots and test commands.
2. Supply the literal task, actual paths and concise inspected code or contract
   facts in a JSON file with `request`, `paths` and `code_context`.
3. Run the search and read its returned source content:

   ```shell
   uv run <skill-directory>/scripts/search.py search --budget=3200 --task-file=/path/to/task.json
   ```

The first search downloads and verifies the embedding model and builds the local
index automatically; later searches reuse it. For a read-only installation,
configure a writable `--cache-dir` as described
in the search guide and use that same option for subsequent commands.

Search reads and ranks the Markdown outside model context. Use the 3,200-token
budget above for the current create references; the CLI ceiling is 4,000.
It returns complete candidate references. Paths such as `tests-new/**`
and `src/<Domain>/**` do not require aliases. Catalog reference paths never
prescribe where the current project's files belong.

Check each candidate's conditions against the live contract. A high rank or a
nonempty result does not establish applicability or complete coverage. If a
specific requirement remains uncovered, search that requirement with its relevant
code facts; reuse evidence already read. Do not repeatedly load the same packet,
raise the budget, browse the catalog with globs, or follow unread reference links
to bypass retrieval. Search live project code independently as needed.

When assembling a controller `create` block, preserve this test order:
authentication, authorization, scoped/soft-deleted parents, lifecycle restrictions,
positive page/options, then dependent-select, filtering and read-only variants.
Use the same test names for equivalent behavior, including `shows the create page`;
the controller file supplies the entity context.

On a search error, report the cause and fix the search setup or narrow an
oversized input. Continue independent work that does not require the missing
guidance. Do not silently substitute manual catalog browsing or lexical search.

Complete the task using the project's required checks. When maintaining this
skill itself, inspect the necessary files directly and use the Python validator. The
[benchmark note](docs/benchmark.md) records why this retrieval method was chosen.
