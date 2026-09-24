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
24. `rejects a case-insensitive duplicate value in the same scope`
25. `rejects a value reserved by an inactive record`
26. `rejects a case-insensitive value reserved by an inactive record`
27. `rejects a newly assigned relation from another tenant`
28. `rejects a newly assigned inactive relation`
29. `rejects a newly assigned soft deleted relation`
30. `rejects a historical relation selected by another record`
31. `validates an upper bound against the stored lower bound`
32. `validates a lower bound against the stored upper bound`
33. `maps a rejected request approval conflict to validation`
34. `maps an approved request rejection conflict to validation`
35. `maps a cleared prerequisite rejection to validation when the dependent field is omitted`
36. `maps an incomplete weight rejection to validation`
37. `maps a mismatched weight unit rejection to validation`
38. `maps incomplete dimensions to validation`
39. `maps a final record rejection to validation`
40. `maps a final parent rejection to validation`
41. `maps a required received date rejection to validation`
42. `maps a relation ownership mismatch to validation`
43. `maps a missing prerequisite relation rejection to validation`
44. `maps a dependent relation mismatch to validation`
45. `maps an unavailable relation rejection to validation`
46. `maps a duplicate reference rejection to validation`
47. `maps a dependent rate rejection to validation`
48. `maps an inactive parent rejection to validation`
49. `maps an inactive ancestor rejection to validation`
50. `maps a second open-ended range rejection to validation`
51. `maps an overlapping range rejection to validation`
52. `allows an email used in a different scope`
53. `allows a phone number used in a different scope`
54. `allows a value used in a different scope`
55. `allows the current email`
56. `allows the current phone number`
57. `allows the current value`
58. `allows an email used by a soft deleted record`
59. `allows a phone number used by a soft deleted record`
60. `allows a value used by a soft deleted record`
61. `updates the record`
62. `updates the settings`
63. `maps submitted and omitted fields to the action`
64. `maps a partial update with null and the current name`
65. `maps a name-only update to the action`
66. `allows disabling an enabled setting`
67. `maps a base status to the update input`
68. `maps a false boolean to the update input`
69. `maps the province using the current country when country is empty`
70. `clears the province when changing country without a province`
71. `clears the rounding increment when rounding is disabled`
72. `retains the stored increment while updating another field`
73. `maps a cleared upper bound with the stored lower bound`
74. `maps partial measurements using stored values`
75. `allows a lower bound update with an open-ended stored upper bound`
76. `clears a relation and its dependent relation when both are empty`
77. `accepts current inactive and deleted relations`
78. `accepts a current deleted relation`
79. `accepts a current deleted dependent relation`
80. `accepts a current inactive relation`
81. `approves a request`
82. `rejects a request`

## Contracts

These examples cover PATCH, not PUT replacement semantics. Route roles are tenant / ancestor / parent / record. Keep valid route graphs for guests and outsiders; authenticate the URL tenant for each 404 case. Wrong-parent binding, conflicting direct tenant ownership and inactive-fixture 403 are independent contracts.

Keep complete named `validates fields` datasets, valid base payloads, stored attributes, expected messages and action non-call checks. Omitted fields, explicit null, empty strings, stored defaults and Optional are distinct. Preserve conditional field requirements, stored-bound comparisons and exact error-bag precedence.

New relation eligibility differs from retaining the current historical selection; another record's historical relation is separate. Scoped uniqueness includes current-value acceptance, other scopes, deleted reuse and inactive reservation. Keep case-insensitive and relation-state qualifiers. Add `: field_name` only when separate tests would otherwise collide; dataset rows already distinguish their fields.

Preserve typed action argument identity, decoded public IDs, enums, decimals, false booleans, geographic normalization and complete transformed measurement datasets. A mocked finality, dependency or decision exception verifies controller translation; do not replace its ordinary fixture with a real failing-state fixture.

Approval and rejection select different actions and pass the actual reviewer; retain the opposite-action non-call assertion when present. Generic redirects, exact named redirects, redirect back, toast payloads and field errors prove different contracts. Mocked action returns do not establish persistence.

Examples are synthetic. Adapt models, namespaces, helpers, public IDs, seeded geography and suite paths to the consuming project. `login()` creates an outsider; `login(team: ...)` supplies membership.
