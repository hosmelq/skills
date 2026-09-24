# Action Tests: Request Guards and Identity Reuse

Integration tests for requesting access: reject a disabled setting or unverified email, persist a new owner and pending request, and reuse an owner through a case-insensitive email match.

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
use App\Exceptions\CannotRequestEnrollment;
use App\Models\Enrollment;
use App\Models\Member;
use App\Models\Team;
use App\Models\User;

it('rejects requests when assignment self-service is disabled', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => false]);
    $user = User::factory()->createOne();

    mock(ProvisionMemberCabinets::class)->shouldNotReceive('handle');

    expect(fn () => resolve(RequestEnrollment::class)->handle($team, $user))->toThrow(
        CannotRequestEnrollment::class,
        'Cabinet self-service is not enabled for this team.',
    );

    assertDatabaseCount(Member::class, 0);
    assertDatabaseCount(Enrollment::class, 0);
});

it('rejects requests without a verified email', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $user = User::factory()->unverified()->createOne();

    mock(ProvisionMemberCabinets::class)->shouldNotReceive('handle');

    expect(fn () => resolve(RequestEnrollment::class)->handle($team, $user))->toThrow(
        CannotRequestEnrollment::class,
        'A verified email is required to request cabinets.',
    );

    assertDatabaseCount(Member::class, 0);
    assertDatabaseCount(Enrollment::class, 0);
});

it('creates an owner and pending request', function (): void {
    $this->freezeTime();

    $team = Team::factory()->createOne([
        'assignment_mode' => AssignmentMode::RequiresApproval,
        'cabinets_enabled' => true,
    ]);
    $user = User::factory()->createOne([
        'email' => 'member@example.com',
        'first_name' => 'Alex',
        'last_name' => 'Example',
    ]);

    mock(ProvisionMemberCabinets::class)->shouldNotReceive('handle');

    $enrollment = resolve(RequestEnrollment::class)->handle($team, $user);

    expect($enrollment)->toBeInstanceOf(Enrollment::class);

    assertDatabaseHas(Member::class, [
        'email' => 'member@example.com',
        'first_name' => 'Alex',
        'last_name' => 'Example',
        'team_id' => $team->id,
    ]);
    assertDatabaseHas(Enrollment::class, [
        'member_id' => $enrollment->member_id,
        'id' => $enrollment->id,
        'team_id' => $team->id,
        'requested_by_user_id' => $user->id,
        'reviewed_by_user_id' => null,
        'requested_at' => now(),
        'reviewed_at' => null,
        'status' => EnrollmentStatus::Pending,
    ]);
});

it('reuses an owner with a case-insensitive email match', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $user = User::factory()->createOne(['email' => 'member@example.com']);
    $member = Member::factory()->recycle($team)->createOne([
        'email' => 'MEMBER@EXAMPLE.COM',
    ]);

    mock(ProvisionMemberCabinets::class)->shouldNotReceive('handle');

    $enrollment = resolve(RequestEnrollment::class)->handle($team, $user);

    expect($enrollment->member_id)->toBe($member->id);

    assertDatabaseCount(Member::class, 1);
    assertDatabaseCount(Enrollment::class, 1);
});
```
