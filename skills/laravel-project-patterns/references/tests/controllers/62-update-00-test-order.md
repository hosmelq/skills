# Controller Tests: Ordered Update Block

Ordered Pest PATCH update cases: access and scoped bindings, inactive policy, complete field datasets, scoped and stored-value validation, action exceptions, accepted partial inputs, historical selections and approval decisions. Canonical names for applying only the inspected endpoint contracts.

## Case Order

1. `requires authentication`
2. `prevents updating from an unrelated tenant`
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
14. `prevents updating when the record is inactive`
15. `prevents updating when the parent is inactive`
16. `prevents updating when the ancestor is inactive`
17. `validates fields`
18. `requires an email, name, or phone number`
19. `returns only the enum error for an invalid initial base status`
20. `prevents changing the initial base status`
21. `rejects a duplicate email in the same scope`
22. `rejects a duplicate phone number in the same scope`
23. `rejects a duplicate value in the same scope`
24. `rejects a value reserved by an inactive record`
25. `rejects a newly assigned relation from another tenant`
26. `rejects a newly assigned inactive relation`
27. `rejects a newly assigned soft deleted relation`
28. `rejects a historical relation selected by another record`
29. `rejects a related rule from another tenant`
30. `validates an upper bound against the stored lower bound`
31. `validates a lower bound against the stored upper bound`
32. `maps a rejected request approval conflict to validation`
33. `maps an approved request rejection conflict to validation`
34. `rejects clearing a service plan while leaving its rule omitted`
35. `maps an incomplete weight rejection to validation`
36. `maps a mismatched weight unit rejection to validation`
37. `maps incomplete dimensions to validation`
38. `maps a final record rejection to validation`
39. `maps a required received date rejection to validation`
40. `maps a member cabinet mismatch to validation`
41. `maps a missing service plan rejection to validation`
42. `maps a plan rule mismatch to validation`
43. `maps an unavailable service plan rejection to validation`
44. `maps an unavailable member rejection to validation`
45. `maps an unavailable received facility rejection to validation`
46. `maps an unavailable pickup facility rejection to validation`
47. `maps an unavailable cabinet rejection to validation`
48. `maps a duplicate reference rejection to validation`
49. `maps an unavailable relation rejection to validation`
50. `maps a dependent rate rejection to validation`
51. `maps an inactive service plan rejection to validation`
52. `maps a second open-ended range rejection to validation`
53. `maps an overlapping range rejection to validation`
54. `allows an email used in a different scope`
55. `allows a phone number used in a different scope`
56. `allows a value used in a different scope`
57. `allows the current email`
58. `allows the current phone number`
59. `allows the current value`
60. `allows an email used by a soft deleted record`
61. `allows a phone number used by a soft deleted record`
62. `allows a value used by a soft deleted record`
63. `updates the record`
64. `updates the settings`
65. `maps submitted and omitted fields to the action`
66. `maps a partial update with null and the current name`
67. `maps a name-only update to the action`
68. `allows disabling the cabinet setting`
69. `allows disabling the shipment setting`
70. `maps a base status to the update input`
71. `maps a false boolean to the update input`
72. `maps the province using the current country when country is empty`
73. `clears the province when changing country without a province`
74. `clears the rounding increment when rounding is disabled`
75. `retains the stored increment while updating another field`
76. `maps a cleared upper bound with the stored lower bound`
77. `maps partial measurements using stored values`
78. `allows a lower bound update with an open-ended stored upper bound`
79. `clears a service plan and its rule when both are empty`
80. `accepts the current historical cabinet, facilities and service plan`
81. `accepts the current historical member`
82. `accepts the current historical rule`
83. `accepts the explicitly unchanged inactive item group`
84. `approves a request`
85. `rejects a request`

## Contracts

These examples cover PATCH, not PUT replacement semantics. Route roles are tenant / ancestor / parent / record. Keep valid route graphs for guests and outsiders; authenticate the URL tenant for each 404 case. Wrong-parent binding, conflicting direct tenant ownership and inactive-fixture 403 are independent contracts.

Keep complete named `validates fields` datasets, valid base payloads, stored attributes, expected messages and action non-call checks. Omitted fields, explicit null, empty strings, stored defaults and Optional are distinct. Preserve conditional field requirements, stored-bound comparisons and exact error-bag precedence.

New relation eligibility differs from retaining the current historical selection; another record's historical relation is separate. Scoped uniqueness includes current-value acceptance, other scopes, deleted reuse and inactive reservation. Qualify names by field only when the same block contains otherwise colliding cases.

Preserve typed action argument identity, decoded public IDs, enums, decimals, false booleans, geographic normalization and complete transformed measurement datasets. A mocked finality, dependency or decision exception verifies controller translation; do not replace its ordinary fixture with a real failing-state fixture.

Approval and rejection select different actions and pass the actual reviewer; retain the opposite-action non-call assertion when present. Generic redirects, exact named redirects, redirect back, toast payloads and field errors prove different contracts. Mocked action returns do not establish persistence.

Examples are synthetic. Adapt models, namespaces, helpers, public IDs, seeded geography and suite paths to the consuming project. `signIn()` creates an outsider; `signIn(team: ...)` supplies membership.
