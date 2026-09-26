# HTTP Resources: Decimal Values and Integer Quantity

Pass model casts through: two-place unit value, four-place measurements, integer quantity and enum units. Preserve null and zero without numeric coercion.

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
