# Controller Tests: Ordered Store Block

Ordered Pest POST store cases for record creation, API requests and deactivation subresources: access, scoped bindings, inactive policy, complete field datasets, related-record eligibility, scoped uniqueness, action-error translation and accepted input mapping. Canonical names and boundaries for the applicable operation.

## Case Order

1. `requires authentication`
2. `prevents storing for an unverified user`
3. `prevents storing from an unrelated tenant` or `prevents deactivating from an unrelated tenant`
4. `returns not found when the tenant public identifier is invalid`
5. `returns not found when the ancestor belongs to another tenant`
6. `returns not found when the ancestor is soft deleted`
7. `returns not found when the parent belongs to another ancestor in the same tenant`
8. `returns not found when the parent belongs to another tenant`
9. `returns not found when the parent is soft deleted`
10. `returns not found when the record belongs to another parent in the same tenant`
11. `returns not found when the record belongs to another tenant`
12. `returns not found when the record is soft deleted`
13. `returns not found when the record tenant does not match its parent tenant`
14. `prevents deactivating when the record is inactive`
15. `prevents storing when the parent is inactive`
16. `prevents storing when the ancestor is inactive`
17. `validates fields`
18. `requires a name`
19. `requires an email, name, or phone number`
20. `rejects an invalid relation id`
21. `rejects a newly assigned relation from another tenant`
22. `rejects a newly assigned inactive relation`
23. `rejects a newly assigned soft deleted relation`
24. `rejects a newly assigned relation with an inactive parent`
25. `rejects a newly assigned relation with a soft deleted parent`
26. `rejects a noninitial status`
27. `rejects a duplicate email in the same scope`
28. `rejects a duplicate phone number in the same scope`
29. `rejects a duplicate value in the same scope`
30. `rejects a case-insensitive duplicate value in the same scope`
31. `rejects a value reserved by an inactive record`
32. `rejects a case-insensitive value reserved by an inactive record`
33. `allows an email used in a different scope`
34. `allows a phone number used in a different scope`
35. `allows a value used in a different scope`
36. `allows an email used by a soft deleted record`
37. `allows a phone number used by a soft deleted record`
38. `allows a value used by a soft deleted record`
39. `maps a disabled self-service rejection to validation`
40. `maps an inactive relation rejection to validation`
41. `maps an already assigned relation rejection to validation`
42. `maps an unavailable initial status rejection to validation`
43. `maps a relation ownership mismatch to validation`
44. `maps a missing prerequisite relation rejection to validation`
45. `maps a dependent relation mismatch to validation`
46. `maps a duplicate reference rejection to validation`
47. `maps an unavailable relation rejection to validation`
48. `maps a mismatched weight unit rejection to validation`
49. `maps a final parent rejection to validation`
50. `maps a required active initial record rejection to validation`
51. `maps an active dependent record rejection to validation`
52. `maps an inactive parent rejection to validation`
53. `maps an inactive ancestor rejection to validation`
54. `maps an overlapping range rejection to validation`
55. `maps a second open-ended range rejection to validation`
56. `stores the record`
57. `clears the rounding increment when rounding is disabled`
58. `maps a boolean field into the input`
59. `stores the record with omitted optional fields`
60. `accepts explicit nulls for nullable fields`
61. `stores the record with an explicit null upper bound`
62. `deactivates the record`

## Contracts

Select the actual operation and transport first. Browser guests redirect to login; JSON guests get 401 and unverified actors may get 403. A store method can deactivate an existing record. Keep its bound target and success route; do not assume creation. Route roles are tenant / ancestor / parent / record. Authenticate the URL tenant for scoped 404 cases and retain valid route parameters for guests and outsiders.

Apply only inspected behavior. Keep one `validates fields` test with the complete applicable named dataset, exact expected fields/messages and valid base payload. Preserve required-with directions, enum and decimal constraints, invalid public IDs and named general error bags. Different datasets are alternative contracts, not a reason to add fields the endpoint does not accept.

New-relation eligibility, route binding and action exceptions are distinct. Retain wrong tenant, inactive, deleted, wrong subtype and contradictory ownership fixtures independently. Scoped uniqueness includes current reserved values, other scopes and deleted reuse; retain case-insensitive qualifiers only when the fixture varies letter case. Add `: field_name` when several fields need separate tests; named dataset rows already distinguish their fields.

Preserve typed action arguments and exact model identity, decoded IDs, enums, decimal strings, booleans, phone normalization and actor selection. Omitted Optional, explicit null and cleared dependent values differ. Mocked exceptions prove translation only; an initially active fixture reaching an action is not an inactive-policy fixture. Preserve negative action expectations only where asserted.

Specific redirects, redirect-back errors, generic redirects, toast payloads and JSON API media type/IDs/status have different assertion strength. A pre-created action return does not prove database persistence. Synthetic `signIn()` is an outsider; `signIn(team: ...)` supplies membership. Adapt models, namespaces, helpers, public IDs, seeded data and suite paths to the consuming project.
