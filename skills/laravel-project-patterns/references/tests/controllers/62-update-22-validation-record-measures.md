# Update Tests: Validation Record Measures

Pest PATCH update: Whole optional record reference/text/date/dimensions/weight dataset, without turning omitted partial-update fields into required creation fields.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\WorkOrder;
use Illuminate\Support\Str;

describe('update', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $workOrder = WorkOrder::factory()->createOne();

        signIn(team: $workOrder->team);

        $response = patch(route('teams.work-orders.update', [
            'team' => $workOrder->team,
            'work_order' => $workOrder,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'date' => [
            'data' => [
                'received_at' => 'not-a-date',
            ],
            'expected' => [
                'received_at' => 'The received at field must be a valid date.',
            ],
        ],
        'decimal:0,4' => [
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
            'data' => [
                'dimension_unit' => 'invalid',
                'weight_unit' => 'invalid',
            ],
            'expected' => [
                'dimension_unit' => 'The selected dimension unit is invalid.',
                'weight_unit' => 'The selected weight unit is invalid.',
            ],
        ],
        'gt:0' => [
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
        'max:2000 (string)' => [
            'data' => [
                'note' => Str::repeat('a', 2001),
            ],
            'expected' => [
                'note' => 'The note field must not be greater than 2000 characters.',
            ],
        ],
        'max:255 (string)' => [
            'data' => [
                'external_carrier_name' => Str::repeat('a', 256),
                'external_tracking_number' => Str::repeat('a', 256),
                'reference' => Str::repeat('a', 256),
            ],
            'expected' => [
                'external_carrier_name' =>
                    'The external carrier name field must not be greater than 255 characters.',
                'external_tracking_number' =>
                    'The external tracking number field must not be greater than 255 characters.',
                'reference' => 'The reference field must not be greater than 255 characters.',
            ],
        ],
        'max:5000 (string)' => [
            'data' => [
                'received_label_text' => Str::repeat('a', 5001),
            ],
            'expected' => [
                'received_label_text' =>
                    'The received label text field must not be greater than 5000 characters.',
            ],
        ],
        'max:9999.9999' => [
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
        'required' => [
            'data' => [
                'received_at' => null,
            ],
            'expected' => [
                'received_at' => 'The received at field is required.',
            ],
        ],
        'string' => [
            'data' => [
                'external_carrier_name' => 123,
                'external_tracking_number' => 123,
                'note' => 123,
                'received_label_text' => 123,
                'reference' => 123,
            ],
            'expected' => [
                'external_carrier_name' => 'The external carrier name field must be a string.',
                'external_tracking_number' =>
                    'The external tracking number field must be a string.',
                'note' => 'The note field must be a string.',
                'received_label_text' => 'The received label text field must be a string.',
                'reference' => 'The reference field must be a string.',
            ],
        ],
    ]);
});
```
