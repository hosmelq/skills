# Update Tests: Errors Unavailable Selections

PATCH update: Each unavailable selected relation has its own mocked exception and exact field error: plan, member, received facility, pickup facility and cabinet.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\WorkOrders\CabinetIsUnavailable;
use App\Exceptions\WorkOrders\MemberIsUnavailable;
use App\Exceptions\WorkOrders\PickupFacilityIsUnavailable;
use App\Exceptions\WorkOrders\ReceivedFacilityIsUnavailable;
use App\Exceptions\WorkOrders\ServicePlanIsUnavailable;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('maps an unavailable relation rejection to validation: service_plan_id', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(ServicePlanIsUnavailable::becauseItIsUnavailable());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'service_plan_id' => 'The selected value is no longer available.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: member_id', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(MemberIsUnavailable::becauseItIsUnavailable());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected value is no longer available.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: received_facility_id', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(ReceivedFacilityIsUnavailable::becauseItIsUnavailable());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'received_facility_id' => 'The selected value is no longer available.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: pickup_facility_id', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(PickupFacilityIsUnavailable::becauseItIsUnavailable());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'pickup_facility_id' => 'The selected value is no longer available.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: cabinet_id', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        login(team: $workOrder->team);

        mock(UpdateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(CabinetIsUnavailable::becauseItIsUnavailable());

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), ['note' => 'No']);

        $response->assertRedirectBackWithErrors([
            'cabinet_id' => 'The selected value is no longer available.',
        ]);
    });
});
```
