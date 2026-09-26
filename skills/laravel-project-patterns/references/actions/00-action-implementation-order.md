# Action Implementation: Reference Order

Implement actions and typed inputs with the matching transaction, return and side-effect contracts. Choose the relevant examples below; a CRUD action does not require the entire catalog.

Use typed `handle()` methods and constructor-injected collaborators. Fortify callbacks follow their interfaces. Data examples target Spatie Laravel Data 4; sortable examples use Spatie Eloquent Sortable 5; index-aware exceptions use Laravel 13. Synthetic types, fields and constraint names must match the inspected project when adapted.

Keep imports and independent declarations alphabetical; preserve meaningful operation order. Separate guards, mutation and side effects with blank lines. Simple calls up to 100 characters stay on one line. Preserve omitted/null/false/zero, soft-deleted history, transaction boundaries and same-instance versus refreshed returns. Inputs define types, not authorization; use the inspected validation and policy contract.

## Reference Order

1. [Required, Nullable and Omitted Fields](01-input-fields.md)
2. [Optional Settings](02-input-settings.md)
3. [Decimal Ranges](03-input-ranges.md)
4. [Relation IDs and Measurements](04-input-relations.md)
5. [Create and Update Through Data](05-create-update.md)
6. [Create and Select a Tenant](06-create-owner.md)
7. [Dispatch After Creation](07-create-dispatch.md)
8. [Dispatch on a Setting Transition](08-update-dispatch.md)
9. [Lock and Resolve an Active Parent](09-active-parent.md)
10. [Create Under an Active Parent](10-create-child.md)
11. [Create a Scoped Assignment](11-create-assignment.md)
12. [Create a Non-Overlapping Range](12-create-range.md)
13. [Update a Locked Parent](13-update-parent.md)
14. [Guard a Changed Child Field](14-update-child.md)
15. [Update Effective Range Bounds](15-update-range.md)
16. [Move Between Ordered Groups](16-update-group-order.md)
17. [Simple Record Lifecycle](17-simple-lifecycle.md)
18. [Guard Historical References](18-delete-history.md)
19. [Delete Related Records Transactionally](19-delete-cascade.md)
20. [Delete a Locked Referenced Parent](20-delete-parent.md)
21. [Delete Under an Active Ancestor](21-delete-child.md)
22. [Initial-State Lifecycle](22-state-lifecycle.md)
23. [Deactivate a Locked Parent](23-deactivate-parent.md)
24. [Reactivate Under an Active Parent](24-reactivate-child.md)
25. [Select an Owner Default](25-select-default.md)
26. [Select an Initial State](26-select-initial.md)
27. [Ensure an Initial State](27-ensure-initial.md)
28. [Move After a Record or to the Start](28-move-order.md)
29. [Generate a Scoped Code](29-generate-code.md)
30. [Generate a One-Time Code](30-generate-one-time-code.md)
31. [Resolve a Normalized Active Code](31-resolve-code.md)
32. [Request or Resubmit Enrollment](32-request-enrollment.md)
33. [Approve or Reject Enrollment](33-review-enrollment.md)
34. [Provision Eligible Missing Records](34-provision-missing.md)
35. [Fortify User Creation](35-fortify-create.md)
36. [Fortify Password Changes](36-fortify-password.md)
37. [Fortify Profile Updates](37-fortify-profile.md)
38. [Create with Related Selections](38-create-related-record.md)
39. [Update with Historical Selections](39-update-related-record.md)
40. [Create a Nested Record](40-create-nested-record.md)
41. [Update a Nested Relation](41-update-nested-record.md)
42. [Delete Under a Mutable State](42-delete-final-state.md)

For tests, use the separate [action test checklist](../tests/actions/00-action-test-order.md).
