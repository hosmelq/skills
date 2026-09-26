# HTTP Resources: Code and Label Fields

Expose the stored code and nullable label, public identifier and cast lifecycle timestamps. This scalar-only shape has no conditional relationships.

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
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```
