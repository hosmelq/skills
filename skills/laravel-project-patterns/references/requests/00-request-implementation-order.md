# Request Implementation: Reference Order

Implement FormRequests with the matching field rules and hooks. Choose the smallest applicable example; a simple request does not need the complete catalog.

Examples use Laravel 13. Preserve rule order, nullable/absent/false/zero distinctions and the shown preparation guards. Keep independent fields and imports alphabetical, public methods before protected/private methods, and blank lines between preparation, guards and effects. Simple calls up to 100 characters stay on one line.

These examples rely on authorization outside the Request; inspect route middleware, scoped bindings and policies. `RouteParameter` injects bound models. `exists`/`unique` rules do not authorize access, decode Sqids or automatically apply Eloquent scopes. Where input uses Sqids, inspect its decoding middleware before adapting numeric-ID rules. Pass validated data to the inspected controller/action input; preparation merges can change that payload.

## Reference Order

1. [Empty Body](01-empty-body.md)
2. [Scoped Unique Name](02-scoped-unique.md)
3. [Create Optional Settings](03-create-settings.md)
4. [Update Optional Settings](04-update-settings.md)
5. [Create Contact Fields](05-create-contact.md)
6. [Update Contact Fields](06-update-contact.md)
7. [Create an Address](07-create-address.md)
8. [Update an Address](08-update-address.md)
9. [Create Dependent Fields](09-create-dependent-fields.md)
10. [Update Dependent Fields](10-update-dependent-fields.md)
11. [Create and Update an Assignment](11-assignment-fields.md)
12. [Create an Ordered State](12-create-state.md)
13. [Update an Initial State](13-update-state.md)
14. [Validate a Predecessor](14-move-record.md)
15. [Restrict an Enum Transition](15-enum-transition.md)
16. [Create Integer Bounds](16-create-bounds.md)
17. [Update Effective Integer Bounds](17-update-bounds.md)
18. [Create a Conditional Decimal](18-create-conditional-value.md)
19. [Update an Effective Conditional Decimal](19-update-conditional-value.md)
20. [Create a Decimal Range](20-create-range.md)
21. [Update Effective Decimal Bounds](21-update-range.md)
22. [Create Scoped Related Selections](22-create-relations.md)
23. [Update Historical Selections](23-update-relations.md)
24. [Create Paired and Grouped Values](24-create-paired-values.md)
25. [Update Paired and Grouped Values](25-update-paired-values.md)
26. [Validate Provider Token Input](26-provider-token.md)
27. [Validate Email and One-Time Code](27-email-code.md)

[Laravel validation](https://laravel.com/docs/13.x/validation) defines presence rules and hooks. [Request input](https://laravel.com/docs/13.x/requests#merging-additional-input) defines merge behavior. Phone examples require [Laravel Phone](https://github.com/Propaganistas/Laravel-Phone); `indisposable` requires its registered validation package.
