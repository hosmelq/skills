# Store Tests: Failures State And Reference

Pest POST store: Required initial status, unavailable selected status and duplicate reference remain distinct action exception translations.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Exceptions\WorkOrders\InitialWorkOrderStatusIsUnavailable;
use App\Exceptions\WorkOrders\WorkOrderReferenceAlreadyExists;
use App\Exceptions\WorkOrders\WorkOrderStatusIsUnavailable;
use App\Models\Team;

describe('store', function (): void {
    it('maps an unavailable initial status rejection to validation', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(InitialWorkOrderStatusIsUnavailable::becauseNoneIsAvailable());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'work_order_status_id' => 'An active initial received work order status is required.',
        ]);
    });

    it('maps a duplicate reference rejection to validation', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderReferenceAlreadyExists::becauseItIsAlreadyInUse());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'reference' => 'The reference has already been taken.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: work_order_status_id', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(WorkOrderStatusIsUnavailable::becauseItIsUnavailable());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'work_order_status_id' => 'The selected value is no longer available.',
        ]);
    });
});
```
