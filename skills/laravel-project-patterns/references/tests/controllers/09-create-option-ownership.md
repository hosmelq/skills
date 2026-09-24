# Create Tests: Independent Option Ownership

GET create-page option exclusion by the option's own tenant, even when its related parent belongs to the authorized tenant. The response remains HTTP 200 with an empty list; place this after relation-state exclusions.

Keep the related parent local and the option foreign so the test isolates its own ownership. This is not a route-binding 404 case.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page without options from another tenant', function (): void {
        $team = Team::factory()->createOne();
        PlanRule::factory()
            ->for(Team::factory())
            ->for(ServicePlan::factory()->for($team))
            ->createOne();

        login(team: $team);

        $response = get(route('teams.work-orders.create', $team));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/Create')
                ->has('planRules', 0));
    });
});
```
