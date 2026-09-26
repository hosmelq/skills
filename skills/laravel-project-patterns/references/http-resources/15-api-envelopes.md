# HTTP Resources: JSON:API Attributes, Identifier and Type

Use JsonApiResource for the inspected JSON:API contract: attributes, public Sqid and explicit type. These examples provide status or contact attributes; the API phone is E.164. They differ from ordinary JsonResource output.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Override;

/**
 * @property Enrollment $resource
 */
class EnrollmentResource extends JsonApiResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toAttributes(Request $request): array
    {
        return [
            'status' => $this->resource->status,
        ];
    }

    #[Override]
    public function toId(Request $request): string
    {
        return $this->resource->sqid;
    }

    #[Override]
    public function toType(Request $request): string
    {
        return 'enrollments';
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources\Api;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Override;

/**
 * @property Team $resource
 */
class TeamResource extends JsonApiResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toAttributes(Request $request): array
    {
        return [
            'contact_email' => $this->resource->contact_email,
            'contact_phone_number' => $this->resource->contact_phone_number?->formatE164(),
            'name' => $this->resource->name,
        ];
    }

    #[Override]
    public function toId(Request $request): string
    {
        return $this->resource->sqid;
    }

    #[Override]
    public function toType(Request $request): string
    {
        return 'teams';
    }
}
```
