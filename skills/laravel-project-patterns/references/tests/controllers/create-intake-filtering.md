# Create Tests: Excluding Options By Related State And Ownership

Use for GET create option lists whose availability depends on record state, parent relations or team ownership. These are separate conditions: excluding an unavailable enrollment does not prove exclusion of an otherwise active enrollment whose member or service plan is unavailable. The workshop domain and its data are fictional. Factories, routes, `signIn(team: ...)` and `public_id` illustrate a project contract; they are not Laravel defaults. Adapt them to the current project and keep its actual test root.

Keep these tests in this order:

1. Exclude an enrollment when its member or service plan is unavailable, using the three dataset cases.
2. Exclude a plan rule owned by another team even when its service plan is local.
3. Exclude each listed record by its own unavailable state. A foreign fixture without an exclusion assertion is not coverage.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\WorkOrderBaseStatus;
use App\Models\Enrollment;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Database\Factories\MemberFactory;
use Database\Factories\ServicePlanFactory;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page without enrollments with unavailable relations', function (string $state): void {
        $team = Team::factory()->createOne();
        $member = Member::factory()
            ->when($state === 'deleted member', fn (MemberFactory $factory): MemberFactory => $factory->trashed())
            ->for($team)
            ->createOne();
        $servicePlan = ServicePlan::factory()
            ->when($state === 'deactivated service plan', fn (ServicePlanFactory $factory): ServicePlanFactory => $factory->deactivated())
            ->when($state === 'deleted service plan', fn (ServicePlanFactory $factory): ServicePlanFactory => $factory->trashed())
            ->for($team)
            ->createOne();
        Enrollment::factory()
            ->for($member)
            ->for($team)
            ->for($servicePlan)
            ->createOne();

        signIn(team: $team);

        $response = get(route('teams.work-orders.create', $team));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/Create')
                ->has('enrollments', 0));
    })->with([
        'deactivated service plan',
        'deleted member',
        'deleted service plan',
    ]);

    it('shows the create page without plan rules from another team', function (): void {
        $team = Team::factory()->createOne();
        PlanRule::factory()
            ->for(Team::factory())
            ->for(ServicePlan::factory()->for($team))
            ->createOne();

        signIn(team: $team);

        $response = get(route('teams.work-orders.create', $team));

        $response->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('work-orders/Create')
                ->has('planRules', 0));
    });

    it('shows the create page without unavailable intake options', function (): void {
        $team = Team::factory()->createOne();

        $deletedMember = Member::factory()->trashed()->for($team)->createOne();
        $deactivatedFacility = Facility::factory()->deactivated()->for($team)->createOne();
        $deletedFacility = Facility::factory()->trashed()->for($team)->createOne();
        $deactivatedEnrollment = Enrollment::factory()->deactivated()->recycle($team)->createOne();
        $deletedEnrollment = Enrollment::factory()->trashed()->recycle($team)->createOne();
        $deactivatedServicePlan = ServicePlan::factory()
            ->deactivated()
            ->for($team)
            ->createOne();
        $deletedServicePlan = ServicePlan::factory()
            ->trashed()
            ->for($team)
            ->createOne();
        $deactivatedStatus = WorkOrderStatus::factory()
            ->deactivated()
            ->for($team)
            ->createOne();
        $deletedStatus = WorkOrderStatus::factory()->trashed()->for($team)->createOne();
        $nonInitialStatus = WorkOrderStatus::factory()
            ->withBaseStatus(WorkOrderBaseStatus::InProgress)
            ->for($team)
            ->createOne();

        signIn(team: $team);

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
                        ->contains($deletedMember->public_id),
                )->where(
                    'facilities',
                    fn (Collection $options): bool => ! $options->pluck('id')->intersect([
                        $deactivatedFacility->public_id,
                        $deletedFacility->public_id,
                    ])->isNotEmpty(),
                )->where(
                    'enrollments',
                    fn (Collection $options): bool => ! $options->pluck('id')->intersect([
                        $deactivatedEnrollment->public_id,
                        $deletedEnrollment->public_id,
                    ])->isNotEmpty(),
                )->where(
                    'servicePlans',
                    fn (Collection $options): bool => ! $options->pluck('id')->intersect([
                        $deactivatedServicePlan->public_id,
                        $deletedServicePlan->public_id,
                    ])->isNotEmpty(),
                )->where(
                    'statuses',
                    fn (Collection $options): bool => ! $options->pluck('id')->intersect([
                        $deactivatedStatus->public_id,
                        $deletedStatus->public_id,
                        $nonInitialStatus->public_id,
                    ])->isNotEmpty(),
                );
            });
    });
});
```

## Related References

- [Create block](create.md)
