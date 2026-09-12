# Code And Schema Contracts

## When To Use

Use this leaf for repository-safe edits, PHP class shape, Laravel generators,
migrations, and normal Eloquent attribute writes.

## Pattern

- Preserve concurrent changes. Re-read a file immediately before patching it.
- Keep new code aligned to the existing architecture. Do not add base folders, dependencies, or broad abstractions unless the task explicitly needs them.
- Preserve existing finality unless the task changes that contract. For new classes, follow project guidance and comparable classes with the same role; one class family's convention does not establish another's.
- Use the project's configured Laravel generators when practical, then align generated output with local patterns.
- When the repository intentionally omits database foreign key constraints, preserve its indexed relationship-column convention. Establish this from current migrations and project guidance.
- Do not add migration `down()` methods when existing migrations intentionally omit them.
- Do not add `$fillable` or `$guarded` on models when the app globally calls `Model::unguard()`.
- When models are globally unguarded, use `$model->update([...])` for normal persisted attribute mutations in app-owned code; `forceFill(...)->save()` is unnecessary as a mass-assignment workaround. Otherwise preserve the project's actual mass-assignment boundary.

## Related References

- [`references/app/README.md`](../app/README.md)
- [`references/app/Models/README.md`](../app/Models/README.md)
- [`references/database/README.md`](../database/README.md)
