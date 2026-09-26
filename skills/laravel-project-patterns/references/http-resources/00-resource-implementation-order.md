# HTTP Resource Implementation: Reference Order

Choose the inspected response shape; scalar-only and relation-bearing examples are alternatives. Keep exact keys, Sqids, nulls, false, zero and empty arrays. Return cast attributes directly; the model supplies enum, date and decimal behavior. Sort independent output keys and imports alphabetically, with public methods before private helpers.

Use complete classes, including lookup helpers and caches. `whenLoaded()` omits an unloaded relation; loaded null remains null. Direct relationship access may query. The controller selects eager loading and historical scope; a resource does not establish ownership or authorization.

Ordinary `JsonResource::toJson()` and HTTP response wrapping are separate contracts. Inspect the application's wrapping configuration; paginated responses retain their data/meta/links structure. `JsonApiResource` uses its own attributes/id/type envelope. Keep the two resource kinds separate.

## Reference Order

1. [Contact Fields](01-contact-fields.md)
2. [Flat Address with Cached Labels](02-flat-address.md)
3. [Nested Address with Cached Labels](03-nested-address.md)
4. [Settings and National Phone Format](04-settings.md)
5. [Account and Nullable Current Tenant](05-account-current-tenant.md)
6. [Required Related Records](06-related-actor.md)
7. [Code and Label Fields](07-code-and-label.md)
8. [Ordered Record Fields](08-ordered-item.md)
9. [Stored State Fields](09-state-fields.md)
10. [Decimal Values and Integer Quantity](10-decimal-fields.md)
11. [Range Bounds and Amount](11-range-fields.md)
12. [Rounding Settings](12-rounding-fields.md)
13. [Lifecycle and Duration Fields](13-lifecycle-fields.md)
14. [Measurements and Event Timestamp](14-measurements.md)
15. [JSON:API Attributes, Identifier and Type](15-api-envelopes.md)
16. [Derived Final-State Flag](16-derived-state.md)
17. [Conditional Parent Resource](17-loaded-parent.md)
18. [Conditional Assignment Relations](18-loaded-assignment.md)
19. [Conditional Group Resource](19-loaded-group.md)
20. [Conditional Relations with Distinct Roles](20-loaded-relations.md)

[Laravel resources](https://laravel.com/docs/13.x/eloquent-resources) documents these APIs. Use the separate HTTP Resource test checklist for exact serialized assertions.
