# HTTP Resources: Conditional Group Resource

Add the loaded group to the complete decimal/quantity payload. Omit the group key when unloaded and retain null when loaded null. The query layer controls inactive or historical selection.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\WorkOrderLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property WorkOrderLine $resource
 */
class WorkOrderLineResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->resource->created_at,
            'currency_code' => $this->resource->currency_code,
            'description' => $this->resource->description,
            'dimension_unit' => $this->resource->dimension_unit,
            'group' => ItemGroupResource::make($this->whenLoaded('itemGroup')),
            'height' => $this->resource->height,
            'id' => $this->resource->sqid,
            'length' => $this->resource->length,
            'quantity' => $this->resource->quantity,
            'unit_value' => $this->resource->unit_value,
            'updated_at' => $this->resource->updated_at,
            'weight' => $this->resource->weight,
            'weight_unit' => $this->resource->weight_unit,
            'width' => $this->resource->width,
        ];
    }
}
```
