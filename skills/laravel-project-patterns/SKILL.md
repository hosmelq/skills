---
name: laravel-project-patterns
description: "Apply the catalog's Laravel controller create-test conventions through selective local retrieval when project guidance or the task calls for these patterns."
---

# Laravel Project Patterns

Current coverage: HTTP controller GET `create` tests in Pest/Inertia. For other
blocks or resource/request tests, report that the catalog does not cover them yet
and continue from the project's conventions without searching this catalog.

Examples are fictional. Adapt models, helpers, factories, enums, routes, props and
identifiers to inspected code; do not introduce contracts to match an example.
Keep reference-project identities confidential.

## Retrieve Only What The Task Needs

1. Inspect project guidance, affected code and the closest comparable test. Locate
   the active suite through Composer and test configuration; preserve actual
   module boundaries, namespaces and paths, including `tests-new` or DDD layouts.
2. Write a temporary JSON file with `request` (requested behaviors), `paths`
   (actual project paths) and `code_context` (concise inspected code/facts).
3. Use the required catalog entrypoint to find descriptions, then read only IDs
   whose conditions match the live contract:

   ```shell
   uv run <skill-directory>/scripts/search.py search --task-file=<task.json> --session=<session.json>
   uv run <skill-directory>/scripts/search.py read --session=<session.json> --ids <id> <id>
   ```

Search returns up to five short descriptions and source sizes, never PHP bodies.
Select the smallest set that covers the requirements; rank is not applicability.
A specialized page example already covers its listed component, IDs and props;
load another positive-page example only for a missing contract.
For a block with several behaviors, use `search --limit=10`. Include the
[ordered case checklist and base example](references/tests/controllers/00-create-test-order.md)
in that selection. For a focused edit, retrieve only the missing behavior.
If a requirement is absent from the descriptions, search that requirement with
its relevant facts using the same session. A broad shortlist is not proof of
complete coverage. Do not read every candidate or bypass selection with catalog
globs or linked-file reads. Search live project code independently as needed.

`read` returns only selected, complete sources, up to 4,000 source tokens per
response. It lists IDs that did not fit in `blocked`; request those separately.
It never substitutes other sources. `read: true` means that source was already
returned in this session; reuse it. Repeated reads omit unchanged content.
Use a unique session file outside the skill for each task/agent context. If
compaction loses a needed example, reread its ID with `--repeat` or use a fresh
session; another agent's receipts do not supply your context.

Assemble applicable cases in their documented order, retaining canonical test
names such as `shows the create page`. Authentication and authorization precede
binding and access restrictions; positive cases precede dependent-select,
filtering and read-only variants. Preserve fixture/setup, assertions and datasets.

The first search installs dependencies, downloads the verified embedding model
and builds the local index automatically. Later searches refresh changed Markdown;
`read` does not load the model. A search failure must be reported and resolved,
not silently replaced with another retrieval method. The
[search guide](docs/search.md) is only needed for setup, recovery or maintenance;
normal use requires no additional guide. The [benchmark note](docs/benchmark.md)
records the evidence and limits. Maintainers may inspect catalog files directly.
