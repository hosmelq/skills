---
name: laravel-project-patterns
description: "Use Laravel model, enum, action, FormRequest, controller, HTTP Resource and policy implementation examples and Pest test patterns for controllers, models, HTTP Resources, enums, actions, jobs, middleware, helpers, console commands, architecture and listeners."
---

# Laravel Project Patterns

Adapt fictional domain types and helpers to inspected contracts, keep reference-project identities confidential,
and preserve real APIs such as `sqid` and `HasSqid`. For uncovered behavior,
report the gap and follow the project’s conventions.

Inspect affected code, nearby examples and applicable configuration; use their actual namespaces and, for tests, suite paths.

## Retrieve Missing Patterns

Reuse examples and candidate descriptions already in this context first. For
missing behavior, write temporary JSON with `request`, `paths` (actual project
paths), and `code_context` (concise inspected facts), then use:

```shell
uv run <skill-directory>/scripts/search.py search --task-file=<task.json> --session=<session.json>
uv run <skill-directory>/scripts/search.py read --session=<session.json> --ids <id> <id>
```

Search returns five ranked candidates; new IDs include applicability and token
cost, known IDs only `id` and `read`. Select the smallest applicable set. For implementation use the
[model guide](references/models/00-model-implementation-order.md),
[enum guide](references/enums/00-enum-implementation-order.md),
[action guide](references/actions/00-action-implementation-order.md),
[request guide](references/requests/00-request-implementation-order.md),
[controller guide](references/controllers/00-controller-implementation-order.md),
[resource guide](references/http-resources/00-resource-implementation-order.md) or
[policy guide](references/policies/00-policy-implementation-order.md). For a
complete test block select its ordered checklist: [create](references/tests/controllers/00-create-test-order.md),
[destroy](references/tests/controllers/12-destroy-test-order.md), [edit](references/tests/controllers/22-edit-test-order.md),
[index](references/tests/controllers/34-index-test-order.md), [show](references/tests/controllers/47-show-test-order.md),
[store](references/tests/controllers/61-store-00-test-order.md), [update](references/tests/controllers/62-update-00-test-order.md),
[authentication](references/tests/controllers/63-auth-00-test-order.md) or [move](references/tests/controllers/64-move-00-test-order.md).
[Initial](references/tests/controllers/65-initial-record.md) and [default](references/tests/controllers/66-default-record.md)
selection include their ordered cases. For model tests use the
[model checklist](references/tests/models/00-model-test-order.md); for HTTP Resources use the
[resource checklist](references/tests/http-resources/00-resource-test-order.md); for enum tests use the
[enum checklist](references/tests/enums/00-enum-test-order.md); for action tests use the
[action checklist](references/tests/actions/00-action-test-order.md); for jobs use the
[job checklist](references/tests/jobs/00-job-test-order.md); for middleware use the
[middleware checklist](references/tests/middleware/00-middleware-test-order.md); for support helpers use the
[support checklist](references/tests/support/00-support-test-order.md); for console commands use the
[console checklist](references/tests/console/00-console-test-order.md); for architecture use the
[complete ordered example](references/tests/architecture/00-architecture-test-order.md).
[Media dimensions](references/tests/listeners/01-media-dimensions.md) belong to Listener tests.
Keep standalone tests at file scope when the suite uses no `describe`.
A specialized example covers its stated assertions; retrieve another only for
missing contracts. Search uncovered requirements with relevant
facts; `--limit=10` can broaden a shortlist. Rank and shortlist size do not prove
coverage. Do not glob the catalog or open its linked files directly.

Read emits complete Markdown examples and a receipt; `blocked` IDs need another
batch (4,000 source tokens per response). `read: true` means already emitted:
reuse that content; repeated reads omit it. Use one temporary session outside the
skill per task/agent context. After compaction use a new session to restore
descriptions, or `read --repeat` if the needed ID remains known. Receipts are not
model memory. Changed sources require searching again.

For tests, keep canonical names, documented case order, fixtures, assertions and datasets.
Keep behavior qualifiers (parent/ancestor, case-insensitive, relation state). Add
`: field_name` only to distinguish separate tests within one block; named dataset
rows already distinguish fields. Do not introduce behavior to match an example.
Prefer one line for simple calls of up to 100 characters; wrap longer calls or
when a break improves readability.

Inspect factory definitions: use `recycle()` for existing default parents and
derive unmodified intermediates from the created record. Keep trashed parents
explicit. Use `for()` for optional or role-specific relations, root-only tenant
assignment and deliberate mismatches. Recycling reaches nested factories, matches
model type and leaves fixed/derived foreign keys unchanged; verify option-list
membership and avoid same-type pools when an exact parent matters.

First search installs dependencies, downloads the model and builds the index; later searches refresh changed Markdown. Read loads no model. Resolve failures using the [search guide](docs/search.md). [Benchmark evidence](docs/benchmark.md) is optional. Maintainers may inspect catalog files directly.
