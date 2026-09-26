# HTTP Resources: Required Related Records

Embed the member and the requesting user under distinct keys. Direct relationship access can query when not preloaded; preserve it only when these relations are part of the inspected response.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property Enrollment $resource
 */
class EnrollmentResource extends JsonResource
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
            'member' => MemberResource::make($this->resource->member),
            'requested_at' => $this->resource->requested_at,
            'requested_by_user' => UserResource::make($this->resource->requestedByUser),
            'status' => $this->resource->status,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```
