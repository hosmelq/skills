# Action Tests: Soft Delete a Record

Integration test for an action that soft deletes an eligible record. assertSoftDeleted checks retained-row deletion state; it does not assert physical removal.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertSoftDeleted;

use App\Actions\Facilities\DeleteFacility;
use App\Models\Facility;

it('soft deletes a record', function (): void {
    $facility = Facility::factory()->createOne();

    resolve(DeleteFacility::class)->handle($facility);

    assertSoftDeleted($facility);
});
```
