# Model Tests: Loaded Relations and Nulls

Model resource JSON with multiple distinct relation roles, including several relations to the same model type. Compare complete loaded resources and explicitly loaded absent relations as null.

Factory states supply the optional roles. Keep their identities separate; recycling a pool of same-type parents cannot select a specific role.

```php
<?php

declare(strict_types=1);

use App\Models\WorkOrder;

it('includes loaded relationship resources', function (): void {
    $workOrder = WorkOrder::factory()
        ->withCurrentFacility()
        ->withCabinet()
        ->withDestinationFacility()
        ->withOriginFacility()
        ->withPlanRule()
        ->createOne();

    $currentFacilityResource = json_decode($workOrder->currentFacility->toResource()->toJson(), true);
    $memberResource = json_decode($workOrder->member->toResource()->toJson(), true);
    $cabinetResource = json_decode($workOrder->cabinet->toResource()->toJson(), true);
    $destinationFacilityResource = json_decode($workOrder->destinationFacility->toResource()->toJson(), true);
    $planRuleResource = json_decode(
        $workOrder->planRule->toResource()->toJson(),
        true,
    );
    $originFacilityResource = json_decode(
        $workOrder->originFacility->toResource()->toJson(),
        true,
    );
    $servicePlanResource = json_decode($workOrder->servicePlan->toResource()->toJson(), true);
    $statusResource = json_decode($workOrder->workOrderStatus->toResource()->toJson(), true);

    $resource = json_decode($workOrder->toResource()->toJson(), true);

    expect($resource)
        ->current_facility->toEqual($currentFacilityResource)
        ->member->toEqual($memberResource)
        ->cabinet->toEqual($cabinetResource)
        ->work_order_status->toEqual($statusResource)
        ->destination_facility->toEqual($destinationFacilityResource)
        ->origin_facility->toEqual($originFacilityResource)
        ->service_plan->toEqual($servicePlanResource)
        ->plan_rule->toEqual($planRuleResource);
});

it('includes null when nullable relations are loaded but unassigned', function (): void {
    $workOrder = WorkOrder::factory()->createOne();

    $workOrder->load([
        'currentFacility',
        'member',
        'cabinet',
        'destinationFacility',
        'originFacility',
        'servicePlan',
        'planRule',
    ]);

    $resource = json_decode($workOrder->toResource()->toJson(), true);

    expect($resource)->toMatchArray([
        'current_facility' => null,
        'member' => null,
        'cabinet' => null,
        'destination_facility' => null,
        'origin_facility' => null,
        'service_plan' => null,
        'plan_rule' => null,
    ]);
});
```
