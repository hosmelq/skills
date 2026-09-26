# HTTP Resources: Lifecycle and Duration Fields

Expose both deactivation and deletion timestamps, nullable description, integer duration bounds and enum units. Serialization uses already-cast values.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ServicePlan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property ServicePlan $resource
 */
class ServicePlanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->resource->created_at,
            'deactivated_at' => $this->resource->deactivated_at,
            'deleted_at' => $this->resource->deleted_at,
            'description' => $this->resource->description,
            'estimated_transit_time_unit' => $this->resource->estimated_transit_time_unit,
            'id' => $this->resource->sqid,
            'maximum_estimated_transit_time' => $this->resource->maximum_estimated_transit_time,
            'minimum_estimated_transit_time' => $this->resource->minimum_estimated_transit_time,
            'name' => $this->resource->name,
            'updated_at' => $this->resource->updated_at,
            'weight_unit' => $this->resource->weight_unit,
        ];
    }
}
```
