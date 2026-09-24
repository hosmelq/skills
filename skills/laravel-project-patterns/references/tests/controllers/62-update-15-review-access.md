# Update Tests: Review Access

PATCH update: Request-decision PATCH access keeps the status payload, direct tenant binding and both ordinary foreign ownership and conflicting stored tenant fixtures.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Member;
use App\Models\Team;

describe('update', function (): void {
    it('requires authentication', function (): void {
        $enrollment = Enrollment::factory()->createOne();

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $enrollment->team,
        ]), [
            'status' => EnrollmentStatus::Approved->value,
        ]);

        $response->assertRedirectToRoute('login');
    });

    it('prevents updating from an unrelated tenant', function (): void {
        $enrollment = Enrollment::factory()->createOne();

        login();

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $enrollment->team,
        ]), [
            'status' => EnrollmentStatus::Approved->value,
        ]);

        $response->assertForbidden();
    });

    it('returns not found when the record belongs to another tenant', function (): void {
        $team = Team::factory()->createOne();
        $enrollment = Enrollment::factory()->createOne();

        login(team: $team);

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $team,
        ]), [
            'status' => EnrollmentStatus::Approved->value,
        ]);

        $response->assertNotFound();
    });

    it('returns not found when the record tenant does not match its parent tenant', function (): void {
        $member = Member::factory()->createOne();
        $enrollment = Enrollment::factory()
            ->for($member)
            ->for(Team::factory())
            ->createOne();

        login(team: $enrollment->team);

        $response = patch(route('teams.enrollments.status.update', [
            'enrollment' => $enrollment,
            'team' => $enrollment->team,
        ]), [
            'status' => EnrollmentStatus::Approved->value,
        ]);

        $response->assertNotFound();
    });
});
```
