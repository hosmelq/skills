# Move Tests: Predecessor Group Binding

Pest browser PATCH reorder within a base-status group: an existing predecessor in the same tenant but another group returns 404, independently of field validation.

The current-record factory defaults to the received group; the predecessor uses the exception group. Preserve this difference when adapting the fixtures.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('returns not found when the predecessor belongs to another group', function (): void {
    $team = Team::factory()->createOne();
    $workOrderStatus = WorkOrderStatus::factory()->recycle($team)->createOne();
    $moveAfterWorkOrderStatus = WorkOrderStatus::factory()->recycle($team)->createOne([
        'base_status' => WorkOrderBaseStatus::Exception,
    ]);

    login(team: $team);

    $response = patch(route('teams.work-order-statuses.move', [
        'team' => $team,
        'work_order_status' => $workOrderStatus,
    ]), [
        'move_after_id' => $moveAfterWorkOrderStatus->public_id,
    ]);

    $response->assertNotFound();
});
```
