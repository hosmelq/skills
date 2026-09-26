# Actions: Create with Related Selections

Complete creation action for scoped optional selections, a uniquely available initial state, active-parent locking, relation compatibility and case-insensitive reference conflicts. Omission/null select documented defaults.

Use the target Data input, model casts and domain exceptions. Role-specific selection failures and QueryException translation are part of this example; preserve unrelated database failures.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrders;

use App\Actions\ServicePlans\EnsureActiveServicePlan;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
use App\Enums\BaseStatus;
use App\Enums\WeightUnit;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Exceptions\WorkOrders\CabinetIsUnavailable;
use App\Exceptions\WorkOrders\CurrentFacilityIsUnavailable;
use App\Exceptions\WorkOrders\InitialWorkOrderStatusIsUnavailable;
use App\Exceptions\WorkOrders\MemberDoesNotOwnCabinet;
use App\Exceptions\WorkOrders\MemberIsUnavailable;
use App\Exceptions\WorkOrders\PickupFacilityIsUnavailable;
use App\Exceptions\WorkOrders\PlanRuleDoesNotBelongToServicePlan;
use App\Exceptions\WorkOrders\PlanRuleRequiresServicePlan;
use App\Exceptions\WorkOrders\ReceivedFacilityIsUnavailable;
use App\Exceptions\WorkOrders\ServicePlanIsUnavailable;
use App\Exceptions\WorkOrders\WeightUnitDoesNotMatchServicePlan;
use App\Exceptions\WorkOrders\WorkOrderReferenceAlreadyExists;
use App\Exceptions\WorkOrders\WorkOrderStatusIsUnavailable;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\Team;
use App\Models\WorkOrder;
use App\Models\WorkOrderStatus;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

