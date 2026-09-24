# Store Tests: Mapping Root Related Id

Pest POST store: Public facility ID decoded with reference value under tenant; returned record drives detail redirect.

## Stores the record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Models\Facility;
use App\Models\Team;
use App\Models\WorkOrder;

describe('store', function (): void {
    it('stores the record', function (): void {
        $team = Team::factory()->createOne();
        $facility = Facility::factory()->recycle($team)->createOne();
        $workOrder = WorkOrder::factory()->recycle($team)->createOne();

        login(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Team $teamArgument,
                CreateWorkOrderInput $input,
            ): bool => $teamArgument->is($team)
                && $input->currentFacilityId === $facility->id
                && $input->reference === 'REF-NEW')
            ->andReturn($workOrder);

        $response = post(route('teams.work-orders.store', $team), [
            'current_facility_id' => $facility->public_id,
            'reference' => 'REF-NEW',
        ]);

        $response->assertRedirectToRoute('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ])->assertToast('Work order created');
    });
});
```
