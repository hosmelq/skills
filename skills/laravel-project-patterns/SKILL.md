---
name: laravel-project-patterns
description: "Apply the catalog's Laravel controller create-test conventions through selective local retrieval when project guidance or the task calls for these patterns."
---

# Laravel Project Patterns

Covers HTTP GET `create` tests in Pest/Inertia. For other behavior, report the
coverage gap and follow project conventions without searching this catalog.
Examples are fictional: adapt to inspected contracts and keep reference-project
identities confidential.

Inspect affected code, nearby tests and Composer/test configuration. Preserve
actual namespaces and suite paths, including `tests-new` and DDD modules.

## Retrieve Missing Patterns

Reuse examples and candidate descriptions already in this context first. For
missing behavior, write temporary JSON with `request`, `paths` (actual project
paths), and `code_context` (concise inspected facts), then use:

```shell
uv run <skill-directory>/scripts/search.py search --task-file=<task.json> --session=<session.json>
uv run <skill-directory>/scripts/search.py read --session=<session.json> --ids <id> <id>
```

Search returns five ranked candidates; new IDs include applicability and token
cost, known IDs only `id` and `read`. Select the smallest applicable set. For a
complete block include the [ordered checklist/base](references/tests/controllers/00-create-test-order.md).
A specialized positive example includes its stated component/IDs/props; retrieve
another only for missing assertions. Search uncovered requirements with relevant
facts; `--limit=10` can broaden a shortlist. Rank and shortlist size do not prove
coverage. Do not glob the catalog or open its linked files directly.

Read emits complete Markdown examples and a receipt; `blocked` IDs need another
batch (4,000 source tokens per response). `read: true` means already emitted:
reuse that content; repeated reads omit it. Use one temporary session outside the
skill per task/agent context. After compaction use a new session to restore
descriptions, or `read --repeat` if the needed ID remains known. Receipts are not
model memory. Changed sources require searching again.

Keep canonical test names, documented case order, fixtures, assertions and datasets.
Qualify names only to distinguish cases within one block; do not introduce behavior
to match an example.

First search installs dependencies/model and builds the local index automatically;
later searches refresh changed Markdown. Read loads no model. Resolve search
failures rather than silently bypassing retrieval. Read the [search guide](docs/search.md)
only for setup, recovery or maintenance; [benchmark evidence](docs/benchmark.md)
is optional. Maintainers may inspect catalog files directly.
