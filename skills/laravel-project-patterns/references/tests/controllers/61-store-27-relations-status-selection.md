# Store Tests: Relations Status Selection

Pest POST store: Selected received status inactive/deleted/foreign tenant or wrong base status.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

describe('store', function (): void {
    it('rejects an inactive received status', function (): void {
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

    it('rejects a soft deleted received status', function (): void {
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

    it('rejects a status that is not a received status', function (): void {
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

    it('rejects a received status from another tenant', function (): void {
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
});
```
