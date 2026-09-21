# Store Tests: Relations Facility Roles

Pest POST store: Three separate facility fields asserted together for foreign tenant, inactive and deleted relation fixtures.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Facility;
use App\Models\Team;

describe('store', function (): void {
    it('rejects facilities from another tenant', function (): void {
        $team = Team::factory()->createOne();
        $facility = Facility::factory()->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'current_facility_id' => $facility->public_id,
            'pickup_facility_id' => $facility->public_id,
            'received_facility_id' => $facility->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'current_facility_id' => 'The selected current facility id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
        ]);
    });

    it('rejects inactive facilities', function (): void {
        $team = Team::factory()->createOne();
        $facility = Facility::factory()->deactivated()->for($team)->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'current_facility_id' => $facility->public_id,
            'pickup_facility_id' => $facility->public_id,
            'received_facility_id' => $facility->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'current_facility_id' => 'The selected current facility id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
        ]);
    });

    it('rejects soft deleted facilities', function (): void {
        $team = Team::factory()->createOne();
        $facility = Facility::factory()->trashed()->for($team)->createOne();

        signIn(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'current_facility_id' => $facility->public_id,
            'pickup_facility_id' => $facility->public_id,
            'received_facility_id' => $facility->public_id,
        ]);

        $response->assertRedirectBackWithErrors([
            'current_facility_id' => 'The selected current facility id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
        ]);
    });
});
```
