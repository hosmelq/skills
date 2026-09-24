# Store Tests: Validation Rounding Rule

Pest POST store: Complete rule dataset: decimal precision, country/currency/mode enums, positive increment, nonnegative minimum and conditional requirement.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\post;

use App\Enums\RoundingMode;
use App\Models\ServicePlan;
use Illuminate\Support\Str;

describe('store', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $servicePlan = ServicePlan::factory()->createOne();

        login(team: $servicePlan->team);

        $response = post(route('teams.service-plans.plan-rules.store', [
            'team' => $servicePlan->team,
            'service_plan' => $servicePlan,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'decimal:0,4' => [
            'data' => [
                'minimum_chargeable_weight' => '12.12345',
                'rounding_increment' => '1.12345',
                'rounding_mode' => RoundingMode::Up->value,
            ],
            'expected' => [
                'minimum_chargeable_weight' => 'The minimum chargeable weight field must have 0-4 decimal places.',
                'rounding_increment' => 'The rounding increment field must have 0-4 decimal places.',
            ],
        ],
        'enum' => [
            'data' => [
                'country_code' => 'invalid',
                'currency_code' => 'invalid',
                'rounding_mode' => 'invalid',
            ],
            'expected' => [
                'country_code' => 'The selected country code is invalid.',
                'currency_code' => 'The selected currency code is invalid.',
                'rounding_mode' => 'The selected rounding mode is invalid.',
            ],
        ],
        'gt:0' => [
            'data' => [
                'rounding_increment' => 0,
                'rounding_mode' => RoundingMode::Up->value,
            ],
            'expected' => [
                'rounding_increment' => 'The rounding increment field must be greater than 0.',
            ],
        ],
        'gte:0' => [
            'data' => [
                'minimum_chargeable_weight' => -1,
            ],
            'expected' => [
                'minimum_chargeable_weight' => 'The minimum chargeable weight field must be greater than or equal to 0.',
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
        'required' => [
            'data' => [],
            'expected' => [
                'country_code' => 'The country code field is required.',
                'currency_code' => 'The currency code field is required.',
                'minimum_chargeable_weight' => 'The minimum chargeable weight field is required.',
                'name' => 'The name field is required.',
                'rounding_mode' => 'The rounding mode field is required.',
            ],
        ],
        'required_if' => [
            'data' => [
                'rounding_mode' => RoundingMode::Up->value,
            ],
            'expected' => [
                'rounding_increment' => 'The rounding increment field is required.',
            ],
        ],
    ]);
});
```
