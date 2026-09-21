# Store Tests: Mapping Nullable Input

Pest POST store: One complete explicit-null case asserts all 20 nullable input properties. It stays separate so ordinary ID/required-field queries do not load these fields.

## Accepts explicit nulls for nullable fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Models\Team;
use App\Models\WorkOrder;

describe('store', function (): void {
    it('accepts explicit nulls for nullable fields', function (): void {
        $team = Team::factory()->createOne();
        $workOrder = WorkOrder::factory()->for($team)->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Team $teamArgument,
                CreateWorkOrderInput $input,
            ): bool => $teamArgument->is($team)
                && $input->currentFacilityId === null
                && $input->memberId === null
                && $input->dimensionUnit === null
                && $input->externalCarrierName === null
                && $input->externalTrackingNumber === null
                && $input->height === null
                && $input->length === null
                && $input->cabinetId === null
                && $input->note === null
                && $input->workOrderStatusId === null
                && $input->pickupFacilityId === null
                && $input->receivedAt === null
                && $input->receivedLabelText === null
                && $input->receivedFacilityId === null
                && $input->reference === null
                && $input->servicePlanId === null
                && $input->planRuleId === null
                && $input->weight === null
                && $input->weightUnit === null
                && $input->width === null)
            ->andReturn($workOrder);

        $response = post(route('teams.work-orders.store', $team), [
            'current_facility_id' => null,
            'member_id' => null,
            'dimension_unit' => null,
            'external_carrier_name' => null,
            'external_tracking_number' => null,
            'height' => null,
            'length' => null,
            'cabinet_id' => null,
            'note' => null,
            'work_order_status_id' => null,
            'pickup_facility_id' => null,
            'received_at' => null,
            'received_label_text' => null,
            'received_facility_id' => null,
            'reference' => null,
            'service_plan_id' => null,
            'plan_rule_id' => null,
            'weight' => null,
            'weight_unit' => null,
            'width' => null,
        ]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ])->assertToast('Work order created');
    });
});
```
