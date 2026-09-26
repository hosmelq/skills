# HTTP Resources: Ordered Record Fields

Keep sort order, optional descriptive fields and nullable deactivation explicit. A null lifecycle value remains present.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ItemGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property ItemGroup $resource
 */
class ItemGroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'color' => $this->resource->color,
            'created_at' => $this->resource->created_at,
            'deactivated_at' => $this->resource->deactivated_at,
            'description' => $this->resource->description,
            'id' => $this->resource->sqid,
            'name' => $this->resource->name,
            'sort_order' => $this->resource->sort_order,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```
