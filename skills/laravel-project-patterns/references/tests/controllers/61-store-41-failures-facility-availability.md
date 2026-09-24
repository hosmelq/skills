# Store Tests: Failures Facility Availability

POST store: Mocked exceptions test validation mapping only. Received/current/pickup facility become unavailable; same message does not permit merging their distinct fields.

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

        login(team: $team);

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

        login(team: $team);

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

        login(team: $team);

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
