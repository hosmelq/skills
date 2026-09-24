# HTTP Resource Tests: Ordered Contract Checklist

HTTP Resource tests assert exact JSON fields, formats, nested payloads, timestamps and API envelopes. Keep them separate from model and controller tests. `toResource()->toJson()` selects the default resource; `Resource::make($model)` selects an explicit class.

Inspect the resource, casts and factory. Preserve the full expected payload, including Sqids, nulls and phone/date/decimal formats. Examples are alternative shapes; do not add fields absent from the inspected payload.

Start with `formats resource correctly`, then applicable cases in example order: `formats the deactivation timestamp when present`, `formats the deletion timestamp when present`, or `includes a null current tenant when none is assigned`. Match the suite's standalone `it()` style. Separate setup, serialization and assertions with blank lines.

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
