# Store Tests: Uniqueness Name With Mapped Success

Pest POST store: Case-insensitive duplicates and exact inactive-name reservation; reuse carries typed name predicate and toast.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrderStatuses\CreateWorkOrderStatus;
use App\Actions\WorkOrderStatuses\Inputs\CreateWorkOrderStatusInput;
use App\Enums\WorkOrderBaseStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

describe('store', function (): void {
    it('rejects a case-insensitive duplicate value in the same scope', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->createOne([
            'name' => 'Received',
        ]);

        signIn(team: $workOrderStatus->team);

        $response = post(route('teams.work-order-statuses.store', [
            'team' => $workOrderStatus->team,
        ]), [
            'base_status' => WorkOrderBaseStatus::Received(),
            'name' => 'received',
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('rejects a value reserved by an inactive record', function (): void {
        $workOrderStatus = WorkOrderStatus::factory()->deactivated()->createOne([
            'name' => 'Received',
        ]);

        signIn(team: $workOrderStatus->team);

        $response = post(route('teams.work-order-statuses.store', [
            'team' => $workOrderStatus->team,
        ]), [
            'base_status' => WorkOrderBaseStatus::Received(),
            'name' => 'Received',
        ]);

        $response->assertRedirectBackWithErrors([
            'name' => 'The name has already been taken.',
        ]);
    });

    it('allows a value used in a different scope', function (): void {
        WorkOrderStatus::factory()->createOne([
            'name' => 'Received',
        ]);

        $team = Team::factory()->createOne();
        $workOrderStatus = WorkOrderStatus::factory()
            ->for($team)
            ->createOne();

        signIn(team: $team);

        mock(CreateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Team $teamArgument,
                CreateWorkOrderStatusInput $input
            ): bool => $teamArgument->is($team)
                && $input->name === 'Received')
            ->andReturn($workOrderStatus);

        $response = post(route('teams.work-order-statuses.store', [
            'team' => $team,
        ]), [
            'base_status' => WorkOrderBaseStatus::Received(),
            'name' => 'Received',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status created');
    });

    it('allows a value used by a soft deleted record', function (): void {
        $deletedWorkOrderStatus = WorkOrderStatus::factory()
            ->trashed()
            ->createOne([
                'name' => 'Received',
            ]);
        $workOrderStatus = WorkOrderStatus::factory()
            ->for($deletedWorkOrderStatus->team)
            ->createOne();

        signIn(team: $deletedWorkOrderStatus->team);

        mock(CreateWorkOrderStatus::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Team $teamArgument,
                CreateWorkOrderStatusInput $input
            ): bool => $teamArgument->is($deletedWorkOrderStatus->team)
                && $input->name === 'Received')
            ->andReturn($workOrderStatus);

        $response = post(route('teams.work-order-statuses.store', [
            'team' => $deletedWorkOrderStatus->team,
        ]), [
            'base_status' => WorkOrderBaseStatus::Received(),
            'name' => 'Received',
        ]);

        $response->assertRedirectToRoute('teams.work-order-statuses.show', [
            'team' => $deletedWorkOrderStatus->team,
            'work_order_status' => $workOrderStatus,
        ])
            ->assertToast('Work order status created');
    });
});
```
