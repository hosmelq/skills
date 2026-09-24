# Create Page Tests: Enum Props And Nested Parent IDs

Positive create-page contracts: HTTP 200, exact Inertia component, public tenant and parent IDs, and complete enum options. Includes a flat form, one bound parent with enums, and two bound parents.

Keep `shows the create page` for each controller. Assert values consumed by the form, not merely prop existence.

## Enum Options

| Page | Component | Props beyond the team public ID |
| --- | --- | --- |
| Member | `members/Create` | None |
| Item group | `item-groups/Create` | None |
| Work order status | `work-order-statuses/Create` | `baseStatuses = WorkOrderBaseStatus::options()` |
| Service plan | `service-plans/Create` | `transitTimeUnits`, `weightUnits` |
| Plan rule | `service-plans/plan-rules/Create` | `servicePlan.id`, `countryCodes`, `currencyCodes`, `roundingModes` |
| Plan rate | `service-plans/rates/Create` | `servicePlan.id`, `planRule.id` |

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\Team;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $team = Team::factory()->createOne();

        login(team: $team);

        $response = get(route('teams.service-plans.create', [
            'team' => $team,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($team): void {
                $page->component('service-plans/Create')
                    ->where('team.id', $team->public_id)
                    ->where('transitTimeUnits', TransitTimeUnit::options())
                    ->where('weightUnits', WeightUnit::options());
            });
    });
});
```

## Nested Form With Enum Options

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\BillableWeightRoundingMode;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.create', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($servicePlan): void {
                $page->component('service-plans/plan-rules/Create')
                    ->where('countryCodes', CountryCode::options())
                    ->where('currencyCodes', CurrencyCode::options())
                    ->where('team.id', $servicePlan->team->public_id)
                    ->where('roundingModes', BillableWeightRoundingMode::options())
                    ->where('servicePlan.id', $servicePlan->public_id);
            });
    });
});
```

## Two Bound Parents

Assert both parent IDs.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $planRule = PlanRule::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.create', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($planRule): void {
                $page->component('service-plans/rates/Create')
                    ->where('team.id', $planRule->servicePlan->team->public_id)
                    ->where('planRule.id', $planRule->public_id)
                    ->where('servicePlan.id', $planRule->servicePlan->public_id);
            });
    });
});
```
