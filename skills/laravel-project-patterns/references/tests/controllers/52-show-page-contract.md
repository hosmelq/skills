# Show Tests: Page And Route Identifiers

Successful browser GET show examples assert the component, record public ID and every applicable tenant, parent and ancestor public ID. A separate variant also compares the record enum options to their owning enum.

## Direct Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Facility;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page', function (): void {
        $facility = Facility::factory()->createOne();

        login(team: $facility->team);

        $response = get(route('teams.facilities.show', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($facility): void {
                $page->component('facilities/Show')
                    ->where('team.id', $facility->team->sqid)
                    ->where('facility.id', $facility->sqid);
            });
    });
});
```

## One Parent

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\MemberAddress;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page', function (): void {
        $address = MemberAddress::factory()->createOne();

        login(team: $address->member->team);

        $response = get(route('teams.members.addresses.show', [
            'team' => $address->member->team,
            'member' => $address->member,
            'address' => $address,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($address): void {
                $page->component('members/addresses/Show')
                    ->where('address.id', $address->sqid)
                    ->where('member.id', $address->member->sqid)
                    ->where('team.id', $address->member->team->sqid);
            });
    });
});
```

## Two Parents

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\PlanRate;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page', function (): void {
        $rate = PlanRate::factory()->createOne();

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
                    ->where('team.id', $rate->planRule->servicePlan->team->sqid)
                    ->where('planRule.id', $rate->planRule->sqid)
                    ->where('rate.id', $rate->sqid)
                    ->where('servicePlan.id', $rate->planRule->servicePlan->sqid);
            });
    });
});
```

## Enum Options

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows the detail page', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        $response = get(route('teams.work-order-statuses.show', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($workOrderStatus): void {
                $page->component('work-order-statuses/Show')
                    ->where('baseStatuses', WorkOrderBaseStatus::options())
                    ->where('team.id', $workOrderStatus->team->sqid)
                    ->where('workOrderStatus.id', $workOrderStatus->sqid);
            });
    });
});
```
