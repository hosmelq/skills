# Models: Implementation Order

Implement Eloquent model code using structure, defaults and casts, relations, lifecycle concerns and conditional behaviors. Select only the contracts present in the inspected model and schema.

Inspect the model, migration, factory, application bootstrap and installed APIs. Examples show complete focused classes or traits; merge relevant members into the existing model. Adapt synthetic names and configuration, and retain the project's mass-assignment policy. Immutable date annotations assume an immutable application date factory.

Examples use Laravel 13 APIs and PHP 8.5 syntax, including `#[Override]` on properties. Match the project's runtime before adapting them.

Keep strict types, grouped property PHPDoc and factory generics. Order constants and defaults before methods; public static methods before public methods, then protected members. Alphabetize independent imports, keys and peer methods; preserve semantic chains and fallback order. Keep simple calls on one line up to 100 characters.

## Reference Order

1. [Structure and property types](01-model-structure.md)
2. [Defaults and casts](02-defaults-casts.md)
3. [Phone casts and display name](03-phone-casts-display-name.md)
4. [Parent and role relations](04-belongs-to.md)
5. [Child relations and creation defaults](05-child-relations.md)
6. [Membership pivot](06-membership-pivot.md)
7. [Deactivation concern](07-deactivation.md)
8. [Scoped sorting and status eligibility](08-sortable-status.md)
9. [Code normalization](09-normalized-code.md)
10. [Sqid identity and route binding](10-sqid-identity.md)
11. [Slug route key](11-slug-route-key.md)
12. [Authentication and panel access](12-authentication-model.md)
13. [Membership and current team selection](13-current-team.md)
14. [Prunable records](14-pruning.md)
15. [Lookup models on another connection](15-lookup-connection.md)

Read through the skill's search/read commands; this list defines order, not a requirement to load every example. Model tests use their separate [checklist](../tests/models/00-model-test-order.md). Database constraints belong to the schema, not to these model examples.
