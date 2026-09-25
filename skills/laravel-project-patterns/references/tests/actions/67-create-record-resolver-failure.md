# Action Tests: Translate a Create Resolver Failure

Integration action tests: Mock the active-plan resolver throwing an inactivity or missing-model exception; check domain exception translation and no inserted record. This does not simulate concurrent writes.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\mock;

use App\Actions\ServicePlans\EnsureActiveServicePlan;
use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Exceptions\WorkOrders\ServicePlanIsUnavailable;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;

it('maps an unavailable plan resolver failure', function (string $state): void {
    $team = Team::factory()->createOne();
    WorkOrderStatus::factory()->initial()->recycle($team)->createOne();
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

    expect(fn () => resolve(CreateWorkOrder::class)->handle($team, CreateWorkOrderInput::from([
        'service_plan_id' => $servicePlan->id,
    ])))->toThrow(
        ServicePlanIsUnavailable::class,
        'The selected service plan is unavailable.',
    );

    assertDatabaseCount(WorkOrder::class, 0);
})->with([
    'deactivated' => 'deactivated',
    'deleted' => 'deleted',
]);
```
