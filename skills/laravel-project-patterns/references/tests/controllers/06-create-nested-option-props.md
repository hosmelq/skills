# Create Tests: Nested Option IDs And Metadata

Positive create-page example with several related option lists: public IDs, nested association IDs, unit metadata, enum options and an initial-status flag. This case checks supplied values; it does not establish every exclusion or list ordering.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use App\Models\Enrollment;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrderStatus;
use Inertia\Testing\AssertableInertia;

describe('create', function (): void {
    it('shows the create page', function (): void {
        $team = Team::factory()->createOne();
        $member = Member::factory()->for($team)->createOne();
        $facility = Facility::factory()->for($team)->createOne();
        $servicePlan = ServicePlan::factory()->for($team)->createOne([
            'weight_unit' => WeightUnit::Kilograms,
        ]);
        $planRule = PlanRule::factory()->for($servicePlan)->createOne();
        $enrollment = Enrollment::factory()
            ->for($member)
            ->for($team)
            ->for($servicePlan)
            ->createOne(['label' => 'Workshop plan']);
        $status = WorkOrderStatus::factory()->initial()->for($team)->createOne();

        signIn(team: $team);

        $response = get(route('teams.work-orders.create', $team));

        $response->assertOk()
            ->assertInertia(function (AssertableInertia $page) use (
                $member,
                $facility,
                $enrollment,
                $team,
                $planRule,
                $servicePlan,
                $status,
            ): void {
                $page->component('work-orders/Create')
                    ->where('team.id', $team->public_id)
                    ->where('members.0.id', $member->public_id)
                    ->where('members.0.display_name', $member->display_name)
                    ->where('facilities.0.id', $facility->public_id)
                    ->where('facilities.0.name', $facility->name)
                    ->where('enrollments.0.id', $enrollment->public_id)
                    ->where('enrollments.0.member.id', $member->public_id)
                    ->where('enrollments.0.service_plan.id', $servicePlan->public_id)
                    ->where('servicePlans.0.id', $servicePlan->public_id)
                    ->where('servicePlans.0.weight_unit', WeightUnit::Kilograms->value)
                    ->where('planRules.0.id', $planRule->public_id)
                    ->where('planRules.0.service_plan.id', $servicePlan->public_id)
                    ->where('statuses.0.id', $status->public_id)
                    ->where('statuses.0.is_initial', true)
                    ->where('lengthUnits', LengthUnit::options())
                    ->where('weightUnits', WeightUnit::options());
            });
    });
});
```
