# HTTP Resources: Settings and National Phone Format

Keep settings, enum values and independent feature flags explicit. This web resource uses national phone formatting; the API shape uses E.164.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property Team $resource
 */
class TeamResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'assignment_mode' => $this->resource->assignment_mode,
            'cabinets_enabled' => $this->resource->cabinets_enabled,
            'code_format_alphabet_type' => $this->resource->code_format_alphabet_type,
            'code_format_length' => $this->resource->code_format_length,
            'code_format_prefix' => $this->resource->code_format_prefix,
            'contact_email' => $this->resource->contact_email,
            'contact_phone_number' => $this->resource->contact_phone_number?->formatNational(),
            'country_code' => $this->resource->country_code,
            'created_at' => $this->resource->created_at,
            'currency_code' => $this->resource->currency_code,
            'id' => $this->resource->sqid,
            'name' => $this->resource->name,
            'slug' => $this->resource->slug,
            'timezone' => $this->resource->timezone,
            'unit_system' => $this->resource->unit_system,
            'updated_at' => $this->resource->updated_at,
            'weight_unit' => $this->resource->weight_unit,
            'work_orders_enabled' => $this->resource->work_orders_enabled,
        ];
    }
}
```
