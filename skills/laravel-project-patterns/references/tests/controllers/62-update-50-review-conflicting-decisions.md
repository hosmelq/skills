# Update Tests: Review Conflicting Decisions

Pest PATCH update: Opposite review decisions map their respective mocked action exceptions to validation. Keep the default request fixture: the conflict comes from the mock, not an approved/rejected factory state.

The action is mocked to throw. These assertions verify controller translation, not the underlying business guard.

## Complete block

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Cabinets\ApproveEnrollment;
use App\Actions\Cabinets\RejectEnrollment;
use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotReviewEnrollment;
use App\Models\Enrollment;
use App\Models\User;

describe('update', function (): void {
    it('maps a rejected request approval conflict to validation', function (): void {
        $enrollment = Enrollment::factory()->createOne();
        $reviewedByUser = login(team: $enrollment->team);

        mock(ApproveEnrollment::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Enrollment $enrollmentArgument,
                User $reviewedByUserArgument,
            ): bool => (
                $enrollmentArgument->is($enrollment)
                && $reviewedByUserArgument->is($reviewedByUser)
            ))
            ->andThrow(CannotReviewEnrollment::becauseItWasRejected());

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $enrollment->team,
        ]), [
            'status' => EnrollmentStatus::Approved->value,
        ]);

        $response->assertRedirectBackWithErrors([
            'enrollment' => 'This cabinet request has already been reviewed with a different decision.',
        ]);
    });

    it('maps an approved request rejection conflict to validation', function (): void {
        $enrollment = Enrollment::factory()->createOne();
        $reviewedByUser = login(team: $enrollment->team);

        mock(RejectEnrollment::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Enrollment $enrollmentArgument,
                User $reviewedByUserArgument,
            ): bool => (
                $enrollmentArgument->is($enrollment)
                && $reviewedByUserArgument->is($reviewedByUser)
            ))
            ->andThrow(CannotReviewEnrollment::becauseItWasApproved());

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $enrollment->team,
        ]), [
            'status' => EnrollmentStatus::Rejected->value,
        ]);

        $response->assertRedirectBackWithErrors([
            'enrollment' => 'This cabinet request has already been reviewed with a different decision.',
        ]);
    });
});
```
