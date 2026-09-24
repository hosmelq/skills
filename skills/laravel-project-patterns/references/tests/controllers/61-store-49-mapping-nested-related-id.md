# Store Tests: Mapping Nested Related Id

Pest POST store: Public related IDs decoded for nested parent actions, with distinct additional mapped fields and valid request payloads.

## Stores the record — variant 1

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\Cabinets\CreateCabinet;
use App\Actions\Cabinets\Inputs\CreateCabinetInput;
use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;

describe('store', function (): void {
    it('stores the record', function (): void {
        $member = Member::factory()->createOne();
        $servicePlan = ServicePlan::factory()->recycle($member->team)->createOne();
        $cabinet = Cabinet::factory()
            ->recycle($member)
            ->for($servicePlan)
            ->createOne();

        login(team: $member->team);

        mock(CreateCabinet::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Member $memberArgument,
                CreateCabinetInput $input
            ): bool => $memberArgument->is($member)
                && $input->servicePlanId === $servicePlan->id)
            ->andReturn($cabinet);

        $response = post(route('teams.members.cabinets.store', [
            'team' => $member->team,
            'member' => $member,
        ]), [
            'service_plan_id' => $servicePlan->public_id,
        ]);

        $response->assertRedirectToRoute('teams.members.cabinets.show', [
            'team' => $member->team,
            'member' => $member,
            'cabinet' => $cabinet,
        ])
            ->assertToast('Cabinet created');
    });
});
```

## Stores the record — variant 2

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrderLines\CreateWorkOrderLine;
use App\Actions\WorkOrderLines\Inputs\CreateWorkOrderLineInput;
use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\ItemGroup;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;

describe('store', function (): void {
    it('stores the record', function (): void {
        $workOrder = WorkOrder::factory()->createOne();
        $group = ItemGroup::factory()->recycle($workOrder->team)->createOne();
        $line = WorkOrderLine::factory()->recycle($workOrder)->createOne();

        login(team: $workOrder->team);

        mock(CreateWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (
                    WorkOrder $workOrderArgument,
                    CreateWorkOrderLineInput $input,
                ): bool => $workOrderArgument->is($workOrder)
                    && $input->description === 'Laptop computer'
                    && $input->quantity === 2
                    && $input->currencyCode === CurrencyCode::USD
                    && $input->itemGroupId === $group->id,
            )
            ->andReturn($line);

        $response = post(route('teams.work-orders.lines.store', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), [
            'currency_code' => CurrencyCode::USD->value,
            'declared_unit_value' => '125.50',
            'description' => 'Laptop computer',
            'dimension_unit' => LengthUnit::Inches->value,
            'height' => '2.0000',
            'length' => '14.0000',
            'item_group_id' => $group->public_id,
            'quantity' => 2,
            'weight' => '4.5000',
            'weight_unit' => WeightUnit::Pounds->value,
            'width' => '10.0000',
        ]);

        $response->assertRedirectToRoute('teams.work-orders.lines.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
            'line' => $line,
        ])->assertToast('Work order line created');
    });
});
```
