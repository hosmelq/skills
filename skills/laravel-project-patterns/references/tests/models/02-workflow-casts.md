# Model Tests: Workflow Enum and Timestamp Casts

In-memory casts preserve a workflow status enum and distinct immutable requested, reviewed, created and updated timestamps.

```php
<?php

declare(strict_types=1);

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use Carbon\CarbonImmutable;

it('correctly casts attributes', function (): void {
    $enrollment = new Enrollment([
        'created_at' => '2026-01-15 14:00:00',
        'requested_at' => '2026-01-15 14:00:00',
        'reviewed_at' => '2026-01-15 15:00:00',
        'status' => 'approved',
        'updated_at' => '2026-01-15 15:00:00',
    ]);

    expect($enrollment)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->requested_at->toBeInstanceOf(CarbonImmutable::class)
        ->reviewed_at->toBeInstanceOf(CarbonImmutable::class)
        ->status->toBeInstanceOf(EnrollmentStatus::class)
        ->updated_at->toBeInstanceOf(CarbonImmutable::class);
});
```
