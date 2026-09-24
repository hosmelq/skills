# Initial Record Tests: Ordered Cases

Pest browser POST initial selection: ordered guest/tenant access, foreign/deleted record bindings, mocked eligibility exception mapped to validation and successful action delegation with redirect/toast.

Keep the top-level case order below. The rejection uses an ordinary fixture and a mocked action exception; it does not establish actual ineligibility. The successful mock does not prove persistence. `signIn()` creates an outsider; `signIn(team: ...)` supplies membership.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrderStatuses\SetInitialWorkOrderStatus;
use App\Exceptions\CannotSetInitialWorkOrderStatus;
use App\Models\Team;
use App\Models\WorkOrderStatus;

it('requires authentication', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne();

    $response = post(route('teams.work-order-statuses.initial.store', [
        'team' => $workOrderStatus->team,
        'work_order_status' => $workOrderStatus,
    ]));

    $response->assertRedirectToRoute('login');
});

it('prevents setting the initial record from an unrelated tenant', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne();

    signIn();

    $response = post(route('teams.work-order-statuses.initial.store', [
        'team' => $workOrderStatus->team,
        'work_order_status' => $workOrderStatus,
    ]));

    $response->assertForbidden();
});

it('returns not found when the record belongs to another tenant', function (): void {
    $team = Team::factory()->createOne();

    $workOrderStatus = WorkOrderStatus::factory()->createOne();

    signIn(team: $team);

    $response = post(route('teams.work-order-statuses.initial.store', [
        'team' => $team,
        'work_order_status' => $workOrderStatus,
    ]));

    $response->assertNotFound();
});

it('returns not found when the record is soft deleted', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->trashed()->createOne();

    signIn(team: $workOrderStatus->team);

    $response = post(route('teams.work-order-statuses.initial.store', [
        'team' => $workOrderStatus->team,
        'work_order_status' => $workOrderStatus,
    ]));

    $response->assertNotFound();
});

it('maps an ineligible initial record rejection to validation', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne();

    signIn(team: $workOrderStatus->team);

    mock(SetInitialWorkOrderStatus::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (WorkOrderStatus $workOrderStatusArgument): bool => $workOrderStatusArgument->is($workOrderStatus))
        ->andThrow(CannotSetInitialWorkOrderStatus::becauseItIsNotActiveReceived());

    $response = post(route('teams.work-order-statuses.initial.store', [
        'team' => $workOrderStatus->team,
        'work_order_status' => $workOrderStatus,
    ]));

    $response->assertRedirectBackWithErrors([
        'work_order_status' => 'Only active received work order statuses can be marked as initial.',
    ]);
});

it('sets the initial record', function (): void {
    $workOrderStatus = WorkOrderStatus::factory()->createOne();

    signIn(team: $workOrderStatus->team);

    mock(SetInitialWorkOrderStatus::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (WorkOrderStatus $workOrderStatusArgument): bool => $workOrderStatusArgument->is($workOrderStatus));

    $response = post(route('teams.work-order-statuses.initial.store', [
        'team' => $workOrderStatus->team,
        'work_order_status' => $workOrderStatus,
    ]));

    $response->assertRedirect()
        ->assertToast('Initial work order status updated');
});
```
