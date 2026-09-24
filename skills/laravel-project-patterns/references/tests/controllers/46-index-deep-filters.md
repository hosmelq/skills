# Index Tests: Nested Ancestor And Deletion Filters

Complete two-parent GET index exclusions for a sibling parent, another ancestor in the same tenant and a soft deleted record. Preserve distinct discriminators and non-overlapping ranges so fixtures isolate the intended edge.

## Sibling Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CountryCode;
use App\Models\PlanRate;
use App\Models\PlanRule;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records from other parents under the same ancestor', function (): void {
        $planRule = PlanRule::factory()
            ->forCountry(CountryCode::Canada)
            ->createOne();
        $rate = PlanRate::factory()->recycle($planRule)->createOne();
        $unrelatedPlanRule = PlanRule::factory()
            ->recycle($planRule->servicePlan)
            ->forCountry(CountryCode::Japan)
            ->createOne();
        $unrelatedRate = PlanRate::factory()->recycle($unrelatedPlanRule)->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($rate, $unrelatedRate): void {
                $page->component('service-plans/rates/Index')
                    ->has('rates.data', 1, function (AssertableJson $json) use ($rate, $unrelatedRate): void {
                        $json
                            ->where('id', $rate->public_id)
                            ->whereNot('id', $unrelatedRate->public_id)
                            ->etc();
                    });
            });
    });
});
```

## Other Ancestor

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes records from other ancestors in the same tenant', function (): void {
        $rate = PlanRate::factory()->createOne();
        $unrelatedRate = PlanRate::factory()->recycle($rate->planRule->servicePlan->team)->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($rate, $unrelatedRate): void {
                $page->component('service-plans/rates/Index')
                    ->has('rates.data', 1, function (AssertableJson $json) use ($rate, $unrelatedRate): void {
                        $json
                            ->where('id', $rate->public_id)
                            ->whereNot('id', $unrelatedRate->public_id)
                            ->etc();
                    });
            });
    });
});
```

## Soft Deleted Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;
use Illuminate\Testing\Fluent\AssertableJson;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('excludes soft deleted records', function (): void {
        $rate = PlanRate::factory()
            ->forRange(0, 10)
            ->createOne();
        $deletedRate = PlanRate::factory()
            ->recycle($rate->planRule)
            ->forRange(10, 20)
            ->trashed()
            ->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.index', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($rate, $deletedRate): void {
                $page->component('service-plans/rates/Index')
                    ->has('rates.data', 1, function (AssertableJson $json) use ($rate, $deletedRate): void {
                        $json
                            ->where('id', $rate->public_id)
                            ->whereNot('id', $deletedRate->public_id)
                            ->etc();
                    });
            });
    });
});
```
