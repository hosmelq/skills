# Middleware Tests: Ordered Contract Checklist

Feature tests register a temporary route with the inspected middleware and make one request per test. Preserve the alias parameters, authentication state and exact response assertions; keep the middleware enabled.

1. [decodes configured sqid request fields](01-request-identifiers.md)
2. [normalizes a differently cased sqid to zero](01-request-identifiers.md)
3. [normalizes invalid identifiers and preserves null or absent fields](01-request-identifiers.md)
4. [forbids guests](02-admin-access.md)
5. [forbids non-admin users](02-admin-access.md)
6. [allows admins to proceed](02-admin-access.md)
7. [shares null authentication data for guests](03-shared-authentication.md)
8. [shares authenticated tenant and user IDs](03-shared-authentication.md)

Keep standalone `it()` declarations and each example’s case order. Separate setup, request and assertions with blank lines. Adapt the local `login()` helper and Inertia page to the inspected suite.
