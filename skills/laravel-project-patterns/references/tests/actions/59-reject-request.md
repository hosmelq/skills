# Action Tests: Reject a Request

Integration tests for rejection: reject an already approved request, persist rejection and reviewer metadata for a pending request, and preserve an already rejected request.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Cabinets\RejectEnrollment;
use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotReviewEnrollment;
use App\Models\Enrollment;
use App\Models\User;

it('prevents rejecting an approved request', function (): void {
    $enrollment = Enrollment::factory()->approved()->createOne();
    $reviewedByUser = User::factory()->createOne();

    expect(fn () => resolve(RejectEnrollment::class)->handle(
        $enrollment,
        $reviewedByUser,
    ))->toThrow(
        CannotReviewEnrollment::class,
        'An approved enrollment cannot be rejected.',
    );
});

it('rejects a pending request', function (): void {
    $enrollment = Enrollment::factory()->createOne();
    $reviewedByUser = User::factory()->createOne();

    $result = resolve(RejectEnrollment::class)->handle($enrollment, $reviewedByUser);

    expect($result->is($enrollment))->toBeTrue();

    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'reviewed_at' => now(),
        'reviewed_by_user_id' => $reviewedByUser->id,
        'status' => EnrollmentStatus::Rejected,
    ]);
});

it('preserves a rejected request', function (): void {
    $originalReviewedByUser = User::factory()->createOne();
    $reviewedAt = now()->subDay();

    $enrollment = Enrollment::factory()
        ->rejected($originalReviewedByUser)
        ->createOne(['reviewed_at' => $reviewedAt]);

    $reviewedByUser = User::factory()->createOne();

    resolve(RejectEnrollment::class)->handle($enrollment, $reviewedByUser);

    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'reviewed_at' => $reviewedAt,
        'reviewed_by_user_id' => $originalReviewedByUser->id,
        'status' => EnrollmentStatus::Rejected,
    ]);
});
```
