# Index Tests: Inactive Records And Ancestors

Complete GET index examples retain inactive rows and list children of inactive parents or ancestors. Assert public row IDs and exact deactivation timestamps; inactivity is distinct from soft deletion.

## Direct Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Facility;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('includes inactive records in the list', function (): void {
        $facility = Facility::factory()->deactivated()->createOne();

        login(team: $facility->team);

        $response = get(route('teams.facilities.index', [
            'team' => $facility->team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($facility): void {
                $page->component('facilities/Index')
                    ->where('facilities.data.0.id', $facility->sqid)
                    ->where('facilities.data.0.deactivated_at', $facility->deactivated_at->toJSON());
            });
    });
});
```

## Child Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Cabinet;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('includes inactive records in the list', function (): void {
        $cabinet = Cabinet::factory()->deactivated()->createOne();

        login(team: $cabinet->member->team);

        $response = get(route('teams.members.cabinets.index', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($cabinet): void {
                $page->component('members/cabinets/Index')
                    ->where('cabinets.data.0.id', $cabinet->sqid)
                    ->where('cabinets.data.0.deactivated_at', $cabinet->deactivated_at->toJSON());
            });
    });
});
```

## Inactive Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('lists records under an inactive parent', function (): void {
        $planRule = PlanRule::factory()->for(ServicePlan::factory()->deactivated())->createOne();

        login(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.index', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($planRule): void {
                $page->component('service-plans/plan-rules/Index')
                    ->where('planRules.data.0.id', $planRule->sqid)
                    ->where('servicePlan.deactivated_at', $planRule->servicePlan->deactivated_at->toJSON());
            });
    });
});
```

## Inactive Ancestor

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('lists records under an inactive ancestor', function (): void {
        $planRule = PlanRule::factory()->for(ServicePlan::factory()->deactivated())->createOne();
        $rate = PlanRate::factory()->recycle($planRule)->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($rate): void {
                $page->component('service-plans/rates/Index')
                    ->where('rates.data.0.id', $rate->sqid)
                    ->where('servicePlan.deactivated_at', $rate->planRule->servicePlan->deactivated_at->toJSON());
            });
    });
});
```
