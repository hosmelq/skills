# Resources: Model Resource

Serialize a model for pages and APIs with a JSON resource named after the model, so `$model->toResource()` finds it.

- Expose the Sqid as `id` and never the numeric key or foreign keys.
- Format phone numbers as E.164; enums and dates serialize themselves.
- List keys alphabetically.
- Test in the integration suite by decoding `toJson()` and comparing the whole array.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property Client $resource
 */
final class ClientResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'created_at' => $this->resource->created_at,
            'display_name' => $this->resource->display_name,
            'email' => $this->resource->email,
            'first_name' => $this->resource->first_name,
            'id' => $this->resource->sqid,
            'last_name' => $this->resource->last_name,
            'phone_number' => $this->resource->phone_number?->formatE164(),
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Models\Client;

it('formats resource correctly', function (): void {
    $client = Client::factory()->createOne();
    $resource = json_decode($client->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'created_at' => $client->created_at->toJSON(),
        'display_name' => $client->display_name,
        'email' => $client->email,
        'first_name' => $client->first_name,
        'id' => $client->sqid,
        'last_name' => $client->last_name,
        'phone_number' => $client->phone_number?->formatE164(),
        'updated_at' => $client->updated_at->toJSON(),
    ]);
});
```
