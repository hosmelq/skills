# Actions: Update with Historical Selections

Complete update action preserving omitted or unchanged historical relations while validating replacements. Lock a changed parent or changed dependent selection, validate effective measurements and translate the named reference conflict.

The input is a partial update: Optional, null and supplied IDs remain distinct. Use the target typed input and exceptions. This complete example is for the combined workflow; reuse the smaller examples for isolated operations.

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrders;

use App\Actions\ServicePlans\EnsureActiveServicePlan;
use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Enums\WeightUnit;
use App\Exceptions\CannotUseDeactivatedServicePlan;
use App\Exceptions\WorkOrders\CabinetIsUnavailable;
use App\Exceptions\WorkOrders\MemberDoesNotOwnCabinet;
use App\Exceptions\WorkOrders\MemberIsUnavailable;
use App\Exceptions\WorkOrders\PickupFacilityIsUnavailable;
use App\Exceptions\WorkOrders\PlanRuleDoesNotBelongToServicePlan;
use App\Exceptions\WorkOrders\PlanRuleRequiresServicePlan;
use App\Exceptions\WorkOrders\ReceivedFacilityIsUnavailable;
use App\Exceptions\WorkOrders\ServicePlanIsUnavailable;
use App\Exceptions\WorkOrders\WeightUnitDoesNotMatchServicePlan;
use App\Exceptions\WorkOrders\WorkOrderDimensionsAreIncomplete;
use App\Exceptions\WorkOrders\WorkOrderIsFinal;
use App\Exceptions\WorkOrders\WorkOrderReferenceAlreadyExists;
use App\Exceptions\WorkOrders\WorkOrderRequiresReceivedAt;
use App\Exceptions\WorkOrders\WorkOrderWeightIsIncomplete;
use App\Models\Cabinet;
use App\Models\Facility;
use App\Models\Member;
use App\Models\PlanRule;
use App\Models\ServicePlan;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

class UpdateWorkOrder
{
    public function __construct(private readonly EnsureActiveServicePlan $ensureActiveServicePlan)
    {
    }

    public function handle(WorkOrder $workOrder, UpdateWorkOrderInput $input): WorkOrder
    {
        if (! $this->shouldLockServicePlan($workOrder, $input)) {
            return $this->updateWorkOrder($workOrder, $input);
        }

        return DB::transaction(function () use ($input, $workOrder): WorkOrder {
            $servicePlan = $this->lockEffectiveServicePlan($workOrder, $input);

            return $this->updateWorkOrder($workOrder, $input, $servicePlan);
        });
    }

    private function currentCabinet(WorkOrder $workOrder): null|Cabinet
    {
        return $workOrder->cabinet()->withTrashed()->first();
    }

    private function currentFacility(null|int $facilityId): null|Facility
    {
        return $facilityId === null ? null : Facility::query()->withTrashed()->find($facilityId);
    }

    private function currentMember(WorkOrder $workOrder): null|Member
    {
        return $workOrder->member()->withTrashed()->first();
    }

    private function currentPlanRule(WorkOrder $workOrder): null|PlanRule
    {
        return $workOrder->planRule()->withTrashed()->first();
    }

    private function currentServicePlan(WorkOrder $workOrder): null|ServicePlan
    {
        return $workOrder->servicePlan()->withTrashed()->first();
    }

    private function effectiveString(
        null|string $persistedValue,
        null|Optional|string $inputValue,
    ): null|string {
        return $inputValue instanceof Optional ? $persistedValue : $inputValue;
    }

    private function ensureMeasurementsAreComplete(
        WorkOrder $workOrder,
        UpdateWorkOrderInput $input,
        null|ServicePlan $servicePlan,
    ): void {
        $weight = $this->effectiveString($workOrder->weight, $input->weight);
        $weightUnit = $input->weightUnit instanceof Optional
            ? $workOrder->weight_unit
            : $input->weightUnit;
        $dimensionUnit = $input->dimensionUnit instanceof Optional
            ? $workOrder->dimension_unit
            : $input->dimensionUnit;

        throw_if(
            ($weight === null) !== ($weightUnit === null),
            WorkOrderWeightIsIncomplete::becauseValueAndUnitMustBeProvidedTogether(),
        );

        throw_if(
            $weight !== null
            && $weightUnit instanceof WeightUnit
            && $servicePlan instanceof ServicePlan
            && $weightUnit !== $servicePlan->weight_unit,
            WeightUnitDoesNotMatchServicePlan::becauseItDiffersFromTheServicePlan(),
        );

        $dimensions = [
            $this->effectiveString($workOrder->length, $input->length),
            $this->effectiveString($workOrder->width, $input->width),
            $this->effectiveString($workOrder->height, $input->height),
            $dimensionUnit,
        ];
        $presentDimensions = collect($dimensions)
            ->filter(fn (mixed $value): bool => $value !== null)
            ->count();

        throw_if(
            $presentDimensions !== 0 && $presentDimensions !== count($dimensions),
            WorkOrderDimensionsAreIncomplete::becauseValuesAndUnitMustBeProvidedTogether(),
        );
    }

