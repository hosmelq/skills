# Action Tests: Deactivate a Record

Integration test for a deactivation action that persists the current timestamp on an active record. The test bootstrap freezes time so the database expectation uses the same instant.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Facilities\DeactivateFacility;
use App\Models\Facility;

it('deactivates a record', function (): void {
    $facility = Facility::factory()->createOne();

    resolve(DeactivateFacility::class)->handle($facility);

    assertDatabaseHas(Facility::class, [
        'id' => $facility->id,
        'deactivated_at' => now(),
        'deleted_at' => null,
    ]);
});
```
