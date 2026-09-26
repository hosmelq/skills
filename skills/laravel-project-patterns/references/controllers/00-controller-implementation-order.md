# Controller Implementation: Reference Order

Choose the relevant method examples, retaining the inspected route and controller middleware. Examples with selected methods are not replacement controllers. Preserve route names, bindings, response fields and action contracts when adapting synthetic types.

Use the permission and Sqid middleware pattern for the matching bound context. Typed parameters alone do not scope records. Web routes need their authentication, verification and scoped-binding configuration; API routes keep their authentication or named throttles. `toast()` and the string translation helper are project extensions. Inertia property-provider examples target Inertia Laravel 3; Requests and typed inputs have separate guides.

Order independent imports and prop keys alphabetically; retain query, middleware, guard, catch and mutation order. Place static middleware first, then public methods alphabetically, private helpers and finally __invoke and separate logical steps with blank lines. Simple calls up to 100 characters stay on one line. Closures defer prop evaluation; no deferred-response contract is implied.

## Reference Order

1. [Authorize Bound Controller Actions](01-controller-middleware.md)
2. [Paginate Tenant and Parent Lists](02-paginated-lists.md)
3. [Return Ordered Collections](03-ordered-lists.md)
4. [List Pending Reviews](04-pending-list.md)
5. [Keep Historical Relations in a List](05-historical-list.md)
6. [Render Basic Record Pages](06-basic-pages.md)
7. [Render Enum Options and Edit Guards](07-enum-pages.md)
8. [Render Parent and Ancestor Context](08-nested-pages.md)
9. [Resolve Dependent Options with Closures](09-dependent-closures.md)
10. [Resolve a Dependent Property](10-dependent-property.md)
11. [Return Label and Public-ID Options](11-option-pairs.md)
12. [Return Resources as Options](12-option-resources.md)
13. [Expose a Historical Selected Parent](13-selected-history.md)
14. [Group Form Properties](14-form-properties.md)
15. [Load Historical Relations for Editing](15-edit-history.md)
16. [Show Scoped Children and Historical Relations](16-show-children.md)
17. [Expose Nested Form Options and Mutation State](17-nested-form-state.md)
18. [Delegate Validated Create and Update Input](18-store-update.md)
19. [Write a Nested Record and Return to Its List](19-nested-writes.md)
20. [Create and Update Owner Settings](20-owner-settings.md)
21. [Map Assignment Creation Errors](21-assignment-writes.md)
22. [Map Parent and Child Write Guards](22-parent-write-guards.md)
23. [Map Dynamic Range Errors](23-range-errors.md)
24. [Map Related Creation Errors](24-create-related-errors.md)
25. [Map Related Update Errors](25-update-related-errors.md)
26. [Map Nested Write Errors](26-nested-write-errors.md)
27. [Delegate a Delete and Redirect](27-simple-delete.md)
28. [Translate Delete Guards](28-delete-guards.md)
29. [Delete Under an Active Ancestor](29-delete-under-parent.md)
30. [Reject Deletion in a Final State](30-delete-final-state.md)
31. [Deactivate and Reactivate](31-simple-lifecycle.md)
32. [Map Deactivation Failures](32-deactivation-guards.md)
33. [Map an Unavailable Parent on Reactivation](33-reactivation-guard.md)
34. [Resolve an Optional Predecessor](34-move-record.md)
35. [Select a Default or Initial Record](35-select-default.md)
36. [Dispatch a Validated Decision](36-review-transition.md)
37. [Return a Bound or Authenticated Resource](37-api-resource.md)
38. [Return an API Action Result](38-api-action.md)
39. [Generate and Send a One-Time Code](39-request-code.md)
40. [Redeem a One-Time Code](40-redeem-code.md)
41. [Authenticate with a Provider Client](41-provider-client.md)
42. [Authenticate with a Cached Key Set](42-provider-key-set.md)

Use the separate controller test checklists for Pest cases. [Laravel controller middleware](https://laravel.com/docs/13.x/controllers#controller-middleware) and [Inertia 3 properties](https://inertiajs.com/docs/v3/the-basics/responses) document the framework APIs. Provider authentication examples retain their stated trust and concurrency limits.
