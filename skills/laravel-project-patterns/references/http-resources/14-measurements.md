# HTTP Resources: Measurements and Event Timestamp

Keep decimal measures, unit enums, external reference metadata and the separate received timestamp. Field names match the action and request contracts.

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
            'created_at' => $this->resource->created_at,
            'dimension_unit' => $this->resource->dimension_unit,
            'external_carrier_name' => $this->resource->external_carrier_name,
            'external_tracking_number' => $this->resource->external_tracking_number,
            'height' => $this->resource->height,
            'id' => $this->resource->sqid,
            'length' => $this->resource->length,
            'note' => $this->resource->note,
            'received_at' => $this->resource->received_at,
            'received_label_text' => $this->resource->received_label_text,
            'reference' => $this->resource->reference,
            'updated_at' => $this->resource->updated_at,
            'weight' => $this->resource->weight,
            'weight_unit' => $this->resource->weight_unit,
            'width' => $this->resource->width,
        ];
    }
}
```