    private function ensureReceivedAtIsPresent(
        WorkOrder $workOrder,
        UpdateWorkOrderInput $input,
    ): void {
        throw_if(
            $this->effectiveString(
                $workOrder->received_at->toDateTimeString(),
                $input->receivedAt,
            ) === null,
            WorkOrderRequiresReceivedAt::becauseItIsRequired(),
        );
    }

    private function ensureRelationsAreCompatible(
        null|Member $member,
        null|Cabinet $cabinet,
        null|ServicePlan $servicePlan,
        null|PlanRule $planRule,
    ): void {
        throw_if(
            $cabinet instanceof Cabinet
            && $member instanceof Member
            && $cabinet->member_id !== $member->id,
            MemberDoesNotOwnCabinet::becauseTheCabinetBelongsToAnotherMember(),
        );

        throw_if(
            $planRule instanceof PlanRule
            && ! $servicePlan instanceof ServicePlan,
            PlanRuleRequiresServicePlan::becauseNoneWasSelected(),
        );

        throw_if(
            $planRule instanceof PlanRule
            && $planRule->service_plan_id !== $servicePlan->id,
            PlanRuleDoesNotBelongToServicePlan::becauseTheyDoNotMatch(),
        );
    }

    private function ensureWorkOrderIsEditable(WorkOrder $workOrder): void
    {
        throw_if(
            $workOrder->workOrderStatus()
                ->withTrashed()
                ->firstOrFail()
                ->base_status
                ->isFinal(),
            WorkOrderIsFinal::becauseItCannotBeChangedOrDeleted(),
        );
    }

    private function isSameId(null|int $currentId, int $submittedId): bool
    {
        return $currentId === $submittedId;
    }

