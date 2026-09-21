# Store Tests: Validation Transit Bounds

Pest POST store: Complete transit integer/range dataset with both ordering comparisons and required enum/name fields.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Models\Team;
use Illuminate\Support\Str;

describe('store', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $team = Team::factory()->createOne();

        signIn(team: $team);

        $response = post(route('teams.service-plans.store', [
            'team' => $team,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'enum' => [
            'data' => [
                'estimated_transit_time_unit' => 'invalid',
                'weight_unit' => 'invalid',
            ],
            'expected' => [
                'estimated_transit_time_unit' => 'The selected estimated transit time unit is invalid.',
                'weight_unit' => 'The selected weight unit is invalid.',
            ],
        ],
        'gte:0' => [
            'data' => [
                'maximum_estimated_transit_time' => -1,
                'minimum_estimated_transit_time' => -2,
            ],
            'expected' => [
                'maximum_estimated_transit_time' => 'The maximum estimated transit time field must be greater than or equal to 0.',
                'minimum_estimated_transit_time' => 'The minimum estimated transit time field must be greater than or equal to 0.',
            ],
        ],
        'gte:minimum_estimated_transit_time' => [
            'data' => [
                'maximum_estimated_transit_time' => 4,
                'minimum_estimated_transit_time' => 5,
            ],
            'expected' => [
                'maximum_estimated_transit_time' => 'The maximum estimated transit time field must be greater than or equal to 5.',
            ],
        ],
        'integer' => [
            'data' => [
                'maximum_estimated_transit_time' => 'a',
                'minimum_estimated_transit_time' => 'a',
            ],
            'expected' => [
                'maximum_estimated_transit_time' => 'The maximum estimated transit time field must be an integer.',
                'minimum_estimated_transit_time' => 'The minimum estimated transit time field must be an integer.',
            ],
        ],
        'lte:maximum_estimated_transit_time' => [
            'data' => [
                'maximum_estimated_transit_time' => 4,
                'minimum_estimated_transit_time' => 5,
            ],
            'expected' => [
                'minimum_estimated_transit_time' => 'The minimum estimated transit time field must be less than or equal to 4.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'name' => Str::repeat('a', 256),
            ],
            'expected' => [
                'name' => 'The name field must not be greater than 255 characters.',
            ],
        ],
        'max:2000 (string)' => [
            'data' => [
                'description' => Str::repeat('a', 2001),
            ],
            'expected' => [
                'description' => 'The description field must not be greater than 2000 characters.',
            ],
        ],
        'required' => [
            'data' => [],
            'expected' => [
                'estimated_transit_time_unit' => 'The estimated transit time unit field is required.',
                'minimum_estimated_transit_time' => 'The minimum estimated transit time field is required.',
                'name' => 'The name field is required.',
                'weight_unit' => 'The weight unit field is required.',
            ],
        ],
    ]);
});
```
