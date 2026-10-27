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
