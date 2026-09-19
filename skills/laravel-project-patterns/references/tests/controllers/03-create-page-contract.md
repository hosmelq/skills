# Create Page Tests: Enum Props And Nested Parent IDs

Use for the positive page test after authentication, authorization, binding and
access restrictions in `describe('create')`. Assert 200, the exact Inertia
component, public identifiers and form props consumed by the page. A
component-only assertion misses a broken select or parent identifier.

These are separate controller examples in a fictional workshop application.
Routes, models, enum `options()`, `signIn(team: ...)` and `public_id` must be
adapted to the current project. Keep its configured test directory.

## Enum Options

Keep the name `shows the create page`. Assert complete enum options,
not only that the prop exists. Equivalent page shapes can share this pattern:

| Page | Component | Props beyond the team public ID |
| --- | --- | --- |
| Member | `members/Create` | None |
| Item category | `item-categories/Create` | None |
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

        signIn(team: $team);

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

        signIn(team: $servicePlan->team);

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

Assert both parent IDs, not only the team ID.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRule;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $planRule = PlanRule::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

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

## Related References

1. [Ordered create block and base example](00-create-test-order.md)
2. [Initial empty province list and dependent selects](06-create-dependent-selects.md)
3. [Active categories and hidden internal parent keys](04-create-select-options.md)
4. [Multi-option page with nested metadata](05-create-nested-option-props.md)
