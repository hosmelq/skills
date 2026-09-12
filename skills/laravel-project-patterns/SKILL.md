---
name: laravel-project-patterns
description: "Apply adopted Laravel conventions through a bounded context router. Use when project guidance or the task calls for this catalog's code and test patterns."
---

# Laravel Project Patterns

Use this catalog to support the current project's conventions. Its examples
describe particular architectures, libraries, and test styles; apply them only
when project guidance or live code supports the same contract. Preserve
synthetic examples and keep reference-project identities confidential.

## Applying The Catalog

Start with project guidance, the affected live code, and comparable siblings.
Compare behavior ownership, preconditions, route depth, binding, transport, and
outcome. A nearby directory alone does not establish an equivalent pattern.

The [`context router`](references/context-resolver.md) is the required entrypoint
for this catalog. Before reading, searching, or listing pattern references, run
it with the affected project paths:

```shell
php <skill-directory>/scripts/context.php --path=<affected-project-path>
```

- Read only the exact selected references and applicable gate sections returned
  by the router. Expand a returned frontier with `--expand`, then use `--select`
  for a child it lists before reading that child. Keep earlier selections when
  descending further.
- Keep the default reference, word, and frontier limits. Narrow broad queries
  and page frontiers with `--offset`; do not raise limits to load the catalog.
  Obtain selected pattern text through `--include-content`, which checks the
  word budget. Do not read selected files directly or concatenate them; metadata
  alone does not authorize loading their content.
- Do not discover pattern references through `rg`, `find`, globs, directory
  listings, guessed paths, the map, or unrestricted link following. Search live
  project code as needed; that does not authorize searching the catalog.
- When affected paths change, refresh the routing query before using references
  for the new surface. Reuse already-read guidance when its content still applies.
- If routing fails or a path is unsupported, report the gap and stop catalog
  access for that surface. Continue independent work that does not depend on the
  missing guidance; do not invent a matching path or fall back to manual browsing.

The router's usage guide is the bootstrap reference for its command syntax.
The links below document ownership and reachability; they do not authorize
reading a pattern before the router selects it. Read catalog-maintenance files
directly only when the requested task changes the catalog itself.

## Core Contracts

- Match class shape, migrations, generated files, and mass-assignment behavior
  to the live project: [`code and schema`](references/core/code-and-schema.md).
- Preserve distinct HTTP, action, and persistence contracts when selecting
  tests: [`test design`](references/core/test-design-and-style.md).
- Keep HTTP entrypoint coverage when actions own persistence; preserve scoped
  binding, public IDs, request normalization, and public responses:
  [`HTTP boundaries`](references/core/http-and-request-boundaries.md).
- Keep action inputs and guards at their owning boundary. Introduce concurrency
  mechanisms only for an evidenced invariant and complete competing-operation
  protocol:
  [`actions and concurrency`](references/core/actions-and-concurrency.md).

## References

- [`project`](references/project/README.md): routes, configuration, tooling,
  localization, bootstrap, public files, and seeders.
- [`database`](references/database/README.md): migrations and factories.
- [`app`](references/app/README.md): PHP under `app/**`.
- [`resources`](references/resources/README.md): JavaScript, Blade, React
  Email, TypeScript, and CSS.
- [`tests`](references/tests/README.md): suite ownership, paths, and support.
- [`controller tests`](references/tests/Feature/Http/Controllers/README.md):
  action, route depth, transport, and validation examples.
- [`core`](references/core/README.md): cross-cutting contracts and completion.

Complete the authorized task using the project's required checks and relevant
behavioral verification. The [completion checklist](references/core/completion-checklist.md)
is returned as a gate; apply only its relevant items. Use the
[reference structure](references/README.md) when maintaining this catalog.
