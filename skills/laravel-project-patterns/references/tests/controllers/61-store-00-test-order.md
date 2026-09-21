# Controller Tests: Ordered Store Block

Ordered Pest POST store cases for record creation, API requests and deactivation subresources: access, scoped bindings, inactive policy, complete field datasets, related-record eligibility, scoped uniqueness, action-error translation and accepted input mapping. Canonical names and boundaries for the applicable operation.

## Case Order

1. `requires authentication`
2. `prevents storing for an unverified user`
3. `prevents storing from an unrelated tenant`
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
14. `prevents storing when the record is inactive`
15. `prevents storing when the parent is inactive`
16. `prevents storing when the ancestor is inactive`
17. `validates fields`
18. `requires a name`
19. `requires an email, name, or phone number`
20. `rejects a service plan from another tenant`
21. `rejects a member from another tenant`
22. `rejects a soft deleted member`
23. `rejects a cabinet from another tenant`
24. `rejects an inactive cabinet`
25. `rejects a soft deleted cabinet`
26. `rejects an inactive service plan`
27. `rejects a soft deleted service plan`
28. `rejects a soft deleted plan rule`
29. `rejects a plan rule from another tenant`
30. `rejects a plan rule for an inactive service plan`
31. `rejects a plan rule for a soft deleted service plan`
32. `rejects facilities from another tenant`
33. `rejects inactive facilities`
34. `rejects soft deleted facilities`
35. `rejects an inactive received status`
36. `rejects a soft deleted received status`
37. `rejects a status that is not a received status`
38. `rejects a received status from another tenant`
39. `rejects an invalid item group id`
40. `rejects an item group from another tenant`
41. `rejects an inactive item group`
42. `rejects a soft deleted item group`
43. `rejects duplicate emails within the tenant`
44. `rejects duplicate phone numbers within the tenant`
45. `rejects case-insensitive duplicate names including inactive records`
46. `rejects case-insensitive duplicate names within the tenant`
47. `rejects duplicate names within the tenant`
48. `keeps inactive record names reserved`
49. `rejects duplicate country codes within the parent`
50. `allows the same email in another tenant`
51. `allows the same phone number in another tenant`
52. `allows the same name in another tenant`
53. `allows the same country code in another parent within the same tenant`
54. `allows reusing an email after the existing record is soft deleted`
55. `allows reusing a phone number after the existing record is soft deleted`
56. `allows reusing a name after the existing record is soft deleted`
57. `allows reusing a country code after the existing record is soft deleted`
58. `rejects storing when self-service is disabled`
59. `rejects storing with an inactive selected service plan`
60. `rejects storing when the selected service plan is already assigned`
61. `rejects storing without an active initial status`
62. `rejects storing when the selected member does not own the selected cabinet`
63. `rejects storing with a plan rule but no service plan`
64. `rejects storing with a plan rule for another service plan`
65. `rejects storing with a duplicate reference`
66. `rejects storing when the selected member becomes unavailable`
67. `rejects storing when the received facility becomes unavailable`
68. `rejects storing when the current facility becomes unavailable`
69. `rejects storing when the pickup facility becomes unavailable`
70. `rejects storing when the selected cabinet becomes unavailable`
71. `rejects storing when the selected status becomes unavailable`
72. `rejects storing when the selected service plan becomes unavailable`
73. `rejects storing with a measurement unit that differs from the service plan`
74. `rejects storing when the parent becomes final`
75. `rejects storing when the selected item group becomes unavailable`
76. `rejects deactivating the required active initial status`
77. `rejects deactivating a record with active dependent records`
78. `rejects storing when the parent becomes inactive`
79. `rejects storing when the ancestor becomes inactive`
80. `rejects overlapping ranges`
81. `rejects a second open-ended range`
82. `stores the record`
83. `clears the rounding increment when rounding is disabled`
84. `maps a boolean field into the input`
85. `stores the record with omitted optional fields`
86. `accepts explicit nulls for nullable fields`
87. `stores the record with an explicit null upper bound`
88. `deactivates the record`

## Contracts

Select the actual operation and transport first. Browser guests redirect to login; JSON guests get 401 and unverified actors may get 403. A store method can deactivate an existing record. Keep its bound target and success route; do not assume creation. Route roles are tenant / ancestor / parent / record. Authenticate the URL tenant for scoped 404 cases and retain valid route parameters for guests and outsiders.

Apply only inspected behavior. Keep one `validates fields` test with the complete applicable named dataset, exact expected fields/messages and valid base payload. Preserve required-with directions, enum and decimal constraints, invalid public IDs and named general error bags. Different datasets are alternative contracts, not a reason to add fields the endpoint does not accept.

New-relation eligibility, route binding and action exceptions are distinct. Retain wrong tenant, inactive, deleted, wrong subtype and contradictory ownership fixtures independently. Scoped uniqueness includes current reserved values, other scopes and deleted reuse; qualify names only when a block needs different fields distinguished.

Preserve typed action arguments and exact model identity, decoded IDs, enums, decimal strings, booleans, phone normalization and actor selection. Omitted Optional, explicit null and cleared dependent values differ. Mocked exceptions prove translation only; an initially active fixture reaching an action is not an inactive-policy fixture. Preserve negative action expectations only where asserted.

Specific redirects, redirect-back errors, generic redirects, toast payloads and JSON API media type/IDs/status have different assertion strength. A pre-created action return does not prove database persistence. Synthetic `signIn()` is an outsider; `signIn(team: ...)` supplies membership. Adapt models, namespaces, helpers, public IDs, seeded data and suite paths to the consuming project.
