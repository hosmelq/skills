# Update Tests: Access Inactive State

PATCH update: Real inactive record/parent/ancestor fixtures cause 403 before the update action; retain explicit shouldNotReceive assertions and the submitted payload.

## Prevents updating when the record is inactive — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Facilities\UpdateFacility;
use App\Models\Facility;

describe('update', function (): void {
    it('prevents updating when the record is inactive', function (): void {
        $facility = Facility::factory()->deactivated()->createOne();

        login(team: $facility->team);

        mock(UpdateFacility::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.facilities.update', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents updating when the record is inactive — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Cabinets\UpdateCabinet;
use App\Models\Cabinet;

describe('update', function (): void {
    it('prevents updating when the record is inactive', function (): void {
        $cabinet = Cabinet::factory()->deactivated()->createOne();

        login(team: $cabinet->member->team);

        mock(UpdateCabinet::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.members.cabinets.update', [
            'team' => $cabinet->member->team,
            'member' => $cabinet->member,
            'cabinet' => $cabinet,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents updating when the parent is inactive — variant 3

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\UpdatePlanRule;
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('update', function (): void {
    it('prevents updating when the parent is inactive', function (): void {
        $planRule = PlanRule::factory()->for(ServicePlan::factory()->deactivated())->createOne();

        login(team: $planRule->servicePlan->team);

        mock(UpdatePlanRule::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.service-plans.plan-rules.update', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertForbidden();
    });
});
```

## Prevents updating when the ancestor is inactive — variant 4

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\ServicePlans\UpdatePlanRate;
use App\Models\PlanRate;
use App\Models\PlanRule;
use App\Models\ServicePlan;

describe('update', function (): void {
    it('prevents updating when the ancestor is inactive', function (): void {
        $planRule = PlanRule::factory()->for(ServicePlan::factory()->deactivated())->createOne();
        $rate = PlanRate::factory()->recycle($planRule)->createOne();

        login(team: $rate->planRule->servicePlan->team);

        mock(UpdatePlanRate::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.service-plans.plan-rules.rates.update', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]), [
            'name' => 'Updated',
        ]);

        $response->assertForbidden();
    });
});
```
