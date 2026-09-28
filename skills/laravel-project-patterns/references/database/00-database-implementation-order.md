# Database Implementation Order

Read only the matching example. Keep dependent factory attributes in evaluation order. Use `recycle($existing)` for shared generated relationships and `for($model, 'relation')` for a specific association. Migrations here define `up()`; these examples do not implement rollback. Indexed `foreignId()` columns do not add foreign key constraints by themselves.

1. [Define Defaults](01-factory-defaults.md).
2. [Configure User State](02-factory-user-state.md).
3. [Create a Child Record](03-factory-child-record.md).
4. [Generate Geographic Data](04-factory-geographic-data.md).
5. [Keep Geographic Fields Consistent](05-factory-geographic-state.md).
6. [Derive Related Attributes](06-factory-dependent-attributes.md).
7. [Configure Review State](07-factory-review-state.md).
8. [Configure Expiration and Usage](08-factory-expiration-state.md).
9. [Configure Numeric Ranges](09-factory-range-state.md).
10. [Configure Status Flags](10-factory-status-state.md).
11. [Generate Dependent Bounds](11-factory-dependent-range.md).
12. [Configure Measurements and Value](12-factory-measurement-state.md).
13. [Reuse Related Records](13-factory-related-records.md).
14. [Enable PostgreSQL Extensions](14-schema-extensions.md).
15. [Define Typed Defaults](15-schema-defaults.md).
16. [Store Case-Insensitive Identities](16-schema-identities.md).
17. [Constrain Membership Pairs](17-schema-membership.md).
18. [Store Polymorphic Data](18-schema-polymorphic-data.md).
19. [Store Cache and Queue State](19-schema-runtime-storage.md).
20. [Add Nullable Columns](20-schema-add-columns.md).
21. [Scope Uniqueness to Live Rows](21-schema-scoped-uniqueness.md).
22. [Constrain a Selected State](22-schema-selected-state.md).
23. [Constrain Coordinate Pairs](23-schema-coordinate-pairs.md).
24. [Index a Normalized Expression](24-schema-functional-index.md).
25. [Constrain Related Values](25-schema-value-groups.md).
26. [Prevent Overlapping Ranges](26-schema-range-exclusion.md).
27. [Store Nullable Structured Data](27-schema-structured-data.md).
28. [Compose Related Fixtures](28-seeder-composition.md).
