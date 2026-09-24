# Show Tests: Inactive Records And Ancestors

Complete browser GET show examples remain readable for an inactive record, parent or ancestor. Assert the public record ID and exact deactivation timestamp at the correct resource level.

## Inactive Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\ItemGroup;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page for an inactive record', function (): void {
        $itemGroup = ItemGroup::factory()->deactivated()->createOne();

        login(team: $itemGroup->team);

        $response = get(route('teams.item-groups.show', [
            'team' => $itemGroup->team,
            'item_group' => $itemGroup,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($itemGroup): void {
                $page->component('item-groups/Show')
                    ->where('team.id', $itemGroup->team->public_id)
                    ->where('itemGroup.id', $itemGroup->public_id)
                    ->where(
                        'itemGroup.deactivated_at',
                        $itemGroup->deactivated_at->toJSON(),
                    );
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

describe('show', function (): void {
    it('shows the detail page under an inactive parent', function (): void {
        $planRule = PlanRule::factory()->for(ServicePlan::factory()->deactivated())->createOne();

        login(team: $planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.show', [
            'team' => $planRule->servicePlan->team,
            'service_plan' => $planRule->servicePlan,
            'plan_rule' => $planRule,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($planRule): void {
                $page->component('service-plans/plan-rules/Show')
                    ->where('planRule.id', $planRule->public_id)
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

describe('show', function (): void {
    it('shows the detail page under an inactive ancestor', function (): void {
        $planRule = PlanRule::factory()->for(ServicePlan::factory()->deactivated())->createOne();
        $rate = PlanRate::factory()->recycle($planRule)->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.show', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($rate): void {
                $page->component('service-plans/rates/Show')
                    ->where('rate.id', $rate->public_id)
                    ->where('servicePlan.deactivated_at', $rate->planRule->servicePlan->deactivated_at->toJSON());
            });
    });
});
```
