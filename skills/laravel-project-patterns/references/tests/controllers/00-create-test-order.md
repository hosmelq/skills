# Controller Tests: Ordered Create Block

Ordered Laravel Pest tests for GET create forms: authentication redirects, tenant authorization, scoped parents, lifecycle restrictions, positive page contracts, dependent selects, option exclusions and read-only states. Includes the base block and canonical names.

## Test Order

Keep applicable cases in this order; do not alphabetize or combine unrelated failures in a dataset. Repeat binding checks for each additional route level. Persistence and submitted-field validation belong to the write action.

1. `requires authentication` — valid route fixtures, no sign-in; redirect to login.
2. `prevents viewing from an unrelated tenant` — authenticated outsider; 403.
3. `returns not found when the parent belongs to another tenant` — authorize the URL tenant; unrelated parent; 404.
4. `returns not found when the parent is soft deleted` — 404.
5. `returns not found when the nested parent belongs to another parent in the same tenant` — two distinct parents within one tenant; 404.
6. `returns not found when the nested parent belongs to another tenant` — 404.
7. `returns not found when the nested parent is soft deleted` — 404.
8. `prevents viewing when the parent is inactive` — policy forbids opening the form; 403.
9. `shows the create page` — 200, component, public IDs, enums, required options and any initially empty dependent list.
10. `loads dependent options for the selected value` — selected value and complete partial-reload options.
11. `shows the create page without options with unavailable relations` — unavailable related-record dataset.
12. `shows the create page without options from another tenant` — option ownership independent of a valid related parent.
13. `shows the create page without unavailable options` — inactive, deleted or otherwise ineligible options.
14. `marks the page read only for final parent states` — 200 and `canMutate = false` for each supported final state.

Reuse these names across controllers; the file identifies the entity. Qualify a parent level or field pair only to distinguish cases in one block. GET authorization uses `prevents viewing`.

## Base Example

Here `login()` authenticates an outsider; `login(team: ...)` authorizes that team. For nested routes, provide valid parents and all route parameters even in authentication and authorization tests.

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

        login();

        $response = get(route('teams.members.create', [
            'team' => $unrelatedTeam,
        ]));

        $response->assertForbidden();
    });

    it('shows the create page', function (): void {
        $team = Team::factory()->createOne();

        login(team: $team);

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
