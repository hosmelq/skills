# HTTP Resources: Conditional Assignment Relations

Keep code/label fields and independently expose loaded member and service-plan resources. Omit unloaded keys and preserve loaded null; the resource does not load historical parents itself.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Cabinet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property Cabinet $resource
 */
class CabinetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'code' => $this->resource->code,
            'created_at' => $this->resource->created_at,
            'deactivated_at' => $this->resource->deactivated_at,
            'id' => $this->resource->sqid,
            'label' => $this->resource->label,
            'member' => MemberResource::make($this->whenLoaded('member')),
            'service_plan' => ServicePlanResource::make($this->whenLoaded('servicePlan')),
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```
