# HTTP Resources: Derived Final-State Flag

Use this shape when the response includes is_final from the base enum. It is independent of deactivation, initial state and visibility; keep all stored fields too.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\WorkOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property WorkOrderStatus $resource
 */
class WorkOrderStatusResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'base_status' => $this->resource->base_status,
            'color' => $this->resource->color,
            'created_at' => $this->resource->created_at,
            'deactivated_at' => $this->resource->deactivated_at,
            'description' => $this->resource->description,
            'id' => $this->resource->sqid,
            'is_final' => $this->resource->base_status->isFinal(),
            'is_initial' => $this->resource->is_initial,
            'is_member_visible' => $this->resource->is_member_visible,
            'name' => $this->resource->name,
            'sort_order' => $this->resource->sort_order,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```
