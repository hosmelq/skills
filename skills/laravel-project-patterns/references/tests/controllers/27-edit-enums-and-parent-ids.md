# Edit Tests: Full Enum Options And Parent IDs

Positive GET edit contracts with complete enum options and public record/tenant IDs, plus a deeply nested page that asserts both ancestor and immediate-parent public IDs. Use the plain page name; these variants add assertions beyond the direct base example.

Keep shows the edit page. Use the full inspected enum options, and retain every parent ID consumed by the form.

## State Enum

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\BaseStatus;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        $response = get(route('teams.work-order-statuses.edit', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($workOrderStatus): void {
                $page->component('work-order-statuses/Edit')
                    ->where('baseStatuses', BaseStatus::options())
                    ->where('team.id', $workOrderStatus->team->sqid)
                    ->where('workOrderStatus.id', $workOrderStatus->sqid);
            });
    });
});
```

## Two Parent IDs

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;
use Inertia\Testing\AssertableInertia;

describe('edit', function (): void {
    it('shows the edit page', function (): void {
        $rate = PlanRate::factory()->createOne();

        login(team: $rate->planRule->servicePlan->team);

        $response = get(route('teams.service-plans.plan-rules.rates.edit', [
            'team' => $rate->planRule->servicePlan->team,
            'service_plan' => $rate->planRule->servicePlan,
            'plan_rule' => $rate->planRule,
            'rate' => $rate,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($rate): void {
                $page->component('service-plans/rates/Edit')
                    ->where('team.id', $rate->planRule->servicePlan->team->sqid)
                    ->where('planRule.id', $rate->planRule->sqid)
                    ->where('rate.id', $rate->sqid)
                    ->where('servicePlan.id', $rate->planRule->servicePlan->sqid);
            });
    });
});
```
