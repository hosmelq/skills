# Update Tests: Mapping State Input

PATCH update: State update preserves name mapping, enum conversion and a false boolean in separate complete action expectations and responses.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrderStatuses\Inputs\UpdateWorkOrderStatusInput;
use App\Actions\WorkOrderStatuses\UpdateWorkOrderStatus;
use App\Enums\WorkOrderBaseStatus;
use App\Models\WorkOrderStatus;

describe('update', function (): void {
    it('updates the record', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        mock(UpdateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                WorkOrderStatus $workOrderStatusArgument,
                UpdateWorkOrderStatusInput $input
            ): bool => $workOrderStatusArgument->is($workOrderStatus)
                && $input->name === 'Exception');

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => 'Exception',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status updated');
    });

    it('maps a base status to the update input', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        mock(UpdateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                WorkOrderStatus $workOrderStatusArgument,
                UpdateWorkOrderStatusInput $input
            ): bool => $workOrderStatusArgument->is($workOrderStatus)
                && $input->baseStatus === WorkOrderBaseStatus::Exception);

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'base_status' => WorkOrderBaseStatus::Exception(),
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status updated');
    });

    it('maps a false boolean to the update input', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne();

        login(team: $workOrderStatus->team);

        mock(UpdateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                WorkOrderStatus $workOrderStatusArgument,
                UpdateWorkOrderStatusInput $input
            ): bool => $workOrderStatusArgument->is($workOrderStatus)
                && $input->isMemberVisible === false);

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'is_member_visible' => '0',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status updated');
    });
});
```
