# Move Tests: Ordered Cases

Ordered Pest browser PATCH reorder cases: access, record bindings, complete predecessor validation, group scope and action delegation. Distinguish tenant-wide ordering from within-group ordering and active from inactive start placement.

Keep top-level tests in the order below, selecting the actual scope.

## Case Order

1. `requires authentication`
2. `prevents moving from an unrelated tenant`
3. `returns not found when the record belongs to another tenant`
4. `returns not found when the record is soft deleted`
5. `validates fields`
6. `rejects moving a record after itself`
7. `rejects a predecessor from another tenant`
8. `rejects a soft deleted predecessor`
9. `rejects a nonexistent predecessor`
10. `returns not found when the predecessor belongs to another group`
11. `moves the record after another record in the same tenant`
12. `moves the record after another record in the same group`
13. `moves the record to the start of its group`
14. `moves an inactive record to the start`
15. `moves an inactive record to the start of its group`

## Contracts

Access and field-validation examples apply to both ordering scopes. Keep self-reference, encoded missing ID, raw nonexistent token, foreign tenant, deleted predecessor and different-group fixtures distinct. A predecessor in another group returns 404; field failures redirect back with errors.

Preserve the selected group, typed model-identity arguments and explicit null predecessor. Active and inactive source fixtures are distinct. A mocked action verifies delegation; redirect/toast assertions do not prove persisted ordering. Adapt synthetic route names, helpers and public IDs to the project.