class CreateWorkOrder
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(Team $team, CreateWorkOrderInput $input): WorkOrder
    {
        try {
            return DB::transaction(function () use ($team, $input): WorkOrder {
                $workOrderStatus = $this->resolveWorkOrderStatus($team, $input);
                $receivedFacility = $this->resolveReceivedFacility(
                    $team,
                    $input->receivedFacilityId instanceof Optional
                        ? null
                        : $input->receivedFacilityId,
                );
                $currentFacility = $this->resolveCurrentFacility(
                    $team,
                    $input->currentFacilityId instanceof Optional
                        ? null
                        : $input->currentFacilityId,
                ) ?? $receivedFacility;
                $pickupFacility = $this->resolvePickupFacility(
                    $team,
                    $input->pickupFacilityId instanceof Optional ? null : $input->pickupFacilityId,
                );
                $member = $this->resolveMember(
                    $team,
                    $input->memberId instanceof Optional ? null : $input->memberId,
                );
                $cabinet = $this->resolveCabinet(
                    $team,
                    $input->cabinetId instanceof Optional ? null : $input->cabinetId,
                );
                $servicePlan = $this->resolveServicePlan(
                    $team,
                    $input->servicePlanId instanceof Optional ? null : $input->servicePlanId,
                );
                $planRule = $this->resolvePlanRule(
                    $servicePlan,
                    $input->planRuleId instanceof Optional ? null : $input->planRuleId,
                );

                throw_if(
                    $cabinet instanceof Cabinet
                    && $member instanceof Member
                    && $cabinet->member_id !== $member->id,
                    MemberDoesNotOwnCabinet::becauseTheCabinetBelongsToAnotherMember(),
                );

                throw_if(
                    $servicePlan instanceof ServicePlan
                    && ! $input->weightUnit instanceof Optional
                    && $input->weightUnit instanceof WeightUnit
                    && $input->weightUnit !== $servicePlan->weight_unit,
                    WeightUnitDoesNotMatchServicePlan::becauseItDiffersFromTheServicePlan(),
                );

                $reference = $input->reference instanceof Optional ? null : $input->reference;

                throw_if(
                    $reference !== null
                    && $team->workOrders()
                        ->whereRaw('lower(reference) = lower(?)', [$reference])
                        ->exists(),
                    WorkOrderReferenceAlreadyExists::becauseItIsAlreadyInUse(),
                );

                return $team->workOrders()->create([
                    'cabinet_id' => $cabinet?->id,
                    'current_facility_id' => $currentFacility?->id,
                    'dimension_unit' => $input->dimensionUnit instanceof Optional
                        ? null
                        : $input->dimensionUnit,
                    'external_carrier_name' => $input->externalCarrierName instanceof Optional
                        ? null
                        : $input->externalCarrierName,
                    'external_tracking_number' => $input->externalTrackingNumber instanceof Optional
                        ? null
                        : $input->externalTrackingNumber,
                    'height' => $input->height instanceof Optional ? null : $input->height,
                    'length' => $input->length instanceof Optional ? null : $input->length,
                    'member_id' => $member?->id,
                    'note' => $input->note instanceof Optional ? null : $input->note,
                    'pickup_facility_id' => $pickupFacility?->id,
                    'plan_rule_id' => $planRule?->id,
                    'received_at' => ($input->receivedAt instanceof Optional
                        ? null
                        : $input->receivedAt) ?? now(),
                    'received_facility_id' => $receivedFacility?->id,
                    'received_label_text' => $input->receivedLabelText instanceof Optional
                        ? null
                        : $input->receivedLabelText,
                    'reference' => $reference,
                    'service_plan_id' => $servicePlan?->id,
                    'weight' => $input->weight instanceof Optional ? null : $input->weight,
                    'weight_unit' => $input->weightUnit instanceof Optional
                        ? null
                        : $input->weightUnit,
                    'width' => $input->width instanceof Optional ? null : $input->width,
                    'work_order_status_id' => $workOrderStatus->id,
                ]);
            });
        } catch (QueryException $exception) {
            throw_if(
                str_contains($exception->getMessage(), 'work_orders_active_reference_unique'),
                WorkOrderReferenceAlreadyExists::becauseItIsAlreadyInUse($exception),
            );

            throw $exception;
        }
    }

    private function resolveCabinet(Team $team, null|int $cabinetId): null|Cabinet
    {
        if ($cabinetId === null) {
            return null;
        }

        $cabinet = $team->cabinets()
            ->active()
            ->whereKey($cabinetId)
            ->first();

        throw_unless($cabinet instanceof Cabinet, CabinetIsUnavailable::becauseItIsUnavailable());

        return $cabinet;
    }

    private function resolveCurrentFacility(Team $team, null|int $facilityId): null|Facility
    {
        $facility = $this->resolveFacility($team, $facilityId);

        throw_if(
            $facilityId !== null && ! $facility instanceof Facility,
            CurrentFacilityIsUnavailable::becauseItIsUnavailable(),
        );

        return $facility;
    }

    private function resolveFacility(Team $team, null|int $facilityId): null|Facility
    {
        if ($facilityId === null) {
            return null;
        }

        return $team->facilities()
            ->active()
            ->whereKey($facilityId)
            ->first();
    }

    private function resolveMember(Team $team, null|int $memberId): null|Member
    {
        if ($memberId === null) {
            return null;
        }

        $member = $team->members()
            ->whereKey($memberId)
            ->first();

        throw_unless($member instanceof Member, MemberIsUnavailable::becauseItIsUnavailable());

        return $member;
    }

    private function resolvePickupFacility(Team $team, null|int $facilityId): null|Facility
    {
        $facility = $this->resolveFacility($team, $facilityId);

        throw_if(
            $facilityId !== null && ! $facility instanceof Facility,
            PickupFacilityIsUnavailable::becauseItIsUnavailable(),
        );

        return $facility;
    }

    private function resolvePlanRule(
        null|ServicePlan $servicePlan,
        null|int $planRuleId,
    ): null|PlanRule {
        if ($planRuleId === null) {
            return null;
        }

        throw_unless(
            $servicePlan instanceof ServicePlan,
            PlanRuleRequiresServicePlan::becauseNoneWasSelected(),
        );

        $planRule = $servicePlan->planRules()
            ->whereKey($planRuleId)
            ->first();

        throw_unless(
            $planRule instanceof PlanRule,
            PlanRuleDoesNotBelongToServicePlan::becauseTheyDoNotMatch(),
        );

        return $planRule;
    }

    private function resolveReceivedFacility(Team $team, null|int $facilityId): null|Facility
    {
        $facility = $this->resolveFacility($team, $facilityId);

        throw_if(
            $facilityId !== null && ! $facility instanceof Facility,
            ReceivedFacilityIsUnavailable::becauseItIsUnavailable(),
        );

        return $facility;
    }

    private function resolveServicePlan(Team $team, null|int $servicePlanId): null|ServicePlan
    {
        if ($servicePlanId === null) {
            return null;
        }

        $servicePlan = $team->servicePlans()
            ->whereKey($servicePlanId)
            ->first();

        throw_unless(
            $servicePlan instanceof ServicePlan,
            ServicePlanIsUnavailable::becauseItIsUnavailable(),
        );

        try {
            return $this->ensureActiveServicePlan->handle($servicePlan);
        } catch (CannotUseDeactivatedServicePlan|ModelNotFoundException) {
            throw ServicePlanIsUnavailable::becauseItIsUnavailable();
        }
    }

    private function resolveWorkOrderStatus(
        Team $team,
        CreateWorkOrderInput $input,
    ): WorkOrderStatus {
        $workOrderStatusId = $input->workOrderStatusId instanceof Optional
            ? null
            : $input->workOrderStatusId;

        if ($workOrderStatusId !== null) {
            $workOrderStatus = $team->workOrderStatuses()
                ->where('base_status', BaseStatus::Open)
                ->whereNull('deactivated_at')
                ->whereKey($workOrderStatusId)
                ->first();

            throw_unless(
                $workOrderStatus instanceof WorkOrderStatus,
                WorkOrderStatusIsUnavailable::becauseItIsUnavailable(),
            );

            return $workOrderStatus;
        }

        $workOrderStatuses = $team->workOrderStatuses()
            ->where('base_status', BaseStatus::Open)
            ->whereNull('deactivated_at')
            ->where('is_initial', true)
            ->orderBy('id')
            ->limit(2)
            ->get();

        throw_if(
            $workOrderStatuses->count() !== 1,
            InitialWorkOrderStatusIsUnavailable::becauseNoneIsAvailable(),
        );

        return $workOrderStatuses->sole();
    }
}
```