    private function lockEffectiveServicePlan(
        WorkOrder $workOrder,
        UpdateWorkOrderInput $input,
    ): ServicePlan {
        $currentServicePlan = $this->currentServicePlan($workOrder);
        $servicePlan = ! $input->servicePlanId instanceof Optional
            && $input->servicePlanId !== null
            && ! $this->isSameId($currentServicePlan?->id, $input->servicePlanId)
                ? $workOrder->team->servicePlans()
                    ->whereKey($input->servicePlanId)
                    ->first()
                : $currentServicePlan;

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

    private function planRuleHasChanged(WorkOrder $workOrder, UpdateWorkOrderInput $input): bool
    {
        if ($input->planRuleId instanceof Optional) {
            return false;
        }

        $currentPlanRule = $this->currentPlanRule($workOrder);

        if ($input->planRuleId === null) {
            return $currentPlanRule instanceof PlanRule;
        }

        return ! $this->isSameId($currentPlanRule?->id, $input->planRuleId);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function replaceRelationValue(
        array &$values,
        string $field,
        null|int|Optional $inputValue,
        null|Model $relation,
    ): void {
        if ($inputValue instanceof Optional) {
            unset($values[$field]);

            return;
        }

        $values[$field] = $relation?->getKey();
    }

    private function resolveCabinet(
        WorkOrder $workOrder,
        null|int|Optional $cabinetId,
    ): null|Cabinet {
        $currentCabinet = $this->currentCabinet($workOrder);

        if ($cabinetId instanceof Optional) {
            return $currentCabinet;
        }

        if ($cabinetId === null) {
            return null;
        }

        if ($this->isSameId($currentCabinet?->id, $cabinetId)) {
            return $currentCabinet;
        }

        $cabinet = $workOrder->team->cabinets()
            ->active()
            ->whereKey($cabinetId)
            ->first();

        throw_unless($cabinet instanceof Cabinet, CabinetIsUnavailable::becauseItIsUnavailable());

        return $cabinet;
    }

    private function resolveMember(WorkOrder $workOrder, null|int|Optional $memberId): null|Member
    {
        $currentMember = $this->currentMember($workOrder);

        if ($memberId instanceof Optional) {
            return $currentMember;
        }

        if ($memberId === null) {
            return null;
        }

        if ($this->isSameId($currentMember?->id, $memberId)) {
            return $currentMember;
        }

        $member = $workOrder->team->members()
            ->whereKey($memberId)
            ->first();

        throw_unless($member instanceof Member, MemberIsUnavailable::becauseItIsUnavailable());

        return $member;
    }

    private function resolvePickupFacility(
        WorkOrder $workOrder,
        null|int|Optional $facilityId,
    ): null|Facility {
        $currentFacility = $this->currentFacility($workOrder->pickup_facility_id);

        if ($facilityId instanceof Optional) {
            return $currentFacility;
        }

        if ($facilityId === null) {
            return null;
        }

        if ($this->isSameId($currentFacility?->id, $facilityId)) {
            return $currentFacility;
        }

        $facility = $workOrder->team->facilities()
            ->active()
            ->whereKey($facilityId)
            ->first();

        throw_unless(
            $facility instanceof Facility,
            PickupFacilityIsUnavailable::becauseItIsUnavailable(),
        );

        return $facility;
    }

    private function resolvePlanRule(
        WorkOrder $workOrder,
        null|ServicePlan $servicePlan,
        null|int|Optional $planRuleId,
    ): null|PlanRule {
        $currentPlanRule = $this->currentPlanRule($workOrder);

        if ($planRuleId instanceof Optional) {
            return $currentPlanRule;
        }

        if ($planRuleId === null) {
            return null;
        }

        if ($this->isSameId($currentPlanRule?->id, $planRuleId)) {
            return $currentPlanRule;
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

    private function resolveReceivedFacility(
        WorkOrder $workOrder,
        null|int|Optional $facilityId,
    ): null|Facility {
        $currentFacility = $this->currentFacility($workOrder->received_facility_id);

        if ($facilityId instanceof Optional) {
            return $currentFacility;
        }

        if ($facilityId === null) {
            return null;
        }

        if ($this->isSameId($currentFacility?->id, $facilityId)) {
            return $currentFacility;
        }

        $facility = $workOrder->team->facilities()
            ->active()
            ->whereKey($facilityId)
            ->first();

        throw_unless(
            $facility instanceof Facility,
            ReceivedFacilityIsUnavailable::becauseItIsUnavailable(),
        );

        return $facility;
    }

    private function resolveServicePlan(
        WorkOrder $workOrder,
        null|int|Optional $servicePlanId,
    ): null|ServicePlan {
        $currentServicePlan = $this->currentServicePlan($workOrder);

        if ($servicePlanId instanceof Optional) {
            return $currentServicePlan;
        }

        if ($servicePlanId === null) {
            return null;
        }

        if ($this->isSameId($currentServicePlan?->id, $servicePlanId)) {
            return $currentServicePlan;
        }

        $servicePlan = $workOrder->team->servicePlans()
            ->active()
            ->whereKey($servicePlanId)
            ->first();

        throw_unless(
            $servicePlan instanceof ServicePlan,
            ServicePlanIsUnavailable::becauseItIsUnavailable(),
        );

        return $servicePlan;
    }

    private function shouldLockServicePlan(WorkOrder $workOrder, UpdateWorkOrderInput $input): bool
    {
        $currentServicePlan = $this->currentServicePlan($workOrder);

        if (
            ! $input->servicePlanId instanceof Optional
            && $input->servicePlanId !== null
            && ! $this->isSameId($currentServicePlan?->id, $input->servicePlanId)
        ) {
            return true;
        }

        if (
            $input->planRuleId === null
            || ! $this->planRuleHasChanged($workOrder, $input)
        ) {
            return false;
        }

        return ($input->servicePlanId instanceof Optional || $input->servicePlanId !== null)
            && $currentServicePlan instanceof ServicePlan;
    }

    private function updateWorkOrder(
        WorkOrder $workOrder,
        UpdateWorkOrderInput $input,
        null|ServicePlan $lockedServicePlan = null,
    ): WorkOrder {
        $this->ensureWorkOrderIsEditable($workOrder);

        $member = $this->resolveMember($workOrder, $input->memberId);
        $cabinet = $this->resolveCabinet($workOrder, $input->cabinetId);
        $receivedFacility = $this->resolveReceivedFacility($workOrder, $input->receivedFacilityId);
        $pickupFacility = $this->resolvePickupFacility($workOrder, $input->pickupFacilityId);
        $servicePlan = $lockedServicePlan
            ?? $this->resolveServicePlan($workOrder, $input->servicePlanId);
        $planRule = $this->resolvePlanRule($workOrder, $servicePlan, $input->planRuleId);

        $this->ensureRelationsAreCompatible($member, $cabinet, $servicePlan, $planRule);
        $this->ensureMeasurementsAreComplete($workOrder, $input, $servicePlan);
        $this->ensureReceivedAtIsPresent($workOrder, $input);

        $reference = $this->effectiveString($workOrder->reference, $input->reference);

        throw_if(
            ! $input->reference instanceof Optional
            && $reference !== null
            && $workOrder->team->workOrders()
                ->whereKeyNot($workOrder)
                ->whereRaw('lower(reference) = lower(?)', [$reference])
                ->exists(),
            WorkOrderReferenceAlreadyExists::becauseItIsAlreadyInUse(),
        );

        $values = $input->transform();

        $this->replaceRelationValue($values, 'member_id', $input->memberId, $member);
        $this->replaceRelationValue($values, 'cabinet_id', $input->cabinetId, $cabinet);
        $this->replaceRelationValue(
            $values,
            'pickup_facility_id',
            $input->pickupFacilityId,
            $pickupFacility,
        );
        $this->replaceRelationValue(
            $values,
            'received_facility_id',
            $input->receivedFacilityId,
            $receivedFacility,
        );
        $this->replaceRelationValue(
            $values,
            'service_plan_id',
            $input->servicePlanId,
            $servicePlan,
        );
        $this->replaceRelationValue($values, 'plan_rule_id', $input->planRuleId, $planRule);

        try {
            return tap($workOrder)->update($values);
        } catch (QueryException $exception) {
            throw_if(
                str_contains($exception->getMessage(), 'work_orders_active_reference_unique'),
                WorkOrderReferenceAlreadyExists::becauseItIsAlreadyInUse($exception),
            );

            throw $exception;
        }
    }
}
```
