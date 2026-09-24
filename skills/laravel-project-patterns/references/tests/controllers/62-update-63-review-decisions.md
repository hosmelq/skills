# Update Tests: Review Decisions

PATCH update: Approval and rejection delegate different actions with the original mocked invocation and success responses; do not claim persisted request transitions.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\patch;

use App\Actions\Cabinets\ApproveEnrollment;
use App\Actions\Cabinets\RejectEnrollment;
use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\User;

describe('update', function (): void {
    it('approves a request', function (): void {
        $enrollment = Enrollment::factory()->createOne();
        $reviewedByUser = login(team: $enrollment->team);

        mock(RejectEnrollment::class)->shouldNotReceive('handle');
        mock(ApproveEnrollment::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Enrollment $enrollmentArgument,
                User $reviewedByUserArgument,
            ): bool => (
                $enrollmentArgument->is($enrollment)
                && $reviewedByUserArgument->is($reviewedByUser)
            ));

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $enrollment->team,
        ]), [
            'status' => EnrollmentStatus::Approved->value,
        ]);

        $response->assertRedirect()
            ->assertToast('Cabinet request approved');
    });

    it('rejects a request', function (): void {
        $enrollment = Enrollment::factory()->createOne();
        $reviewedByUser = login(team: $enrollment->team);

        mock(ApproveEnrollment::class)->shouldNotReceive('handle');
        mock(RejectEnrollment::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(fn (
                Enrollment $enrollmentArgument,
                User $reviewedByUserArgument,
            ): bool => (
                $enrollmentArgument->is($enrollment)
                && $reviewedByUserArgument->is($reviewedByUser)
            ));

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $enrollment->team,
        ]), [
            'status' => EnrollmentStatus::Rejected->value,
        ]);

        $response->assertRedirect()
            ->assertToast('Cabinet request rejected');
    });
});
```
