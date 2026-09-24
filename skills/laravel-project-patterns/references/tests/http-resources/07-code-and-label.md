# HTTP Resource Tests: Code and Label Fields

Exact resource JSON with factory-derived code/label fields, a Sqid and formatted deactivation, creation and update timestamps.

```php
<?php

declare(strict_types=1);

use App\Models\Cabinet;

it('formats resource correctly', function (): void {
    $cabinet = Cabinet::factory()
        ->deactivated()
        ->createOne();

    $resource = json_decode($cabinet->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'code' => $cabinet->code,
        'created_at' => $cabinet->created_at->toJSON(),
        'deactivated_at' => $cabinet->deactivated_at->toJSON(),
        'id' => $cabinet->sqid,
        'label' => $cabinet->label,
        'updated_at' => $cabinet->updated_at->toJSON(),
    ]);
});
```
