# Show Tests: Record Binding Under Two Parents

GET show tenant / ancestor / parent / record binding: wrong sibling parent, another same-tenant ancestor, foreign record and soft deleted target return 404. Preserve distinct discriminators in same-ancestor fixture graphs.

## Two Parents

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\CountryCode;
use App\Models\PlanRate;
use App\Models\PlanRule;

describe('show', function (): void {
    it('returns not found when the record belongs to another parent under the same ancestor', function (): void {
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

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another ancestor in the same tenant', function (): void {
        $rate = PlanRate::factory()->createOne();
        $unrelatedRate = PlanRate::factory()->recycle($rate->planRule->servicePlan->team)->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $planRule = PlanRule::factory()->createOne();
        $unrelatedRate = PlanRate::factory()->createOne();

        login(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
            'rate' => $unrelatedRate,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $rate = PlanRate::factory()->trashed()->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertNotFound();
    });
});
```
