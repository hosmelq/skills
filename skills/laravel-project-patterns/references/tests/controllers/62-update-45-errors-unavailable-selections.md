# Update Tests: Errors Unavailable Selections

Pest PATCH update: Each unavailable selected relation has its own mocked exception and exact field error: plan, member, received facility, pickup facility and cabinet.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

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
    it('maps an unavailable service plan rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

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

    it('maps an unavailable member rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

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

    it('maps an unavailable received facility rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

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

    it('maps an unavailable pickup facility rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

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

    it('maps an unavailable cabinet rejection to validation', function (): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

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
