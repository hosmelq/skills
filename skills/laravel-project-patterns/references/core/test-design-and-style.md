# Test Design And Style Contracts

## When To Use

Use this leaf for behavior ownership and persistence assertions when changing
tests. Apply Pest-specific syntax and suite conventions only where the project
uses them.

## Pattern

- Follow the project's required checks. Add or update focused tests when they prove changed behavior, an interface contract, or a regression risk; examples alone do not justify new tests.
- Name tests after observable behavior and use comparable live tests for local naming, fixture, and assertion conventions. Compare preconditions, operations, ownership boundaries, and outcomes. The [parallel test examples](../tests/maps/parallel-test-structure.md) illustrate one style; they do not require a matching grammatical template in another project.
- Assert durable database effects with `assertDatabaseHas()`, `assertDatabaseMissing()`, `assertSoftDeleted()`, or `assertModelMissing()` according to the persistence contract. Factory setup does not itself need a database assertion. Returned identity, collection counts, and relationship behavior are separate contracts; avoid proving the same ordinary persisted field twice. Consult the [factory and persistence examples](../tests/maps/persistence-assertions.md) when this distinction matters.
- For a soft-deleted fixture, prefer an existing factory `trashed()` state when deletion side effects are outside the scenario. Keep a soft-deleted parent available for route/login arguments because normal `belongsTo` queries may filter it out. If a child factory derives ownership through a query that cannot see trashed rows, pass the minimum FK/owner IDs needed for a coherent fixture.
- Refresh an already-loaded Eloquent instance only when the test must observe a later database change through that instance. A first relationship access already queries current state; avoid preloading it unnecessarily. Keep a required refresh separate from assertions so the state transition remains clear.
- Follow the project's Pest assertion style. Where it uses expectation chains, `and()` changes the subject and higher-order expectations can keep checks on the same subject together. See `references/tests/Pest.md` for the catalog's examples.
- Place persisted system behavior in the project's owning suite. Where `tests/Integration/Models/**` is used, `references/tests/Integration/Models/README.md` describes its boundary. Avoid tests of generic Laravel relationship mechanics, FK/ID equality, related-model types, or factory/count smoke checks.
- Preserve distinct HTTP, action, model, resource, and database contracts when a change crosses those boundaries. Update affected coverage without adding parallel cases merely for symmetry.

## Related References

- [`references/tests/README.md`](../tests/README.md)
- [`references/tests/Pest.md`](../tests/Pest.md)
- [`references/tests/maps/parallel-test-structure.md`](../tests/maps/parallel-test-structure.md)
- [`references/tests/maps/persistence-assertions.md`](../tests/maps/persistence-assertions.md)
- [`references/tests/Integration/Actions/README.md`](../tests/Integration/Actions/README.md)
- [`references/tests/Integration/Models/README.md`](../tests/Integration/Models/README.md)
