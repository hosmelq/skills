<?php

declare(strict_types=1);

namespace App\Models\World;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $country_id
 * @property-read int $state_id
 * @property-read string $country_code
 * @property-read string $name
 * @property-read State $state
 */
#[Connection('world')]
final class City extends Model
{
    /**
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
