# Create Tests: Unavailable Options

GET create-page exclusions for each option's own inactive, deleted or noninitial state. Includes complete multi-list assertions and a nested-form variant with no available options; keep this after relation-state and ownership exclusions.

A foreign or unavailable fixture without an exclusion assertion is not coverage. Keep the same canonical name for equivalent availability cases across controllers.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\BaseStatus;
use App\Models\Enrollment;
use App\Models\Facility;
use App\Models\Member;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page without unavailable options', function (): void {
        $team = Team::factory()->createOne();
        $deletedMember = Member::factory()->trashed()->recycle($team)->createOne();
        $deactivatedFacility = Facility::factory()->deactivated()->recycle($team)->createOne();
        $deletedFacility = Facility::factory()->trashed()->recycle($team)->createOne();
        $deactivatedEnrollment = Enrollment::factory()->deactivated()->recycle($team)->createOne();
        $deletedEnrollment = Enrollment::factory()->trashed()->recycle($team)->createOne();
        $deactivatedServicePlan = ServicePlan::factory()->deactivated()->recycle($team)->createOne();
        $deletedServicePlan = ServicePlan::factory()->trashed()->recycle($team)->createOne();
        $deactivatedStatus = WorkOrderStatus::factory()->deactivated()->recycle($team)->createOne();
        $deletedStatus = WorkOrderStatus::factory()->trashed()->recycle($team)->createOne();
        $nonInitialStatus = WorkOrderStatus::factory()
            ->withBaseStatus(BaseStatus::InProgress)
            ->recycle($team)
            ->createOne();

        login(team: $team);

        $response = get(route('teams.work-orders.create', $team));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (
                $deactivatedFacility,
                $deactivatedEnrollment,
                $deactivatedServicePlan,
                $deactivatedStatus,
                $deletedMember,
                $deletedFacility,
                $deletedEnrollment,
                $deletedServicePlan,
                $deletedStatus,
                $nonInitialStatus,
            ): void {
                $page->where(
                    'members',
                    fn (Collection $options): bool => ! $options
                        ->pluck('id')
                        ->contains($deletedMember->sqid),
                )->where(
                    'facilities',
                    fn (Collection $options): bool => ! $options->pluck('id')->intersect([
                        $deactivatedFacility->sqid,
                        $deletedFacility->sqid,
                    ])->isNotEmpty(),
                )->where(
                    'enrollments',
                    fn (Collection $options): bool => ! $options->pluck('id')->intersect([
                        $deactivatedEnrollment->sqid,
                        $deletedEnrollment->sqid,
                    ])->isNotEmpty(),
                )->where(
                    'servicePlans',
                    fn (Collection $options): bool => ! $options->pluck('id')->intersect([
                        $deactivatedServicePlan->sqid,
                        $deletedServicePlan->sqid,
                    ])->isNotEmpty(),
                )->where(
                    'statuses',
                    fn (Collection $options): bool => ! $options->pluck('id')->intersect([
                        $deactivatedStatus->sqid,
                        $deletedStatus->sqid,
                        $nonInitialStatus->sqid,
                    ])->isNotEmpty(),
                );
            });
    });
});
```

## Same Availability Case On A Nested Form

A separate controller uses the same case name when its only options are inactive or soft deleted. Place after its positive option-list case.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\Member;
use App\Models\ServicePlan;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page without unavailable options', function (): void {
        $member = Member::factory()->createOne();

        ServicePlan::factory()->deactivated()->recycle($member->team)->createOne();
        ServicePlan::factory()->trashed()->recycle($member->team)->createOne();

        login(team: $member->team);

        $response = get(route('teams.members.enrollments.create', [
            'team' => $member->team,
            'member' => $member,
        ]));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page): void {
                $page->component('members/enrollments/Create')
                    ->has('servicePlans', 0);
            });
    });
});
```
