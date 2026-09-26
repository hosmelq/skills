# HTTP Resources: Conditional Relations with Distinct Roles

Expose each loaded relation under its response key, preserving distinct current, pickup and received roles. Keep status, plan and rule separate; unloaded keys disappear and loaded null stays null. Historical loading belongs to the query.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property WorkOrder $resource
 */
class WorkOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'cabinet' => CabinetResource::make($this->whenLoaded('cabinet')),
            'created_at' => $this->resource->created_at,
            'current_facility' => FacilityResource::make($this->whenLoaded('currentFacility')),
            'dimension_unit' => $this->resource->dimension_unit,
            'external_carrier_name' => $this->resource->external_carrier_name,
            'external_tracking_number' => $this->resource->external_tracking_number,
            'height' => $this->resource->height,
            'id' => $this->resource->sqid,
            'length' => $this->resource->length,
            'member' => MemberResource::make($this->whenLoaded('member')),
            'note' => $this->resource->note,
            'pickup_facility' => FacilityResource::make($this->whenLoaded('pickupFacility')),
            'plan_rule' => PlanRuleResource::make($this->whenLoaded('planRule')),
            'received_at' => $this->resource->received_at,
            'received_facility' => FacilityResource::make($this->whenLoaded('receivedFacility')),
            'received_label_text' => $this->resource->received_label_text,
            'reference' => $this->resource->reference,
            'service_plan' => ServicePlanResource::make($this->whenLoaded('servicePlan')),
            'status' => WorkOrderStatusResource::make($this->whenLoaded('workOrderStatus')),
            'updated_at' => $this->resource->updated_at,
            'weight' => $this->resource->weight,
            'weight_unit' => $this->resource->weight_unit,
            'width' => $this->resource->width,
        ];
    }
}
```
