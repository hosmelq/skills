# Controllers: Map Related Creation Errors

Keep the complete creation exception map: selected relation availability, ownership mismatch, initial state, unique reference and unit mismatch. Different exceptions can intentionally target the same field. These selected methods retain the inspected controller and route middleware when adapted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\WorkOrders\CreateWorkOrder;
use App\Actions\WorkOrders\Inputs\CreateWorkOrderInput;
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
use App\Http\Requests\StoreWorkOrderRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class WorkOrderController
{
    public function store(
        StoreWorkOrderRequest $request,
        Team $team,
        CreateWorkOrder $createWorkOrder,
    ): RedirectResponse {
        try {
            $workOrder = $createWorkOrder->handle(
                $team,
                CreateWorkOrderInput::from($request->validated()),
            );
        } catch (CurrentFacilityIsUnavailable) {
            throw ValidationException::withMessages([
                'current_facility_id' => __('work_order.validation.relation_unavailable'),
            ]);
        } catch (MemberDoesNotOwnCabinet) {
            throw ValidationException::withMessages([
                'member_id' => __('work_order.validation.cabinet_member_mismatch'),
            ]);
        } catch (MemberIsUnavailable) {
            throw ValidationException::withMessages([
                'member_id' => __('work_order.validation.relation_unavailable'),
            ]);
        } catch (InitialWorkOrderStatusIsUnavailable) {
            throw ValidationException::withMessages([
                'work_order_status_id' => __('work_order.validation.initial_status_required'),
            ]);
        } catch (CabinetIsUnavailable) {
            throw ValidationException::withMessages([
                'cabinet_id' => __('work_order.validation.relation_unavailable'),
            ]);
        } catch (WorkOrderReferenceAlreadyExists) {
            throw ValidationException::withMessages([
                'reference' => __('work_order.validation.reference_unique'),
            ]);
        } catch (WorkOrderStatusIsUnavailable) {
            throw ValidationException::withMessages([
                'work_order_status_id' => __('work_order.validation.relation_unavailable'),
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
        ])->toast(__('work_order.created.title'));
    }
}
```
