# Action Tests: Translate an Update Resolver Failure

Integration action tests: Mock the active-plan resolver throwing inactivity or missing-model failures; translate the exception and preserve the existing selection. This does not simulate concurrent writes.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\EnsureActiveServicePlan;
use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Exceptions\WorkOrders\ServicePlanIsUnavailable;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('maps an unavailable plan resolver failure', function (string $state): void {
    $team = Team::factory()->createOne();
    $workOrder = WorkOrder::factory()->for($team)->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $exception = match ($state) {
        'deactivated' => CannotUseDeactivatedServicePlan::becauseItIsDeactivated(),
        'deleted' => new ModelNotFoundException()->setModel(
            ServicePlan::class,
            [$servicePlan->id],
        ),
    };

    mock(EnsureActiveServicePlan::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (ServicePlan $candidate): bool => $candidate->is($servicePlan))
        ->andThrow($exception);

    expect(fn () => resolve(UpdateWorkOrder::class)->handle($workOrder, UpdateWorkOrderInput::from([
        'service_plan_id' => $servicePlan->id,
    ])))->toThrow(
        ServicePlanIsUnavailable::class,
        'The selected service plan is unavailable.',
    );

    assertDatabaseHas(WorkOrder::class, [
        'id' => $workOrder->id,
        'service_plan_id' => null,
    ]);
})->with([
    'deactivated' => 'deactivated',
    'deleted' => 'deleted',
]);
```
