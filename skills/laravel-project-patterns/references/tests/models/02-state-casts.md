# Model Tests: State Enum and Independent Boolean Casts

In-memory state casts include a base enum, two independent true flags from strings, and immutable creation, update, deactivation and deletion timestamps.

```php
<?php

declare(strict_types=1);

use App\Enums\BaseStatus;
use App\Models\WorkOrderStatus;
use Carbon\CarbonImmutable;

it('correctly casts attributes', function (): void {
    $workOrderStatus = new WorkOrderStatus([
        'base_status' => 'open',
        'created_at' => '2026-01-15 15:33:00',
        'deactivated_at' => '2026-01-15 15:33:00',
        'deleted_at' => '2026-01-15 15:33:00',
        'is_initial' => '1',
        'is_member_visible' => '1',
        'updated_at' => '2026-01-15 15:33:00',
    ]);

    expect($workOrderStatus)
        ->base_status->toBeInstanceOf(BaseStatus::class)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->deactivated_at->toBeInstanceOf(CarbonImmutable::class)
        ->deleted_at->toBeInstanceOf(CarbonImmutable::class)
        ->is_initial->toBeTrue()
        ->is_member_visible->toBeTrue()
        ->updated_at->toBeInstanceOf(CarbonImmutable::class);
});
```
