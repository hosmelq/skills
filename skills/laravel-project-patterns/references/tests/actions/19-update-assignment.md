# Action Tests: Update Assignment Fields

Integration tests for updating a nullable label through an input object: return the same record, preserve an omitted label and clear it with explicit null. The related parent key remains unchanged.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Cabinets\Inputs\UpdateCabinetInput;
use App\Actions\Cabinets\UpdateCabinet;
use App\Models\Cabinet;

it('updates a record', function (): void {
    $cabinet = Cabinet::factory()->createOne([
        'label' => 'Standard',
    ]);

    $updatedCabinet = resolve(UpdateCabinet::class)->handle(
        $cabinet,
        UpdateCabinetInput::from([
            'label' => 'Seattle',
        ]),
    );

    expect($updatedCabinet->is($cabinet))->toBeTrue();

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'service_plan_id' => $cabinet->service_plan_id,
        'label' => 'Seattle',
    ]);
});

it('updates only provided fields', function (): void {
    $cabinet = Cabinet::factory()->createOne([
        'label' => 'Standard',
    ]);

    resolve(UpdateCabinet::class)->handle(
        $cabinet,
        UpdateCabinetInput::from([]),
    );

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'label' => 'Standard',
    ]);
});

it('clears nullable fields', function (): void {
    $cabinet = Cabinet::factory()->createOne([
        'label' => 'Standard',
    ]);

    resolve(UpdateCabinet::class)->handle(
        $cabinet,
        UpdateCabinetInput::from([
            'label' => null,
        ]),
    );

    assertDatabaseHas(Cabinet::class, [
        'id' => $cabinet->id,
        'label' => null,
    ]);
});
```
