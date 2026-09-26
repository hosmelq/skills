# Controllers: Map Related Update Errors

Keep the complete update exception map, including final state and incomplete effective measures. Its branches differ from creation; forward the validated partial input unchanged. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrders\Inputs\UpdateWorkOrderInput;
use App\Actions\WorkOrders\UpdateWorkOrder;
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
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Models\Team;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class WorkOrderController
{
    public function update(
        UpdateWorkOrderRequest $request,
        Team $team,
        WorkOrder $workOrder,
        UpdateWorkOrder $updateWorkOrder,
    ): RedirectResponse {
        try {
            $updateWorkOrder->handle(
                $workOrder,
                UpdateWorkOrderInput::from($request->validated()),
            );
        } catch (MemberDoesNotOwnCabinet) {
            throw ValidationException::withMessages([
                'member_id' => __('work_order.validation.cabinet_member_mismatch'),
            ]);
        } catch (MemberIsUnavailable) {
            throw ValidationException::withMessages([
                'member_id' => __('work_order.validation.relation_unavailable'),
            ]);
        } catch (CabinetIsUnavailable) {
            throw ValidationException::withMessages([
                'cabinet_id' => __('work_order.validation.relation_unavailable'),
            ]);
        } catch (WorkOrderDimensionsAreIncomplete) {
            throw ValidationException::withMessages([
                'dimension_unit' => __('work_order.validation.dimensions_incomplete'),
            ]);
        } catch (WorkOrderIsFinal) {
            throw ValidationException::withMessages([
                'work_order' => __('work_order.validation.final_state'),
            ]);
        } catch (WorkOrderReferenceAlreadyExists) {
            throw ValidationException::withMessages([
                'reference' => __('work_order.validation.reference_unique'),
            ]);
        } catch (WorkOrderRequiresReceivedAt) {
            throw ValidationException::withMessages([
                'received_at' => __('work_order.validation.received_at_required'),
            ]);
        } catch (WorkOrderWeightIsIncomplete) {
            throw ValidationException::withMessages([
                'weight_unit' => __('work_order.validation.weight_pair_incomplete'),
            ]);
        } catch (PickupFacilityIsUnavailable) {
            throw ValidationException::withMessages([
                'pickup_facility_id' => __('work_order.validation.relation_unavailable'),
            ]);
        } catch (PlanRuleDoesNotBelongToServicePlan) {
            throw ValidationException::withMessages([
                'plan_rule_id' => __('work_order.validation.plan_rule_service_plan_mismatch'),
            ]);
        } catch (PlanRuleRequiresServicePlan) {
            throw ValidationException::withMessages([
                'plan_rule_id' => __('work_order.validation.plan_rule_requires_service_plan'),
            ]);
        } catch (ReceivedFacilityIsUnavailable) {
            throw ValidationException::withMessages([
                'received_facility_id' => __('work_order.validation.relation_unavailable'),
            ]);
        } catch (ServicePlanIsUnavailable) {
            throw ValidationException::withMessages([
                'service_plan_id' => __('work_order.validation.relation_unavailable'),
            ]);
        } catch (WeightUnitDoesNotMatchServicePlan) {
            throw ValidationException::withMessages([
                'weight_unit' => __('work_order.validation.weight_unit_mismatch'),
            ]);
        }

        return to_route('teams.work-orders.show', [
            'team' => $team,
            'work_order' => $workOrder,
        ])->toast(__('work_order.updated.title'));
    }
}
```
