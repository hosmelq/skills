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
    it('rejects a newly assigned relation from another tenant', function (): void {
        $team = Team::factory()->createOne();
        $facility = Facility::factory()->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'current_facility_id' => $facility->sqid,
            'pickup_facility_id' => $facility->sqid,
            'received_facility_id' => $facility->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'current_facility_id' => 'The selected current facility id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
        ]);
    });

    it('rejects a newly assigned inactive relation', function (): void {
        $team = Team::factory()->createOne();
        $facility = Facility::factory()->deactivated()->recycle($team)->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'current_facility_id' => $facility->sqid,
            'pickup_facility_id' => $facility->sqid,
            'received_facility_id' => $facility->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'current_facility_id' => 'The selected current facility id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
        ]);
    });

    it('rejects a newly assigned soft deleted relation', function (): void {
        $team = Team::factory()->createOne();
        $facility = Facility::factory()->trashed()->recycle($team)->createOne();

        login(team: $team);

        $response = post(route('teams.work-orders.store', $team), [
            'current_facility_id' => $facility->sqid,
            'pickup_facility_id' => $facility->sqid,
            'received_facility_id' => $facility->sqid,
        ]);

        $response->assertRedirectBackWithErrors([
            'current_facility_id' => 'The selected current facility id is invalid.',
            'pickup_facility_id' => 'The selected pickup facility id is invalid.',
            'received_facility_id' => 'The selected received facility id is invalid.',
        ]);
    });
});
```
