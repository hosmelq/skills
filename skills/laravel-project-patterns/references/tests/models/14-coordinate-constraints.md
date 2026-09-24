# Model Tests: Coordinate Constraints

Database checks reject either missing coordinate and out-of-range latitude or longitude. Both nullable-pair dataset rows and the named check constraints are explicit.

The factory supplies a valid coordinate pair. Run against the project’s configured database engine; do not replace named constraint checks with HTTP validation.

```php
<?php

declare(strict_types=1);

use App\Models\Facility;
use Illuminate\Database\QueryException;

it('enforces coordinate pairs at the database level', function (array $coordinates): void {
    expect(fn () => Facility::factory()->createOne($coordinates))
        ->toThrow(QueryException::class, 'facilities_coordinate_pair_check');
})->with([
    'missing latitude' => [[
        'latitude' => null,
        'longitude' => -118.24,
    ]],
    'missing longitude' => [[
        'latitude' => 34.05,
        'longitude' => null,
    ]],
]);

it('enforces the latitude geographic range at the database level', function (): void {
    expect(fn () => Facility::factory()->createOne([
        'latitude' => 91,
    ]))->toThrow(QueryException::class, 'facilities_latitude_range_check');
});

it('enforces the longitude geographic range at the database level', function (): void {
    expect(fn () => Facility::factory()->createOne([
        'longitude' => 181,
    ]))->toThrow(QueryException::class, 'facilities_longitude_range_check');
});
```
