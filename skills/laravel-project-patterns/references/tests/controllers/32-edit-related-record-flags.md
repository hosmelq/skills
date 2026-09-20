# Edit Tests: Absent And Present Related Rate Flags

GET edit hasRates=false without rates and true with a real rate fixture. Covers a service plan whose rates are descendants through rules, and an individual rule whose rates are direct children. Normal page cases retain complete IDs and enums; true-flag cases assert component and flag.

Keep shows the edit page before shows the edit page and marks existing rates. The two hierarchies have separate complete examples.

## Plan Descendant Rates

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\TransitTimeUnit;
use App\Enums\WeightUnit;
use App\Models\PlanRate;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page', function (): void {
        $servicePlan = ServicePlan::factory()->createOne();

        signIn(team: $servicePlan->team);

        $response = get(route('teams.service-plans.edit', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($servicePlan): void {
                $page->component('service-plans/Edit')
                    ->where('hasRates', false)
                    ->where('team.id', $servicePlan->team->public_id)
                    ->where('servicePlan.id', $servicePlan->public_id)
                    ->where('transitTimeUnits', TransitTimeUnit::options())
                    ->where('weightUnits', WeightUnit::options());
            });
    });

    it('shows the edit page and marks existing rates', function (): void {
        $rate = PlanRate::factory()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.edit', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page): void {
                $page->component('service-plans/Edit')
                    ->where('hasRates', true);
            });
    });
});
```

## Rule Child Rates

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\BillableWeightRoundingMode;
use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Models\PlanRate;
use App\Models\PlanRule;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page', function (): void {
        $planRule = PlanRule::factory()->createOne();

        signIn(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.edit', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($planRule): void {
                $page->component('service-plans/plan-rules/Edit')
                    ->where('countryCodes', CountryCode::options())
                    ->where('currencyCodes', CurrencyCode::options())
                    ->where('hasRates', false)
                    ->where('team.id', $planRule->servicePlan->team->public_id)
                    ->where('planRule.id', $planRule->public_id)
                    ->where('roundingModes', BillableWeightRoundingMode::options())
                    ->where('servicePlan.id', $planRule->servicePlan->public_id);
            });
    });

    it('shows the edit page and marks existing rates', function (): void {
        $rate = PlanRate::factory()->createOne();

        signIn(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.edit', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page): void {
                $page->component('service-plans/plan-rules/Edit')
                    ->where('hasRates', true);
            });
    });
});
```
