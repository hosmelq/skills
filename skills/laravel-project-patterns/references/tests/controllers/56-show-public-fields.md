# Show Tests: Public Fields And Hidden Foreign Keys

Complete GET show detail contract verifies a known reference, initial state value, derived finality false and public record/tenant IDs. Three explicit raw foreign keys must be absent from the serialized record.

## Public Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\WorkOrder;
use Inertia\Testing\AssertableInertia;

describe('show', function (): void {
    it('shows public fields without raw foreign keys', function (): void {
        $workOrder = WorkOrder::factory()->createOne([
            'reference' => 'REF-100',
        ]);

        login(team: $workOrder->team);

        $response = get(route('teams.work-orders.show', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($workOrder): void {
                $page->component('work-orders/Show')
                    ->where('team.id', $workOrder->team->public_id)
                    ->where('workOrder.id', $workOrder->public_id)
                    ->where('workOrder.reference', 'REF-100')
                    ->where(
                        'workOrder.status.base_status',
                        WorkOrderBaseStatus::Received->value,
                    )
                    ->where('workOrder.status.is_final', false)
                    ->missing('workOrder.team_id')
                    ->missing('workOrder.work_order_status_id')
                    ->missing('workOrder.current_facility_id');
            });
    });
});
```
