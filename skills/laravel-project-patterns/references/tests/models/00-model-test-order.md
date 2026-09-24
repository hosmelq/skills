# Model Tests: Ordered Contract Checklist

Choose model test examples by execution boundary: in-memory casts and predicates, persisted lifecycle and constraints, HTTP route binding, model-backed resource serialization or event effects. This checklist orders the supported families; each selected reference supplies complete canonical cases.

Inspect the model, factory, migrations, test bootstrap and configured suite before choosing. Keep the existing paths, including DDD modules or `tests-new`; folder names alone do not establish isolation. `new Model()` avoids row creation, while casts/config may still need Laravel. Queries, factories, constraints, membership and event effects need the suite’s isolated database. Preserve its real engine and time/storage setup.

Use standalone `it()` declarations when the suite has no `describe`. Within a model file keep applicable families in the numbered order below, and cases in their example order. Reuse exact names; add a field or role qualifier only when needed to distinguish tests in that file. Do not add unrelated capabilities to match a template.

Separate fixture setup, operation and assertions with a blank line; related fixture declarations may be consecutive. Keep every dataset row and assertion. `null`, `false`, zero, empty strings and unloaded relations are different contracts. Named constraints belong to database tests; raw model exceptions and HTTP status codes are separate boundaries.

## Reference Order

1. [Trait Composition](01-traits.md)
2. [Scalar and Value Object Casts](02-scalar-casts.md)
3. [Decimal and Integer Casts](03-decimal-casts.md)
4. [Settings Casts and Defaults](04-settings-defaults.md)
5. [Account Casts and Allowlist](05-account-casts-access.md)
6. [Display Name Precedence](06-display-name.md)
7. [Code Normalization](07-normalization.md)
8. [Initial State Eligibility](08-eligibility.md)
9. [Deactivation Lifecycle](09-deactivation.md)
10. [Computed Public Identifiers](10-public-identifiers.md)
11. [Public Identifier Route Binding](11-route-bindings.md)
12. [Slug Lifecycle](12-slug-lifecycle.md)
13. [Prunable Records](13-prunable-scope.md)
14. [Coordinate Constraints](14-coordinate-constraints.md)
15. [Measurement Constraints](15-measurement-constraints.md)
16. [Value and Currency Constraints](16-money-constraints.md)
17. [Normalized Contact Uniqueness](17-contact-uniqueness.md)
18. [Case-Insensitive Name Uniqueness](18-case-insensitive-uniqueness.md)
19. [Same-Value Name Uniqueness](19-literal-name-uniqueness.md)
20. [Nullable Reference Uniqueness](20-nullable-uniqueness.md)
21. [Parent-Scoped Uniqueness](21-parent-uniqueness.md)
22. [Assignment and Normalized Code Uniqueness](22-assignment-uniqueness.md)
23. [Persisted Default Selection](23-default-selection.md)
24. [Initial State Constraints](24-initial-selection.md)
25. [Scoped Sort Order](25-scoped-ordering.md)
26. [Membership and Current Selection](26-membership.md)
27. [Range Exclusion Constraints](27-range-exclusion.md)
28. [Media Event Effects](28-media-events.md)
29. [Contact Resource Fields](29-resource-contact.md)
30. [Flat Address Resource](30-resource-flat-address.md)
31. [Nested Address Resource](31-resource-nested-address.md)
32. [Settings Resource](32-resource-settings.md)
33. [Account and Current Tenant Resource](33-resource-account.md)
34. [Related Actor Resource](34-resource-actor.md)
35. [Code Resource and Loaded Relations](35-resource-code-relations.md)
36. [Ordered Item Resource](36-resource-ordered-item.md)
37. [State Resource](37-resource-state.md)
38. [Line Resource and Optional Relation](38-resource-line.md)
39. [Range Resource](39-resource-range.md)
40. [Rule Resource and Loaded Parent](40-resource-rule.md)
41. [Resource Deletion Timestamp](41-resource-lifecycle.md)
42. [Record Resource Fields](42-resource-record.md)
43. [Loaded Relations and Nulls](43-resource-loaded-relations.md)
44. [Historical Related Resources](44-resource-history.md)
45. [API Resource Envelopes](45-api-resource-envelope.md)

Resource examples assert exact payloads for their fictional schemas; retain the consumer’s complete inspected fields and relation roles. API wrappers, loaded-null relations and historical relations have separate examples. These references cover the observed contracts, not every feature available in Eloquent.
