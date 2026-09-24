# Store Tests: Relations Status Selection

Pest POST store: Selected initial status inactive/deleted/foreign tenant or wrong base status.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

describe('store', function (): void {
    it('rejects a newly assigned relation from another tenant: work_order_status_id', function (): void {
        $team = Team::factory()->createOne();
        $status = WorkOrderStatus::factory()->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'work_order_status_id' => $status->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'work_order_status_id' => 'The selected work order status id is invalid.',
        ]);
    });

    it('rejects a newly assigned inactive relation: work_order_status_id', function (): void {
        $status = WorkOrderStatus::factory()->deactivated()->createOne();
        $team = $status->team;

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'work_order_status_id' => $status->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'work_order_status_id' => 'The selected work order status id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation: work_order_status_id', function (): void {
        $status = WorkOrderStatus::factory()->trashed()->createOne();
        $team = $status->team;

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'work_order_status_id' => $status->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'work_order_status_id' => 'The selected work order status id is invalid.',
        ]);
    });

    it('rejects a noninitial status', function (): void {
        $status = WorkOrderStatus::factory()
            ->withBaseStatus(WorkOrderBaseStatus::InTransit)
            ->createOne();
        $team = $status->team;

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'work_order_status_id' => $status->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'work_order_status_id' => 'The selected work order status id is invalid.',
        ]);
    });
});
```
