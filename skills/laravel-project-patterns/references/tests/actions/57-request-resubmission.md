# Action Tests: Resubmit a Rejected Request

Integration tests for resubmission of a rejected request: reset review metadata and either return to pending or approve and invoke provisioning according to the inspected setting.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\mock;

use App\Actions\Cabinets\ProvisionMemberCabinets;
use App\Actions\Cabinets\RequestEnrollment;
use App\Enums\AssignmentMode;
use App\Enums\EnrollmentStatus;
use App\Models\Enrollment;
use App\Models\Member;
use App\Models\Team;
use App\Models\User;

it('resubmits a rejected request for approval', function (): void {
    $this->freezeTime();

    $team = Team::factory()->createOne([
        'assignment_mode' => AssignmentMode::RequiresApproval,
        'cabinets_enabled' => true,
    ]);
    $reviewer = User::factory()->createOne();
    $user = User::factory()->createOne();
    $member = Member::factory()->recycle($team)->createOne(['email' => $user->email]);
    $enrollment = Enrollment::factory()
        ->recycle($member)
        ->rejected($reviewer)
        ->createOne(['requested_at' => now()->subDay()]);

    mock(ProvisionMemberCabinets::class)->shouldNotReceive('handle');

    resolve(RequestEnrollment::class)->handle($team, $user);

    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'requested_by_user_id' => $user->id,
        'reviewed_by_user_id' => null,
        'requested_at' => now(),
        'reviewed_at' => null,
        'status' => EnrollmentStatus::Pending,
    ]);
});

it('resubmits and provisions a rejected instant request', function (): void {
    $team = Team::factory()->createOne([
        'assignment_mode' => AssignmentMode::Instant,
        'cabinets_enabled' => true,
    ]);
    $user = User::factory()->createOne();
    $member = Member::factory()->recycle($team)->createOne(['email' => $user->email]);
    $enrollment = Enrollment::factory()
        ->recycle($member)
        ->rejected()
        ->createOne();

    mock(ProvisionMemberCabinets::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Enrollment $enrollment): bool => (
            $enrollment->status === EnrollmentStatus::Approved
        ));

    resolve(RequestEnrollment::class)->handle($team, $user);

    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'reviewed_at' => null,
        'reviewed_by_user_id' => null,
        'status' => EnrollmentStatus::Approved,
    ]);
});
```
