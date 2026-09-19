# Create Tests: Options With Unavailable Relations

GET create-page option exclusions caused by unavailable relations on an otherwise active option. A dataset covers deleted members, deleted service plans, and deactivated service plans; place this before independent ownership and own-state cases.

An unavailable option does not prove exclusion of an active option with an unavailable relation. Keep all three dataset variants under the same canonical case name.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Enrollment;
use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\Team;
use Database\Factories\MemberFactory;
use Database\Factories\ServicePlanFactory;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page without options with unavailable relations', function (string $state): void {
        $team = Team::factory()->createOne();
        $member = Member::factory()
            ->when($state === 'deleted member', fn (MemberFactory $factory): MemberFactory => $factory->trashed())
            ->for($team)
            ->createOne();
        $servicePlan = ServicePlan::factory()
            ->when($state === 'deactivated service plan', fn (ServicePlanFactory $factory): ServicePlanFactory => $factory->deactivated())
            ->when($state === 'deleted service plan', fn (ServicePlanFactory $factory): ServicePlanFactory => $factory->trashed())
            ->for($team)
            ->createOne();
        Enrollment::factory()
            ->for($member)
            ->for($team)
            ->for($servicePlan)
            ->createOne();

        signIn(team: $team);

        $response = get(route('teams.work-orders.create', $team));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/Create')
                ->has('enrollments', 0));
    })->with([
        'deactivated service plan',
        'deleted member',
        'deleted service plan',
    ]);
});
```
