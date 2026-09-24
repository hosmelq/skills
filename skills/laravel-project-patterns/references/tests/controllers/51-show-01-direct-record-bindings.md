# Show Tests: Direct Record Binding

Direct GET show route with tenant and record: a foreign tenant record or soft deleted target returns 404. Authenticate in the URL tenant and preserve the exact bound record fixtures.

## Direct Record

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Facility;
use App\Models\Team;

describe('show', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $unrelatedFacility = Facility::factory()->createOne();

        login(team: $team);

        $response = get(route('teams.facilities.show', [
            'team' => $team,
            'facility' => $unrelatedFacility,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $facility = Facility::factory()->trashed()->createOne();

        login(team: $facility->team);

        $response = get(route('teams.facilities.show', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertNotFound();
    });
});
```
