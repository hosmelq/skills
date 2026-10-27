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
