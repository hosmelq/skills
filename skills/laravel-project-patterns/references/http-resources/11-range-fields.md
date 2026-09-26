# HTTP Resources: Range Bounds and Amount

Return the named bounds and rate using their model casts. Keep a nullable maximum distinct from zero; expose Sqid without internal relation IDs.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PlanRate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property PlanRate $resource
 */
class PlanRateResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->resource->created_at,
            'id' => $this->resource->sqid,
            'maximum_weight' => $this->resource->maximum_weight,
            'minimum_weight' => $this->resource->minimum_weight,
            'name' => $this->resource->name,
            'rate' => $this->resource->rate,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```
