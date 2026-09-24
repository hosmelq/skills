# Controller Tests: Ordered Show Block

Ordered GET show tests for browser pages and JSON resources: access, bindings, page contracts, historical relations, child collections, and mutation flags.

## Case Order

1. `requires authentication`
2. `prevents viewing for an unverified user`
3. `prevents viewing from an unrelated tenant`
4. `returns not found for an invalid public identifier`
5. `returns not found when the ancestor belongs to another tenant`
6. `returns not found when the ancestor is soft deleted`
7. `returns not found when the parent belongs to another ancestor in the same tenant`
8. `returns not found when the parent belongs to another tenant`
9. `returns not found when the parent is soft deleted`
10. `returns not found when the record belongs to another parent in the same tenant`
11. `returns not found when the record belongs to another parent under the same ancestor`
12. `returns not found when the record belongs to another ancestor in the same tenant`
13. `returns not found when the record belongs to another tenant`
14. `returns not found when the record is soft deleted`
15. `returns not found when the record tenant does not match its parent tenant`
16. `shows the detail page`
17. `shows the detail page with the default relation`
18. `shows the detail page with the selected relation`
19. `shows the detail page with the current soft deleted relation`
20. `shows the detail page with the current inactive relation`
21. `shows the settings page`
22. `shows public fields without raw foreign keys`
23. `shows the detail page with its selected historical relations`
24. `lists live child records oldest first`
25. `shows the detail page for an inactive record`
26. `shows the detail page under an inactive parent`
27. `shows the detail page under an inactive ancestor`
28. `exposes mutation availability for a nonfinal parent`
29. `marks the page read only for final parent states`
30. `marks the page read only for a historical final parent state`
31. `returns the authenticated user`
32. `returns the resource`

## Contracts

Apply only the inspected endpoint contracts. Route roles are tenant / ancestor / parent / record. Preserve valid guest and outsider route chains; authorize the URL tenant for scoped 404 cases. A child with a conflicting direct tenant is distinct from a wrong-parent fixture.

Browser authentication redirects; JSON authentication returns 401. Verification and tenant membership are separate restrictions. Flat JSON uses `id`; JSON API uses `data.id`, `data.type` and its media type. Do not impose membership on a public resource endpoint.

Use `shows the detail page` across direct and nested controllers. Specialized examples include their stated page assertions; retrieve an additional plain page example only for an uncovered contract. Preserve exact IDs, counts, enum options, timestamps, nulls, absent raw keys and dataset rows. Qualify canonical names only to distinguish cases within one block.

Historical selections remain attached to a live record; they are not available-choice filters. Ordered live children require exact count and both positions. Finality examples expose read-only flags while keeping GET successful; they do not prove action guards. Keep the historical flag example separate from the broader final-state dataset assertions.

Here `login()` creates an outsider and `login(team: ...)` supplies membership.
