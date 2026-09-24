# Model Tests: Historical Related Resources

Model resource serialization preserves a soft-deleted related record through an explicit withTrashed load, and a historical parent inherited through an intermediate relation.

The first example explicitly loads the historical relation. The second relies on the inspected model/factory’s historical parent contract; these paths are not interchangeable.

```php
<?php

declare(strict_types=1);

use App\Models\Cabinet;
use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

it('preserves a historical related record in the resource', function (): void {
    $member = Member::factory()->trashed()->createOne();
    $cabinet = Cabinet::factory()
        ->for($member)
        ->for($member->team)
        ->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($member->team)
        ->for($cabinet)
        ->withMember()
        ->createOne();

    $workOrder->load([
        'member' => fn (BelongsTo $relation): BelongsTo => $relation->withTrashed(),
    ]);

    $resource = json_decode($workOrder->toResource()->toJson(), true);
    $memberResource = json_decode($workOrder->member->toResource()->toJson(), true);

    expect($resource)->member->toEqual($memberResource);
});

it('preserves a historical parent through an intermediate relation', function (): void {
    $servicePlan = ServicePlan::factory()->trashed()->createOne();
    $cabinet = Cabinet::factory()
        ->recycle($servicePlan->team)
        ->for($servicePlan)
        ->createOne();
    $workOrder = WorkOrder::factory()
        ->recycle($servicePlan->team)
        ->for($cabinet)
        ->withServicePlan()
        ->createOne();

    $resource = json_decode($workOrder->toResource()->toJson(), true);
    $servicePlanResource = json_decode($servicePlan->toResource()->toJson(), true);

    expect($resource)->service_plan->toEqual($servicePlanResource);
});
```
