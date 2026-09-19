# Controller Tests: Ordered Create Block

Use for a Laravel Pest controller test of a GET form route grouped in
`describe('create')`. Persistence and submitted-field validation belong to the
appropriate write action. Keep the current controller test file and configured
suite, including `tests-new` or module/DDD layouts.

All examples describe a fictional workshop application. Models, routes, props,
factory states, the team-aware `signIn()` helper and `public_id` identifiers are
illustrative contracts, not framework defaults. Adapt them to inspected project
code; do not introduce fields, helpers or dependencies just to copy an example.

## Test Order

Use this sequence when assembling the block. Keep only applicable cases and
preserve the relative order; do not alphabetize the tests or group unrelated
failures into a dataset. For each additional route level, repeat the binding
checks before moving to the next level.

1. `requires authentication` — valid route fixtures, no sign-in; redirect to login.
2. `prevents viewing from an unrelated tenant` — authenticated outsider; 403.
3. `returns not found when the parent belongs to another tenant` — sign in
   to the team in the URL; bind an unrelated parent; 404.
4. `returns not found when the parent is soft deleted` — 404.
5. `returns not found when the nested parent belongs to another parent in the same tenant`
   — two distinct parents within one team; 404.
6. `returns not found when the nested parent belongs to another tenant` — 404.
7. `returns not found when the nested parent is soft deleted` — 404.
8. `prevents viewing when the parent is inactive` — 403 where the policy
   denies opening the form.
9. `shows the create page` — 200, exact component, public IDs, enums,
   required options (including eligibility/order when promised) and any initial
   empty dependent list. Keep this name even when the page has richer props.
10. `loads dependent options for the selected value` —
    selected country and partial reload of the dependent province select.
11. `shows the create page without options with unavailable relations`
    — dataset of unavailable related records.
12. `shows the create page without options from another tenant` —
    option ownership independent of a valid related parent.
13. `shows the create page without unavailable options` —
    inactive, deleted or otherwise ineligible options.
14. `marks the page read only for final parent states` — each supported final state returns 200
    with `canMutate = false`.

Use identical names for identical behavior across controllers. The test file and
`describe('create')` already identify the entity and action: use
`shows the create page`, not `shows the create <entity> page`. Add a condition
only when it distinguishes another case in the block. Use parent/nested parent
roles; qualify the field or level only to distinguish cases in one block. Avoid
`prevents creating` for a GET authorization test. Preserve 403, scoped-binding 404 and read-only distinctions.

## Base Example

Here `signIn()` without arguments authenticates a member of another team;
`signIn(team: ...)` authorizes the supplied team. For nested routes, create
valid parents and pass all bound parameters even in the first two tests.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Team;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('requires authentication', function (): void {
        $team = Team::factory()->createOne();

        $response = get(route('teams.members.create', [
            'team' => $team,
        ]));

        $response->assertRedirectToRoute('login');
    });

    it('prevents viewing from an unrelated tenant', function (): void {
        $unrelatedTeam = Team::factory()->createOne();

        signIn();

        $response = get(route('teams.members.create', [
            'team' => $unrelatedTeam,
        ]));

        $response->assertForbidden();
    });

    it('shows the create page', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        $response = get(route('teams.members.create', [
            'team' => $team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($team): void {
                $page->component('members/Create')
                    ->where('team.id', $team->public_id);
            });
    });
});
```


## Related References

File numbers follow the applicable case order; they do not require reading all
files. Search only the current requirement.

1. [Route binding and soft-deleted parents](01-create-route-bindings.md)
2. [Inactive parent access restriction](02-create-inactive-parent.md)
3. [Positive page contract and enum props](03-create-page-contract.md)
4. [Positive eligible and ordered select options](04-create-select-options.md)
5. [Positive nested option IDs and metadata](05-create-nested-option-props.md)
6. [Dependent selects and partial reload](06-create-dependent-selects.md)
7. [Option exclusions: relations, ownership, own state](07-create-option-filters.md)
8. [Read-only final parent states](08-create-read-only.md)
