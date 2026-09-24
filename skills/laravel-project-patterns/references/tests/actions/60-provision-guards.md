# Action Tests: Provisioning Guards and Failures

Integration tests for bulk related-record creation: no-op when disabled or unapproved, with both pending and rejected dataset rows; propagate a creation failure when no corresponding record exists.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\mock;

use App\Actions\Cabinets\CreateCabinet;
use App\Actions\Cabinets\ProvisionMemberCabinets;
use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotCreateCabinet;
use App\Models\Cabinet;
use App\Models\Enrollment;
use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\Team;

it('does not provision assignments when self-service is disabled', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => false]);
    $member = Member::factory()->recycle($team)->createOne();
    $enrollment = Enrollment::factory()
        ->approved()
        ->recycle($member)
        ->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();

    resolve(ProvisionMemberCabinets::class)->handle($enrollment);

    assertDatabaseMissing(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});

it('does not provision assignments for unapproved requests', function (EnrollmentStatus $status): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $member = Member::factory()->recycle($team)->createOne();
    $enrollment = Enrollment::factory()
        ->recycle($member)
        ->createOne(['status' => $status]);
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();

    resolve(ProvisionMemberCabinets::class)->handle($enrollment);

    assertDatabaseMissing(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $servicePlan->id,
    ]);
})->with([
    'pending' => EnrollmentStatus::Pending,
    'rejected' => EnrollmentStatus::Rejected,
]);

it('propagates assignment creation failures when no assignment exists', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $member = Member::factory()->recycle($team)->createOne();
    $enrollment = Enrollment::factory()
        ->approved()
        ->recycle($member)
        ->createOne();
    $servicePlan = ServicePlan::factory()->recycle($team)->createOne();

    mock(CreateCabinet::class)
        ->shouldReceive('handle')
        ->once()
        ->andThrow(CannotCreateCabinet::becauseServicePlanIsAlreadyAssigned());

    expect(fn () => resolve(ProvisionMemberCabinets::class)->handle($enrollment))->toThrow(
        CannotCreateCabinet::class,
        'A member can only have one cabinet per service plan.',
    );

    assertDatabaseMissing(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});
```
