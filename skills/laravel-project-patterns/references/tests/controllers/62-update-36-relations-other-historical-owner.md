# Update Tests: Relations Other Historical Owner

PATCH update: A historical relation attached to a different record is rejected for the current record; this differs from retaining its own selected historical relation.

## Rejects a historical relation selected by another record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Models\Member;
use App\Models\Team;
use App\Models\WorkOrder;

describe('update', function (): void {
    it('rejects a historical relation selected by another record', function (): void {
        $team = Team::factory()->createOne();
        $member = Member::factory()->trashed()->recycle($team)->createOne();
        WorkOrder::factory()->for($member)->recycle($team)->createOne();
        $workOrder = WorkOrder::factory()->recycle($team)->createOne();

        login(team: $team);

        mock(UpdateWorkOrder::class)
            ->shouldNotReceive('handle');

        $response = patch(route('teams.work-orders.update', [
            'team' => $team,
            'work_order' => $workOrder,
        ]), ['member_id' => $member->sqid]);

        $response->assertRedirectBackWithErrors([
            'member_id' => 'The selected member id is invalid.',
        ]);
    });
});
```
