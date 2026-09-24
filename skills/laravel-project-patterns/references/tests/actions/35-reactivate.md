# Action Tests: Reactivate a Record

Integration test for a reactivation action that clears the persisted deactivation timestamp of an inactive record.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Facilities\ReactivateFacility;
use App\Models\Facility;

it('reactivates a record', function (): void {
    $facility = Facility::factory()->deactivated()->createOne();

    resolve(ReactivateFacility::class)->handle($facility);

    assertDatabaseHas(Facility::class, [
        'id' => $facility->id,
        'deactivated_at' => null,
    ]);
});
```
