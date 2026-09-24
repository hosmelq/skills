# Update Tests: Validation Line Measures

Pest PATCH update: Complete partial-update dataset for precision, quantity, currency/value, dimensions and weight. Each row supplies stored factory attributes and a separate PATCH payload; preserve both.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\WorkOrderLine;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (
        array $attributes,
        array $data,
        array $expected,
    ): void {
        $line = WorkOrderLine::factory()->createOne($attributes);

        login(team: $line->workOrder->team);

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'decimal:0,2' => [
            'attributes' => [],
            'data' => [
                'currency_code' => CurrencyCode::USD->value,
                'unit_value' => '1.001',
            ],
            'expected' => [
                'unit_value' =>
                    'The unit value field must have 0-2 decimal places.',
            ],
        ],
        'decimal:0,4' => [
            'attributes' => [],
            'data' => [
                'height' => '1.00001',
                'length' => '1.00001',
                'weight' => '1.00001',
                'width' => '1.00001',
            ],
            'expected' => [
                'height' => 'The height field must have 0-4 decimal places.',
                'length' => 'The length field must have 0-4 decimal places.',
                'weight' => 'The weight field must have 0-4 decimal places.',
                'width' => 'The width field must have 0-4 decimal places.',
            ],
        ],
        'enum' => [
            'attributes' => [],
            'data' => [
                'currency_code' => 'invalid',
                'dimension_unit' => 'invalid',
                'weight_unit' => 'invalid',
            ],
            'expected' => [
                'currency_code' => 'The selected currency code is invalid.',
                'dimension_unit' => 'The selected dimension unit is invalid.',
                'weight_unit' => 'The selected weight unit is invalid.',
            ],
        ],
        'gt:0' => [
            'attributes' => [],
            'data' => [
                'height' => '0',
                'length' => '0',
                'weight' => '0',
                'width' => '0',
            ],
            'expected' => [
                'height' => 'The height field must be greater than 0.',
                'length' => 'The length field must be greater than 0.',
                'weight' => 'The weight field must be greater than 0.',
                'width' => 'The width field must be greater than 0.',
            ],
        ],
        'gte:0' => [
            'attributes' => [],
            'data' => ['unit_value' => '-0.01'],
            'expected' => [
                'unit_value' =>
                    'The unit value field must be greater than or equal to 0.',
            ],
        ],
        'integer' => [
            'attributes' => [],
            'data' => ['quantity' => 1.5],
            'expected' => ['quantity' => 'The quantity field must be an integer.'],
        ],
        'max:2000 (string)' => [
            'attributes' => [],
            'data' => ['description' => Str::repeat('a', 2001)],
            'expected' => [
                'description' =>
                    'The description field must not be greater than 2000 characters.',
            ],
        ],
        'max:2147483647' => [
            'attributes' => [],
            'data' => ['quantity' => 2147483648],
            'expected' => [
                'quantity' => 'The quantity field must not be greater than 2147483647.',
            ],
        ],
        'max:9999.9999' => [
            'attributes' => [],
            'data' => [
                'height' => '10000',
                'length' => '10000',
                'weight' => '10000',
                'width' => '10000',
            ],
            'expected' => [
                'height' => 'The height field must not be greater than 9999.9999.',
                'length' => 'The length field must not be greater than 9999.9999.',
                'weight' => 'The weight field must not be greater than 9999.9999.',
                'width' => 'The width field must not be greater than 9999.9999.',
            ],
        ],
        'max:999999.99' => [
            'attributes' => [],
            'data' => ['unit_value' => '1000000'],
            'expected' => [
                'unit_value' =>
                    'The unit value field must not be greater than 999999.99.',
            ],
        ],
        'min:1' => [
            'attributes' => [],
            'data' => ['quantity' => 0],
            'expected' => ['quantity' => 'The quantity field must be at least 1.'],
        ],
        'required' => [
            'attributes' => [],
            'data' => [
                'description' => null,
                'quantity' => null,
            ],
            'expected' => [
                'description' => 'The description field is required.',
                'quantity' => 'The quantity field is required.',
            ],
        ],
        'required_with:currency_code' => [
            'attributes' => [
                'currency_code' => CurrencyCode::USD,
                'unit_value' => '10.00',
            ],
            'data' => ['unit_value' => null],
            'expected' => [
                'unit_value' =>
                    'The unit value field is required when currency code is present.',
            ],
        ],
        'required_with:unit_value' => [
            'attributes' => [
                'currency_code' => CurrencyCode::USD,
                'unit_value' => '10.00',
            ],
            'data' => ['currency_code' => null],
            'expected' => [
                'currency_code' =>
                    'The currency code field is required when unit value is present.',
            ],
        ],
        'required_with:dimension_unit,height,length' => [
            'attributes' => [
                'dimension_unit' => LengthUnit::Inches,
                'height' => '1.0000',
                'length' => '1.0000',
                'width' => '1.0000',
            ],
            'data' => ['width' => null],
            'expected' => [
                'width' =>
                    'The width field is required when dimension unit / height / length is present.',
            ],
        ],
        'required_with:dimension_unit,height,width' => [
            'attributes' => [
                'dimension_unit' => LengthUnit::Inches,
                'height' => '1.0000',
                'length' => '1.0000',
                'width' => '1.0000',
            ],
            'data' => ['length' => null],
            'expected' => [
                'length' =>
                    'The length field is required when dimension unit / height / width is present.',
            ],
        ],
        'required_with:dimension_unit,length,width' => [
            'attributes' => [
                'dimension_unit' => LengthUnit::Inches,
                'height' => '1.0000',
                'length' => '1.0000',
                'width' => '1.0000',
            ],
            'data' => ['height' => null],
            'expected' => [
                'height' =>
                    'The height field is required when dimension unit / length / width is present.',
            ],
        ],
        'required_with:height,length,width' => [
            'attributes' => [
                'dimension_unit' => LengthUnit::Inches,
                'height' => '1.0000',
                'length' => '1.0000',
                'width' => '1.0000',
            ],
            'data' => ['dimension_unit' => null],
            'expected' => [
                'dimension_unit' =>
                    'The dimension unit field is required when height / length / width is present.',
            ],
        ],
        'required_with:weight' => [
            'attributes' => [
                'weight' => '1.0000',
                'weight_unit' => WeightUnit::Pounds,
            ],
            'data' => ['weight_unit' => null],
            'expected' => [
                'weight_unit' => 'The weight unit field is required when weight is present.',
            ],
        ],
        'required_with:weight_unit' => [
            'attributes' => [
                'weight' => '1.0000',
                'weight_unit' => WeightUnit::Pounds,
            ],
            'data' => ['weight' => null],
            'expected' => [
                'weight' => 'The weight field is required when weight unit is present.',
            ],
        ],
        'string' => [
            'attributes' => [],
            'data' => ['description' => 123],
            'expected' => ['description' => 'The description field must be a string.'],
        ],
    ]);
});
```
