# Policy Implementation: Reference Order

Choose by ability signatures and predicates, then keep public methods alphabetical. A class argument selects the policy; additional context follows the authenticated User in the declared order. Record abilities receive the bound record. Match the controller’s `can` middleware arguments.

`belongsToTeam()` means owner or member of the supplied team, not ownership of the child; null returns false. Preserve `bool|Response`: `false` denies with 403, while `Response::denyAsNotFound()` preserves 404 through Gate authorization. Nonnullable User arguments deny guests. Resolve policies through the project’s discovery or registration configuration.

Keep membership, related-record consistency and lifecycle predicates distinct. A policy does not establish scoped route binding or replace action guards. These examples have no admin bypass or `before` hook.

## Reference Order

1. [Root Creation](01-root-creation.md)
2. [Team Membership](02-team-membership.md)
3. [Parent Membership](03-parent-membership.md)
4. [Active Record Writes](04-active-record.md)
5. [Lifecycle Transitions](05-lifecycle-transitions.md)
6. [Explicit Deactivation Timestamp](06-explicit-state.md)
7. [Active Parent Writes](07-active-parent.md)
8. [Ordered Ancestor Context](08-ancestor-context.md)
9. [Related Ownership and State](09-related-state.md)
10. [Related Ownership on Update](10-related-update.md)
11. [Related Ownership on a Child](11-related-record.md)

[Laravel authorization](https://laravel.com/docs/13.x/authorization) documents policy arguments and responses.
