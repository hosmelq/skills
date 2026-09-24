# Update Tests: Mapping Line Partial Measures

PATCH update: One partial child request checks a submitted description and Optional quantity. A separate complete three-row dataset compares transformed measurement input using stored and submitted values.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\WorkOrderLines\Inputs\UpdateWorkOrderLineInput;
use App\Actions\WorkOrderLines\UpdateWorkOrderLine;
use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\WorkOrderLine;
use Spatie\LaravelData\Optional;

describe('update', function (): void {
    it('maps submitted and omitted fields to the action', function (): void {
        $line = WorkOrderLine::factory()->createOne();

        login(team: $line->workOrder->team);

        mock(UpdateWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrderLine $lineArgument, UpdateWorkOrderLineInput $input): bool => $lineArgument
                    ->is($line)
                    && $input->description === 'Updated description'
                    && $input->quantity instanceof Optional,
            );

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]), [
            'description' => 'Updated description',
        ]);

        $response->assertRedirectToRoute('teams.work-orders.lines.show', [
            'team' => $line->workOrder->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ])->assertToast('Work order line updated');
    });

    it('maps partial measurements using stored values', function (
        array $attributes,
        array $data,
        array $expected,
    ): void {
        $line = WorkOrderLine::factory()->createOne($attributes);

        login(team: $line->team);

        mock(UpdateWorkOrderLine::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(
                fn (WorkOrderLine $lineArgument, UpdateWorkOrderLineInput $input): bool => $lineArgument
                    ->is($line)
                    && $input->transform() === $expected,
            );

        $response = patch(route('teams.work-orders.lines.update', [
            'team' => $line->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ]), $data);

        $response->assertRedirectToRoute('teams.work-orders.lines.show', [
            'team' => $line->team,
            'work_order' => $line->workOrder,
            'line' => $line,
        ])->assertToast('Work order line updated');
    })->with([
        'unit value' => [
            'attributes' => [
                'currency_code' => CurrencyCode::USD,
                'unit_value' => '10.00',
            ],
            'data' => ['unit_value' => '25.50'],
            'expected' => [
                'currency_code' => CurrencyCode::USD->value,
                'unit_value' => '25.50',
            ],
        ],
        'dimensions' => [
            'attributes' => [
                'dimension_unit' => LengthUnit::Inches,
                'height' => '1.0000',
                'length' => '2.0000',
                'width' => '3.0000',
            ],
            'data' => ['height' => '4.0000'],
            'expected' => [
                'dimension_unit' => LengthUnit::Inches->value,
                'height' => '4.0000',
                'length' => '2.0000',
                'width' => '3.0000',
            ],
        ],
        'weight' => [
            'attributes' => [
                'weight' => '1.0000',
                'weight_unit' => WeightUnit::Pounds,
            ],
            'data' => ['weight' => '2.5000'],
            'expected' => [
                'weight' => '2.5000',
                'weight_unit' => WeightUnit::Pounds->value,
            ],
        ],
    ]);
});
```
