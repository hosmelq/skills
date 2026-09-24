# Action Tests: Provision Eligible Missing Records

Integration tests for bulk related-record creation: select active parents, create only missing assignments, handle an empty selection and recover an already-created record after a simulated uniqueness conflict. The conflict is synchronous, not a concurrent-worker test.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\mock;

use App\Actions\Cabinets\CreateCabinet;
use App\Actions\Cabinets\Inputs\CreateCabinetInput;
use App\Actions\Cabinets\ProvisionMemberCabinets;
use App\Exceptions\CannotCreateCabinet;
use App\Models\Cabinet;
use App\Models\Enrollment;
use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\Team;

it('creates assignments for active parent records', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $member = Member::factory()->recycle($team)->createOne();
    $enrollment = Enrollment::factory()
        ->approved()
        ->recycle($member)
        ->createOne();
    $firstServicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $secondServicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $deactivatedServicePlan = ServicePlan::factory()
        ->deactivated()
        ->recycle($team)
        ->createOne();
    $deletedServicePlan = ServicePlan::factory()
        ->trashed()
        ->recycle($team)
        ->createOne();

    resolve(ProvisionMemberCabinets::class)->handle($enrollment);

    assertDatabaseHas(Cabinet::class, [
        'member_id' => $member->id,
        'team_id' => $team->id,
        'service_plan_id' => $firstServicePlan->id,
    ]);
    assertDatabaseHas(Cabinet::class, [
        'member_id' => $member->id,
        'team_id' => $team->id,
        'service_plan_id' => $secondServicePlan->id,
    ]);
    assertDatabaseMissing(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $deactivatedServicePlan->id,
    ]);
    assertDatabaseMissing(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $deletedServicePlan->id,
    ]);
});

it('creates only missing assignments', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $member = Member::factory()->recycle($team)->createOne();
    $enrollment = Enrollment::factory()
        ->approved()
        ->recycle($member)
        ->createOne();
    $existingServicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $missingServicePlan = ServicePlan::factory()->recycle($team)->createOne();
    $existingCabinet = Cabinet::factory()->recycle([$member, $existingServicePlan])->createOne();

    resolve(ProvisionMemberCabinets::class)->handle($enrollment);

    assertDatabaseCount(Cabinet::class, 2);
    assertDatabaseHas(Cabinet::class, [
        'id' => $existingCabinet->id,
        'member_id' => $member->id,
        'service_plan_id' => $existingServicePlan->id,
    ]);
    assertDatabaseHas(Cabinet::class, [
        'member_id' => $member->id,
        'service_plan_id' => $missingServicePlan->id,
    ]);
});

it('reuses an assignment created during a simulated uniqueness conflict', function (): void {
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
        ->andReturnUsing(function (Member $member, CreateCabinetInput $input): void {
            $attributes = $input->transform();

            Cabinet::factory()->recycle($member)->createOne([
                'service_plan_id' => $attributes['service_plan_id'],
            ]);

            throw CannotCreateCabinet::becauseServicePlanIsAlreadyAssigned();
        });

    resolve(ProvisionMemberCabinets::class)->handle($enrollment);

    assertDatabaseCount(Cabinet::class, 1);
    assertDatabaseHas(Cabinet::class, [
        'member_id' => $member->id,
        'team_id' => $team->id,
        'service_plan_id' => $servicePlan->id,
    ]);
});

it('succeeds without active parent records', function (): void {
    $team = Team::factory()->createOne(['cabinets_enabled' => true]);
    $member = Member::factory()->recycle($team)->createOne();
    $enrollment = Enrollment::factory()
        ->approved()
        ->recycle($member)
        ->createOne();

    resolve(ProvisionMemberCabinets::class)->handle($enrollment);

    assertDatabaseCount(Cabinet::class, 0);
});
```
