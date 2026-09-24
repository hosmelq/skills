# Model Tests: Ordered Contract Checklist

Model tests cover trait composition, in-memory casts and predicates, persisted lifecycle and constraints, membership and Sqid route binding. Select a cast example matching the inspected attributes.

Inspect the model, factory, migrations and test bootstrap. `new Model()` needs no row, but casts/config may require Laravel; database checks use the project's engine and isolation. Resource and listener tests stay in their own categories even with model fixtures.

Use standalone `it()` cases when the suite has no `describe`. Keep canonical names; order families as listed below and cases as shown in each example. Separate setup, operation and assertions with blank lines; related fixture declarations may stay consecutive. Preserve all fields, assertions and dataset rows, including null, false, zero, empty strings and unloaded relations; keep real APIs such as `HasSqid`.

## Reference Order

1. [Trait Composition](01-traits.md)
2. [Scalar and Value Object Casts](02-scalar-casts.md)
3. [State Enum and Independent Boolean Casts](02-state-casts.md)
4. [Workflow Enum and Timestamp Casts](02-workflow-casts.md)
5. [Decimal and Integer Casts](03-decimal-casts.md)
6. [Range and Rate Casts](03-range-casts.md)
7. [Measurement and Event Timestamp Casts](03-record-casts.md)
8. [Rounding and Minimum Casts](03-rounding-casts.md)
9. [Settings Casts and Defaults](04-settings-defaults.md)
10. [Account Casts and Allowlist](05-account-casts-access.md)
11. [Display Name Precedence](06-display-name.md)
12. [Code Normalization](07-normalization.md)
13. [Initial State Eligibility](08-eligibility.md)
14. [Deactivation Lifecycle](09-deactivation.md)
15. [Computed Sqids](10-sqids.md)
16. [Sqid Route Binding](11-route-bindings.md)
17. [Slug Lifecycle](12-slug-lifecycle.md)
18. [Prunable Records](13-prunable-scope.md)
19. [Coordinate Constraints](14-coordinate-constraints.md)
20. [Measurement Constraints](15-measurement-constraints.md)
21. [Value and Currency Constraints](16-money-constraints.md)
22. [Normalized Contact Uniqueness](17-contact-uniqueness.md)
23. [Case-Insensitive Name Uniqueness](18-case-insensitive-uniqueness.md)
24. [Same-Value Name Uniqueness](19-literal-name-uniqueness.md)
25. [Nullable Reference Uniqueness](20-nullable-uniqueness.md)
26. [Parent-Scoped Uniqueness](21-parent-uniqueness.md)
27. [Assignment and Normalized Code Uniqueness](22-assignment-uniqueness.md)
28. [Persisted Default Selection](23-default-selection.md)
29. [Initial State Constraints](24-initial-selection.md)
30. [Scoped Sort Order](25-scoped-ordering.md)
31. [Membership and Current Selection](26-membership.md)
32. [Range Exclusion Constraints](27-range-exclusion.md)
