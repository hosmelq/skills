# Controller Tests: Ordered Edit Block

Ordered GET edit tests for access and binding, page props, dependent options, retained historical selections, related-record flags, and final-state forms.

## Case Order

Apply only inspected contracts; omit absent route levels. Keep independent failures separate and do not alphabetize the block.

1. `requires authentication`
2. `prevents viewing from an unrelated tenant`
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
14. `prevents viewing when the record is inactive`
15. `prevents viewing when the parent is inactive`
16. `prevents viewing when the ancestor is inactive`
17. `shows the edit page`
18. `loads dependent options for the selected value`
19. `shows the edit page with the current soft deleted relation`
20. `shows the edit page with the current inactive relation`
21. `shows the edit page with the current inactive option`
22. `shows the edit page with its selected historical relations`
23. `shows the edit page and marks existing rates`
24. `exposes the record final state`
25. `marks the page read only for final parent states`

## Contracts

Route roles are tenant / ancestor / parent / record. Keep all valid route parameters even for guest and outsider requests. Authenticate the URL tenant for 404 cases. A correct parent foreign key with a conflicting target tenant ID is independent of wrong-parent binding.

Use shows the edit page for the applicable positive contract: IDs, enums, stored dependent choices, singular selected resources or false related-record flags. Specialized historical/selected-option examples already contain their stated page assertions; do not add a duplicate plain page test solely to match this list.

Selected historical resources and available choices are separate contracts. Preserve exact timestamps, enum options, missing props, partial reloads and dataset variants that the endpoint supports. A first-option assertion is not a complete-list assertion.

Inactive-policy fixtures return 403. Final-state examples remain viewable: the record case asserts is_final; the child dataset asserts canMutate=false. Do not substitute one for the other or infer write-action validation.

Reuse canonical names across controllers; qualify only to distinguish cases within one block. Here login() authenticates an outsider and login(team: ...) authorizes that team.
