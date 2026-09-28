# Factories: Reuse Related Records

Reuse same-team records in order: direct relation, cabinet relation, then the plan-rule relation for a service plan. Explicit `withTrashed()` queries allow deleted fixtures to be reused, unlike relationship property access, which applies default scopes. Optional factories are used only when a new record is needed. Hooks create or associate related records after the parent is persisted.

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderLine;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cabinet_id' => null,
            'current_facility_id' => null,
            'member_id' => null,
            'pickup_facility_id' => null,
            'plan_rule_id' => null,
            'received_facility_id' => null,
            'service_plan_id' => null,
            'team_id' => Team::factory(),
            'work_order_status_id' => $this->workOrderStatusFactoryFor(...),

            'dimension_unit' => null,
            'external_carrier_name' => null,
            'external_tracking_number' => null,
            'height' => null,
            'length' => null,
            'note' => fake()->optional()->paragraph(),
            'received_at' => now(),
            'received_label_text' => null,
            'reference' => null,
            'weight' => null,
            'weight_unit' => null,
            'width' => null,
        ];
    }

    public function withCabinet(null|CabinetFactory $cabinetFactory = null): static
    {
        return $this->afterCreating(function (WorkOrder $workOrder) use ($cabinetFactory): void {
            $member = $this->memberFor($workOrder);
            $servicePlan = $this->servicePlanFor($workOrder);
            $cabinet = ($cabinetFactory ?? Cabinet::factory())->createOne([
                'member_id' => $member->id,
                'service_plan_id' => $servicePlan->id,
                'team_id' => $workOrder->team_id,
            ]);

            $workOrder->member()->associate($member);
            $workOrder->cabinet()->associate($cabinet);
            $workOrder->servicePlan()->associate($servicePlan);
            $workOrder->save();
        });
    }

    public function withCurrentFacility(null|FacilityFactory $facilityFactory = null): static
    {
        return $this->afterCreating(function (WorkOrder $workOrder) use ($facilityFactory): void {
            $facility = ($facilityFactory ?? Facility::factory())->createOne([
                'team_id' => $workOrder->team_id,
            ]);

            $workOrder->currentFacility()->associate($facility);
            $workOrder->save();
        });
    }

    public function withLine(): static
    {
        return $this->afterCreating(function (WorkOrder $workOrder): void {
            WorkOrderLine::factory()
                ->for($workOrder)
                ->createOne();
        });
    }

    public function withMember(null|MemberFactory $memberFactory = null): static
    {
        return $this->afterCreating(function (WorkOrder $workOrder) use ($memberFactory): void {
            $workOrder->member()->associate($this->memberFor($workOrder, $memberFactory));
            $workOrder->save();
        });
    }

    public function withPickupFacility(null|FacilityFactory $facilityFactory = null): static
    {
        return $this->afterCreating(function (WorkOrder $workOrder) use ($facilityFactory): void {
            $facility = ($facilityFactory ?? Facility::factory())->createOne([
                'team_id' => $workOrder->team_id,
            ]);

            $workOrder->pickupFacility()->associate($facility);
            $workOrder->save();
        });
    }

    public function withPlanRule(null|PlanRuleFactory $planRuleFactory = null): static
    {
        return $this->afterCreating(function (WorkOrder $workOrder) use ($planRuleFactory): void {
            $servicePlan = $this->servicePlanFor($workOrder);
            $planRule = ($planRuleFactory ?? PlanRule::factory())
                ->for($servicePlan)
                ->createOne();

            $workOrder->servicePlan()->associate($servicePlan);
            $workOrder->planRule()->associate($planRule);
            $workOrder->save();
        });
    }

    public function withReceivedFacility(null|FacilityFactory $facilityFactory = null): static
    {
        return $this->afterCreating(function (WorkOrder $workOrder) use ($facilityFactory): void {
            $facility = ($facilityFactory ?? Facility::factory())->createOne([
                'team_id' => $workOrder->team_id,
            ]);

            $workOrder->receivedFacility()->associate($facility);
            $workOrder->save();
        });
    }

    public function withServicePlan(null|ServicePlanFactory $servicePlanFactory = null): static
    {
        return $this->afterCreating(
            function (WorkOrder $workOrder) use ($servicePlanFactory): void {
                $workOrder->servicePlan()->associate(
                    $this->servicePlanFor($workOrder, $servicePlanFactory),
                );
                $workOrder->save();
            }
        );
    }

    private function memberFor(
        WorkOrder $workOrder,
        null|MemberFactory $memberFactory = null,
    ): Member {
        $member = $workOrder->member()->withTrashed()->first();

        if ($member?->team_id === $workOrder->team_id) {
            return $member;
        }

        $cabinet = $workOrder->cabinet()->withTrashed()->first();

        if ($cabinet?->team_id === $workOrder->team_id) {
            return $cabinet->member()->withTrashed()->firstOrFail();
        }

        return ($memberFactory ?? Member::factory())->createOne([
            'team_id' => $workOrder->team_id,
        ]);
    }

    private function servicePlanFor(
        WorkOrder $workOrder,
        null|ServicePlanFactory $servicePlanFactory = null,
    ): ServicePlan {
        $servicePlan = $workOrder->servicePlan()->withTrashed()->first();

        if ($servicePlan?->team_id === $workOrder->team_id) {
            return $servicePlan;
        }

        $cabinet = $workOrder->cabinet()->withTrashed()->first();

        if ($cabinet?->team_id === $workOrder->team_id) {
            return $cabinet->servicePlan()->withTrashed()->firstOrFail();
        }

        $planRule = $workOrder->planRule()->withTrashed()->first();
        $planRuleServicePlan = $planRule?->servicePlan()->withTrashed()->first();

        if ($planRuleServicePlan?->team_id === $workOrder->team_id) {
            return $planRuleServicePlan;
        }

        return ($servicePlanFactory ?? ServicePlan::factory())->createOne([
            'team_id' => $workOrder->team_id,
        ]);
    }

    /**
     * @param array{team_id: int} $attributes
     */
    private function workOrderStatusFactoryFor(array $attributes): WorkOrderStatusFactory
    {
        return WorkOrderStatus::factory()
            ->state(['team_id' => $attributes['team_id']]);
    }
}
```
