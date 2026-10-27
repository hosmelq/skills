# Resources: Nested Resource

Include a related record through its own resource, keeping the key with a `null` value when the relation is empty, and derive values the client needs, such as an avatar URL.

- Read the loaded relation; never resolve defaults or switch state while serializing.

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property Clinic $resource
 */
final class ClinicResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'accepts_emergencies' => $this->resource->accepts_emergencies,
            'consultation_fee' => $this->resource->consultation_fee,
            'country_code' => $this->resource->country_code,
            'created_at' => $this->resource->created_at,
            'currency_code' => $this->resource->currency_code,
            'id' => $this->resource->sqid,
            'name' => $this->resource->name,
            'phone_number' => $this->resource->phone_number?->formatE164(),
            'slug' => $this->resource->slug,
            'updated_at' => $this->resource->updated_at,
            'visit_duration_minutes' => $this->resource->visit_duration_minutes,
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * @property User $resource
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'avatar_url' => sprintf(
                'https://www.gravatar.com/avatar/%s?d=mp',
                str($this->resource->email)->trim()->lower()->hash('sha256'),
            ),
            'created_at' => $this->resource->created_at,
            'current_clinic' => $this->when(
                ($currentClinic = $this->resource->currentClinic) instanceof Clinic,
                fn (): ClinicResource => ClinicResource::make($currentClinic),
                null,
            ),
            'email' => $this->resource->email,
            'first_name' => $this->resource->first_name,
            'id' => $this->resource->sqid,
            'is_email_verified' => $this->resource->email_verified_at !== null,
            'last_name' => $this->resource->last_name,
            'name' => $this->resource->name,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Models\Clinic;
use App\Models\User;

it('formats resource correctly', function (): void {
    $clinic = Clinic::factory()->createOne([
        'name' => 'Downtown Clinic',
        'slug' => 'downtown-clinic',
    ]);
    $user = User::factory()->withClinic($clinic)->createOne([
        'email' => ' TEST@example.com ',
        'first_name' => 'Jane',
        'last_name' => 'Doe',
    ]);
    $resource = json_decode($user->toResource()->toJson(), true);

    expect($resource)->toEqual([
        'avatar_url' => sprintf(
            'https://www.gravatar.com/avatar/%s?d=mp',
            '973dfe463ec85785f5f95af5ba3906eedb2d931c24e69824a89ea65dba4e813b',
        ),
        'created_at' => $user->created_at->toJSON(),
        'current_clinic' => [
            'accepts_emergencies' => false,
            'consultation_fee' => '25.00',
            'country_code' => $clinic->country_code->value,
            'created_at' => $clinic->created_at->toJSON(),
            'currency_code' => 'USD',
            'id' => $clinic->sqid,
            'name' => 'Downtown Clinic',
            'phone_number' => $clinic->phone_number?->formatE164(),
            'slug' => 'downtown-clinic',
            'updated_at' => $clinic->updated_at->toJSON(),
            'visit_duration_minutes' => 30,
        ],
        'email' => $user->email,
        'first_name' => 'Jane',
        'id' => $user->sqid,
        'is_email_verified' => true,
        'last_name' => 'Doe',
        'name' => 'Jane Doe',
        'updated_at' => $user->updated_at->toJSON(),
    ]);
});

it('includes null current clinic when user has none', function (): void {
    $user = User::factory()->createOne();
    $resource = json_decode($user->toResource()->toJson(), true);

    expect($resource['current_clinic'])->toBeNull();
});
```
