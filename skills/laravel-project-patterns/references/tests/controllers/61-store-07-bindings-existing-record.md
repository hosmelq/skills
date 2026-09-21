# Store Tests: Bindings Existing Record

Pest POST store: Two bindings: existing record belongs to another tenant or is soft deleted.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Facility;
use App\Models\Team;

describe('store', function (): void {
    it('returns not found when the record belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();

        $unrelatedFacility = Facility::factory()->createOne();

        signIn(team: $team);

        $response = post(route('teams.facilities.deactivation.store', [
            'team' => $team,
            'facility' => $unrelatedFacility,
        ]));

        $response->assertNotFound();
    });

    it('returns not found when the record is soft deleted', function (): void {
        $facility = Facility::factory()->trashed()->createOne();

        signIn(team: $facility->team);

        $response = post(route('teams.facilities.deactivation.store', [
            'team' => $facility->team,
            'facility' => $facility,
        ]));

        $response->assertNotFound();
    });
});
```
