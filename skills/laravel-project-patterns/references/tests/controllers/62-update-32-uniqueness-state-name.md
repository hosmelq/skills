# Update Tests: Uniqueness State Name

Pest PATCH update: State-name uniqueness retains case-insensitive duplicate and inactive reservation, current-name reuse, other tenant and soft-deleted reuse.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrderStatuses\Inputs\UpdateWorkOrderStatusInput;
use App\Actions\WorkOrderStatuses\UpdateWorkOrderStatus;
use App\Models\WorkOrderStatus;

describe('update', function (): void {
    it('rejects a case-insensitive duplicate value in the same scope', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne([
            'name' => 'Ready',
        ]);
        $duplicateWorkOrderStatus = WorkOrderStatus::factory()->recycle($workOrderStatus->team)->createOne([
            'name' => 'Received',
        ]);

        login(team: $workOrderStatus->team);

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => mb_strtolower($duplicateWorkOrderStatus->name),
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('rejects a case-insensitive value reserved by an inactive record', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne([
            'name' => 'Ready',
        ]);
        $deactivatedWorkOrderStatus = WorkOrderStatus::factory()
            ->deactivated()
            ->recycle($workOrderStatus->team)
            ->createOne([
                'name' => 'Received',
            ]);

        login(team: $workOrderStatus->team);

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => mb_strtolower($deactivatedWorkOrderStatus->name),
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('allows a value used in a different scope', function (): void {
        WorkOrderStatus::factory()->createOne([
            'name' => 'Received',
        ]);

        $workOrderStatus = WorkOrderStatus::factory()->createOne([
            'name' => 'Ready',
        ]);

        login(team: $workOrderStatus->team);

        mock(UpdateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                WorkOrderStatus $workOrderStatusArgument,
                UpdateWorkOrderStatusInput $input
            ): bool => $workOrderStatusArgument->is($workOrderStatus)
                && $input->name === 'Received');

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => 'Received',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status updated');
    });

    it('allows the current value', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne([
            'name' => 'Received',
        ]);

        login(team: $workOrderStatus->team);

        mock(UpdateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                WorkOrderStatus $workOrderStatusArgument,
                UpdateWorkOrderStatusInput $input
            ): bool => $workOrderStatusArgument->is($workOrderStatus)
                && $input->name === 'Received');

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => 'Received',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status updated');
    });

    it('allows a value used by a soft deleted record', function (): void {
        $deletedWorkOrderStatus = WorkOrderStatus::factory()
            ->trashed()
            ->createOne([
                'name' => 'Received',
            ]);
        $workOrderStatus = WorkOrderStatus::factory()
            ->recycle($deletedWorkOrderStatus->team)
            ->createOne([
                'name' => 'Ready',
            ]);

        login(team: $workOrderStatus->team);

        mock(UpdateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                WorkOrderStatus $workOrderStatusArgument,
                UpdateWorkOrderStatusInput $input
            ): bool => $workOrderStatusArgument->is($workOrderStatus)
                && $input->name === 'Received');

        $response = patch(route('teams.work-order-statuses.update', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ]), [
            'name' => 'Received',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $workOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status updated');
    });
});
```
