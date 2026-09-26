# HTTP Resources: Rounding Settings

Return country/currency, rounding enum and cast minimum/increment values. Preserve decimal strings and nulls; this shape excludes the parent relation.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PlanRule;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property PlanRule $resource
 */
class PlanRuleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'country_code' => $this->resource->country_code,
            'created_at' => $this->resource->created_at,
            'currency_code' => $this->resource->currency_code,
            'id' => $this->resource->sqid,
            'minimum_chargeable_weight' => $this->resource->minimum_chargeable_weight,
            'name' => $this->resource->name,
            'rounding_increment' => $this->resource->rounding_increment,
            'rounding_mode' => $this->resource->rounding_mode,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```
