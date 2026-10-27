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
