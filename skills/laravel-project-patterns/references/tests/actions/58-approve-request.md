# Action Tests: Approve a Request

Integration tests for approval: refuse to approve a rejected request, persist approval and reviewer metadata for a pending request, and preserve an approved request while invoking mocked provisioning.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\mock;

use App\Actions\Cabinets\ApproveEnrollment;
use App\Actions\Cabinets\ProvisionMemberCabinets;
use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotReviewEnrollment;
use App\Models\Enrollment;
use App\Models\User;

it('prevents approving a rejected request', function (): void {
    $enrollment = Enrollment::factory()->rejected()->createOne();
    $reviewedByUser = User::factory()->createOne();

    mock(ProvisionMemberCabinets::class)->shouldNotReceive('handle');

    expect(fn () => resolve(ApproveEnrollment::class)->handle(
        $enrollment,
        $reviewedByUser,
    ))->toThrow(
        CannotReviewEnrollment::class,
        'A rejected enrollment cannot be approved.',
    );
});

it('approves a pending request', function (): void {
    $enrollment = Enrollment::factory()->createOne();
    $reviewedByUser = User::factory()->createOne();

    mock(ProvisionMemberCabinets::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Enrollment $enrollmentArgument): bool => (
            $enrollmentArgument->is($enrollment)
            && $enrollmentArgument->status === EnrollmentStatus::Approved
        ));

    $result = resolve(ApproveEnrollment::class)->handle(
        $enrollment,
        $reviewedByUser,
    );

    expect($result->is($enrollment))->toBeTrue();

    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'reviewed_at' => now(),
        'reviewed_by_user_id' => $reviewedByUser->id,
        'status' => EnrollmentStatus::Approved,
    ]);
});

it('preserves and provisions an approved request', function (): void {
    $originalReviewedByUser = User::factory()->createOne();
    $reviewedAt = now()->subDay();

    $enrollment = Enrollment::factory()
        ->approved()
        ->createOne([
            'reviewed_at' => $reviewedAt,
            'reviewed_by_user_id' => $originalReviewedByUser->id,
        ]);

    $reviewedByUser = User::factory()->createOne();

    mock(ProvisionMemberCabinets::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Enrollment $enrollmentArgument): bool => (
            $enrollmentArgument->is($enrollment)
        ));

    resolve(ApproveEnrollment::class)->handle(
        $enrollment,
        $reviewedByUser,
    );

    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'reviewed_at' => $reviewedAt,
        'reviewed_by_user_id' => $originalReviewedByUser->id,
        'status' => EnrollmentStatus::Approved,
    ]);
});
```
