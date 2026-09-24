# HTTP Resource Tests: Ordered Contract Checklist

HTTP Resource tests assert serialized JSON fields, formats, timestamps, nested payloads and explicit JSON:API envelopes. Keep these in the project's HTTP Resource test category, separate from model casts and controller responses.

Inspect the resource class, its model casts, factory and configured suite; preserve actual paths, including DDD modules and `tests-new`. `toResource()->toJson()` selects the model's default resource; `Resource::make($model)` selects an explicit class. Preserve full expected arrays, Sqids, nulls, scalar types and phone/date/decimal formats.

Use standalone `it()` declarations where the suite does. Start with `formats resource correctly`; then add applicable `formats the deactivation timestamp when present`, `formats the deletion timestamp when present`, or `includes a null current tenant when none is assigned` in the example order. Keep setup, serialization and assertions separated by a blank line. Examples are alternatives for distinct resource shapes; adapt their synthetic fields to the inspected payload without inventing extra fields.

## Reference Order

1. [Contact Resource Fields](01-contact-fields.md)
2. [Flat Address Resource](02-flat-address.md)
3. [Nested Address Resource](03-nested-address.md)
4. [Settings Resource](04-settings.md)
5. [Account and Current Tenant Resource](05-account-current-tenant.md)
6. [Related Actor Resource](06-related-actor.md)
7. [Code and Label Fields](07-code-and-label.md)
8. [Ordered Item Resource](08-ordered-item.md)
9. [State Resource](09-state.md)
10. [Decimal Fields and Integer Quantity](10-decimal-fields.md)
11. [Range Resource](11-range.md)
12. [Rounding Fields](12-rounding.md)
13. [Resource Deletion Timestamp](13-lifecycle.md)
14. [Record Resource Fields](14-measurements.md)
15. [API Resource Envelopes](15-api-envelopes.md)
