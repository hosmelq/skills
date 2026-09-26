# Middleware: Implementation Order

Choose the needed implementation:

1. [Request identifiers](01-request-identifiers.md): configured Sqid fields; preserve missing/null input and normalize invalid input.
2. [Admin access](02-admin-access.md): guest/non-admin rejection and admin pass-through.
3. [Shared authentication](03-shared-authentication.md): parent props plus nullable team/user resources.

In the existing `bootstrap/app.php` middleware configuration, alias `admin` to `EnsureAdmin` and `sqids` to `DecodeSqids`; append `HandleInertiaRequests` to the `web` group. Aliases run only where assigned. This catalog order does not set pipeline execution order.

Keep constructors before `handle()`/`share()` and private helpers after public methods. Use the separate [middleware test checklist](../tests/middleware/00-middleware-test-order.md) for test cases.

APIs: [Laravel middleware](https://laravel.com/docs/13.x/middleware), [request merging](https://laravel.com/docs/13.x/requests#merging-additional-input), [Inertia shared data](https://inertiajs.com/docs/v3/data-props/shared-data).
