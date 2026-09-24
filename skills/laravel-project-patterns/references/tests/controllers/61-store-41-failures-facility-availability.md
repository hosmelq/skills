# Store Tests: Failures Facility Availability

Pest POST store: Received/current/pickup facility become unavailable; same message does not permit merging their distinct fields.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\post;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Exceptions\WorkOrders\CurrentFacilityIsUnavailable;
use App\Exceptions\WorkOrders\PickupFacilityIsUnavailable;
use App\Exceptions\WorkOrders\ReceivedFacilityIsUnavailable;
use App\Models\Team;

describe('store', function (): void {
    it('maps an unavailable relation rejection to validation: received_facility_id', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(ReceivedFacilityIsUnavailable::becauseItIsUnavailable());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'received_facility_id' => 'The selected value is no longer available.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: current_facility_id', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(CurrentFacilityIsUnavailable::becauseItIsUnavailable());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'current_facility_id' => 'The selected value is no longer available.',
        ]);
    });

    it('maps an unavailable relation rejection to validation: pickup_facility_id', function (): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        mock(CreateWorkOrder::class)
            ->shouldReceive('handle')
            ->once()
            ->andThrow(PickupFacilityIsUnavailable::becauseItIsUnavailable());

        $response = post(route('teams.work-orders.store', $team));

        $response->assertRedirectBackWithErrors([
            'pickup_facility_id' => 'The selected value is no longer available.',
        ]);
    });
});
```
