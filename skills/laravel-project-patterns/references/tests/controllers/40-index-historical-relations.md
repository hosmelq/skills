# Index Tests: Historical Related Records

A live index row retains its soft deleted related member and final status. Complete GET example asserts the row and both relation IDs plus derived finality true; the principal record remains live.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Member;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('index', function (): void {
    it('includes historical relations in the list', function (): void {
        $team = Team::factory()->createOne();
        $status = WorkOrderStatus::factory()
            ->trashed()
            ->withBaseStatus(WorkOrderBaseStatus::Completed)
            ->recycle($team)
            ->createOne();
        $workOrder = WorkOrder::factory()
            ->withMember(Member::factory()->trashed())
            ->for($status, 'workOrderStatus')
            ->recycle($team)
            ->createOne();

        login(team: $team);

        $response = get(route('teams.work-orders.index', $team));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/Index')
                ->where('workOrders.data.0.id', $workOrder->sqid)
                ->where('workOrders.data.0.member.id', $workOrder->member->sqid)
                ->where('workOrders.data.0.status.id', $status->sqid)
                ->where('workOrders.data.0.status.is_final', true));
    });
});
```
