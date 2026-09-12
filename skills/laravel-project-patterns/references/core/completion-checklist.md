# Completion Checklist

## When To Use

Use relevant items from this leaf when checking an implemented Laravel change.
The affected behavior and current project conventions determine applicability.

## Pattern

- New migration matches the local style and avoids unsupported rollback/FK patterns.
- New or changed models preserve the project's relationship, cast, type, and route-key/public-ID contracts.
- Factory can create a valid row with realistic defaults and coherent relationship ownership.
- Project tooling changes preserve the documented build graph, generated-code ordering, staged hooks, and local process definitions.
- Frontend changes preserve the active stack's typed request/response contracts, accessible server-error mapping, dependent selection resets, and pending-state cleanup. Apply Inertia/Wayfinder examples only where those tools are used.
- View-shell changes preserve existing application slots, build entrypoints, metadata, fonts, and required production/authenticated scripts.
- Where React Email exports Blade views/assets, changes preserve the source/generated boundary and use the project's configured paths and commands.
- Verification covers affected behavior in the owning suites. Add browser tests when an existing browser suite and the changed interaction, focus, keyboard, or visual behavior require them. Report any material runtime behavior that remains unverified.
- Related model/resource/controller tests are updated when system behavior or serialized contracts change; do not add paired model relationship tests just to prove Laravel relationship wiring.
- Test names, fixtures, and assertions follow the current project's conventions for comparable scenarios.
- Persistence assertions prove changed durable state without duplicating the same contract. Refresh an existing model instance only when assertions must observe a later database change through it.
- Controller coverage preserves the current binding and ownership contracts. Where those contracts reject inconsistent `Workspace`/ancestor ownership on nested children, retain the same-parent mismatched-ownership `404` case and the corresponding list exclusion.
- Run the project's required checks and smallest relevant tests using its configured runtime, suite paths, and options. Use its configured formatter for changed PHP files.

## Related References

- [`references/tests/README.md`](../tests/README.md)
- [`references/MAP.md`](../MAP.md)
