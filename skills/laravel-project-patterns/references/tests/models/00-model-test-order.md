# Model Tests: Ordered Contract Checklist

Model and concern tests cover trait composition, in-memory casts and predicates, persisted lifecycle and database constraints, membership and Sqid route binding. Choose a focused cast example for each inspected attribute vector.

Inspect the model, factory, migrations, bootstrap and configured suite. Preserve actual paths, including DDD modules and `tests-new`; `new Model()` needs no row but casts/config may still require Laravel. Database tests use the project's real engine and isolation. Keep HTTP Resources and Listeners in their own categories, even when their fixtures are models.

Keep standalone `it()` declarations when the suite has no `describe`. Order applicable families as below and cases as shown in each example; reuse canonical names. Separate setup, operation and assertions with a blank line; related fixture declarations may be consecutive. Preserve every field, assertion and dataset row: null, false, zero, empty strings and unloaded relations differ. App wrappers such as `HasSqid` retain their real API names.

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
