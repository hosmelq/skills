# Controller Tests: Ordered Destroy Block

Ordered DELETE tests for entity deletion and reactivation through a deactivation subresource: access, bindings, policy, action errors, and success responses.

Route roles are tenant / ancestor / parent / record; omit absent levels. Apply only cases supported by inspected code. Keep independent failures separate. Reactivation removes a deactivation subresource, not the entity; its fixtures are inactive except the already-active case.

## Case Order

1. `requires authentication`
2. `prevents deleting from an unrelated tenant` or `prevents reactivating from an unrelated tenant`
3. `returns not found when the ancestor belongs to another tenant`
4. `returns not found when the ancestor is soft deleted`
5. `returns not found when the parent belongs to another ancestor in the same tenant`
6. `returns not found when the parent belongs to another tenant`
7. `returns not found when the parent is soft deleted`
8. `returns not found when the record belongs to another parent in the same tenant`
9. `returns not found when the record belongs to another parent under the same ancestor`
10. `returns not found when the record belongs to another ancestor in the same tenant`
11. `returns not found when the record belongs to another tenant`
12. `returns not found when the record is soft deleted`
13. `returns not found when the record tenant does not match its parent tenant`
14. `prevents deleting when the record is inactive`
15. `prevents deleting when the parent is inactive`
16. `prevents deleting when the ancestor is inactive`
17. `prevents reactivating when the record is active`
18. `maps an existing history rejection to validation`
19. `maps a required active initial record rejection to validation`
20. `maps an assigned record rejection to validation`
21. `maps a child dependency rejection to validation`
22. `maps a soft deleted child dependency rejection to validation`
23. `maps a related dependency rejection to validation`
24. `maps a soft deleted related dependency rejection to validation`
25. `maps a final record rejection to validation`
26. `maps a final parent rejection to validation`
27. `maps an inactive parent rejection to validation`
28. `maps an inactive ancestor rejection to validation`
29. `maps an inactive relation rejection to validation`
30. `deletes the record`
31. `deletes the default record`
32. `reactivates the record`

## Contracts

Authenticate the URL tenant for 404 cases. A target with a correct parent foreign key but conflicting tenant ID tests an independent policy boundary. A trashed binding fixture does not imply destroy uses soft deletion.

Policy 403 cases use real restricted fixtures. Action-error cases use ordinary authorized fixtures and mock the exception, asserting exact redirect-back field/message errors. Preserve live and trashed dependency variants separately.

Use deletes the record for collection or parent-detail redirects, and reactivates the record for exact-collection or redirect-only contracts. Normal success precedes deletes the default record. Keep the asserted destination strength and all surviving route parameters.

Success examples mock the action; they prove delegation, response and toast, not persistence. Do not infer cleanup, replacement defaults or persisted state changes.

Reuse these names across controllers; qualify only to distinguish otherwise identical cases within one block. Here login() authenticates an outsider and login(team: ...) authorizes that team.
