# Model Tests: Prunable Records

Database-backed model pruning query: include expired unused records and records used at the month boundary; exclude active and recently used records. This checks selection, not deletion.

Use the project’s database-capable suite even if a reference project placed this contract under Unit. Freeze time before factory creation; inspect the `expired()` and `used()` states.

```php
<?php

declare(strict_types=1);

use App\Models\OneTimePassword;

it('selects expired or month-old used records for pruning', function (): void {
    $active = OneTimePassword::factory()->createOne();
    $expiredUnused = OneTimePassword::factory()->expired()->createOne();
    $usedOlder = OneTimePassword::factory()->used()->createOne([
        'used_at' => now()->subMonth(),
    ]);
    $usedRecent = OneTimePassword::factory()->used()->createOne();

    $prunableIds = new OneTimePassword()->prunable()->pluck('id');

    expect($prunableIds)
        ->toContain($expiredUnused->id)
        ->toContain($usedOlder->id)
        ->not->toContain($active->id)
        ->not->toContain($usedRecent->id);
});
```
