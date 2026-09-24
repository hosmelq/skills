# Update Tests: Review Status Validation

PATCH update: Complete required/enum status dataset for a decision request.

## Validates fields

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;

describe('update', function (): void {
    it('validates fields', function (array $data, array $expected): void {
        $enrollment = Enrollment::factory()->createOne();

        login(team: $enrollment->team);

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $enrollment->team,
        ]), $data);

        $response->assertRedirectBackWithErrors($expected);
    })->with([
        'enum' => [
            'data' => [
                'status' => EnrollmentStatus::Pending->value,
            ],
            'expected' => [
                'status' => 'The selected status is invalid.',
            ],
        ],
        'required' => [
            'data' => [],
            'expected' => [
                'status' => 'The status field is required.',
            ],
        ],
    ]);
});
```
