# Model Tests: Range and Rate Casts

In-memory casts preserve both range bounds as four-place strings and a fractional rate as a two-place string, with immutable timestamps.

```php
<?php

declare(strict_types=1);

use App\Models\PlanRate;
use Carbon\CarbonImmutable;

it('correctly casts attributes', function (): void {
    $rate = new PlanRate([
        'created_at' => '2026-01-15 15:33:00',
        'deleted_at' => '2026-01-15 15:33:00',
        'maximum_weight' => 10,
        'minimum_weight' => 1,
        'rate' => 1.2,
        'updated_at' => '2026-01-15 15:33:00',
    ]);

    expect($rate)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->deleted_at->toBeInstanceOf(CarbonImmutable::class)
        ->maximum_weight->toBe('10.0000')
        ->minimum_weight->toBe('1.0000')
        ->rate->toBe('1.20')
        ->updated_at->toBeInstanceOf(CarbonImmutable::class);
});
```
