# Controller Tests: Ordered Index Block

Ordered Pest GET index cases: access, scoped ancestors, page contracts, ordered or paginated collections, historical relations, pending and inactive rows, then tenant, parent and deletion filters. Canonical names for composing only the inspected behaviors.

## Case Order

1. `requires authentication`
2. `prevents listing from an unrelated tenant`
3. `returns not found when the ancestor belongs to another tenant`
4. `returns not found when the ancestor is soft deleted`
5. `returns not found when the parent belongs to another ancestor in the same tenant`
6. `returns not found when the parent belongs to another tenant`
7. `returns not found when the parent is soft deleted`
8. `shows the index page`
9. `lists records in the configured order`
10. `paginates live records with the newest record first`
11. `includes historical relations in the list`
12. `lists pending records newest first`
13. `includes inactive records in the list`
14. `lists records under an inactive parent`
15. `lists records under an inactive ancestor`
16. `excludes records from other tenants`
17. `excludes records from other parents in the same tenant`
18. `excludes records whose tenant does not match their parent tenant`
19. `excludes records whose parent belongs to another tenant`
20. `excludes foreign and soft deleted records`
21. `excludes records from other parents under the same ancestor`
22. `excludes records from other ancestors in the same tenant`
23. `excludes soft deleted records`

## Contracts

Route roles are tenant / ancestor / parent / collection. Keep valid URL parameters for guests and outsiders; authenticate the URL tenant for 404 cases. Omit absent route levels.

Use `shows the index page` for the applicable component, IDs, count, row fields and enum options. A specialized example already includes its stated page assertions; add a plain page test only for an uncovered contract. Reuse names across controllers and qualify only distinct cases within one block.

Assert membership with exact count and identities when the contract calls for both. Raw collections use `records.0.id`; paginated resources use `records.data.0.id`. Neither position alone nor count alone proves both. Keep timestamp, enum, missing-field and pagination assertions intact.

Inactive rows and ancestors can remain visible. Historical relations concern live principal rows with deleted relations. Keep wrong-parent binding, ordinary collection scope and both conflicting-ownership directions separate. Deep fixtures must satisfy unrelated uniqueness and range constraints.

Examples use synthetic models, `login()` for an outsider, `login(team: ...)` for membership and `sqid`. Adapt these to actual namespaces, helpers, suite paths and inspected contracts.
