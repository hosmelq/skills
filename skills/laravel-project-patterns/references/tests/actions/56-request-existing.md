# Action Tests: Preserve or Approve a Request

Integration tests for access requests: approve immediately when configured, preserve an already approved request while invoking provisioning, and preserve pending request data. Provisioning is a mocked collaborator.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;
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

it('approves and provisions an instant request', function (): void {
    $team = Team::factory()->createOne([
        'assignment_mode' => AssignmentMode::Instant,
        'cabinets_enabled' => true,
    ]);
    $user = User::factory()->createOne();

    mock(ProvisionMemberCabinets::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Enrollment $enrollment): bool => (
            $enrollment->status === EnrollmentStatus::Approved
        ));

    $enrollment = resolve(RequestEnrollment::class)->handle(
        $team,
        $user,
    );

    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'status' => EnrollmentStatus::Approved,
    ]);
});

it('preserves and provisions an approved request', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $user = User::factory()->createOne();
    $member = Member::factory()->recycle($team)->createOne(['email' => $user->email]);
    $enrollment = Enrollment::factory()
        ->approved()
        ->recycle($member)
        ->createOne();

    mock(ProvisionMemberCabinets::class)
        ->shouldReceive('handle')
        ->once()
        ->withArgs(fn (Enrollment $actualEnrollment): bool => (
            $actualEnrollment->is($enrollment)
        ));

    $result = resolve(RequestEnrollment::class)->handle($team, $user);

    expect($result->is($enrollment))->toBeTrue();

    assertDatabaseCount(Enrollment::class, 1);
    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'status' => EnrollmentStatus::Approved,
    ]);
});

it('preserves a pending request', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $originalRequester = User::factory()->createOne();
    $user = User::factory()->createOne();
    $member = Member::factory()->recycle($team)->createOne(['email' => $user->email]);
    $requestedAt = now()->subDay();
    $enrollment = Enrollment::factory()
        ->recycle($member)
        ->createOne([
            'requested_at' => $requestedAt,
            'requested_by_user_id' => $originalRequester->id,
        ]);

    mock(ProvisionMemberCabinets::class)->shouldNotReceive('handle');

    $result = resolve(RequestEnrollment::class)->handle($team, $user);

    expect($result->is($enrollment))->toBeTrue();

    assertDatabaseCount(Enrollment::class, 1);
    assertDatabaseHas(Enrollment::class, [
        'id' => $enrollment->id,
        'requested_at' => $requestedAt,
        'requested_by_user_id' => $originalRequester->id,
        'status' => EnrollmentStatus::Pending,
    ]);
});
```
