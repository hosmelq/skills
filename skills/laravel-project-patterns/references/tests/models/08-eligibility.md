# Model Tests: Initial State Eligibility

In-memory initial-state predicate: an eligible active base state succeeds; a different base state, deactivation and soft deletion each fail.

```php
<?php

declare(strict_types=1);

use App\Enums\BaseStatus;
use App\Models\WorkOrderStatus;

it('detects states that can be initial', function (): void {
    $workOrderStatus = new WorkOrderStatus([
        'base_status' => BaseStatus::Open,
        'deactivated_at' => null,
    ]);

    expect($workOrderStatus->canBeInitial())->toBeTrue();
});

it('detects states that cannot be initial', function (array $attributes): void {
    $workOrderStatus = new WorkOrderStatus($attributes);

    expect($workOrderStatus->canBeInitial())->toBeFalse();
})->with([
    'non-open base status' => [[
        'base_status' => BaseStatus::Blocked,
        'deactivated_at' => null,
    ]],
    'deactivated' => [[
        'base_status' => BaseStatus::Open,
        'deactivated_at' => '2026-01-15 15:33:00',
    ]],
    'soft deleted' => [[
        'base_status' => BaseStatus::Open,
        'deactivated_at' => null,
        'deleted_at' => '2026-01-15 15:33:00',
    ]],
]);
```
