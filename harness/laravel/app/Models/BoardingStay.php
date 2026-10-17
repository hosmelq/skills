<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\BoardingStayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property-read int $id
 * @property-read int $clinic_id
 * @property-read int $pet_id
 * @property-read int $room_id
 * @property-read null|CarbonImmutable $ends_at
 * @property-read CarbonImmutable $starts_at
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|CarbonImmutable $deleted_at
 * @property-read Room $room
 */
final class BoardingStay extends Model
{
    /** @use HasFactory<BoardingStayFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'ends_at' => 'datetime',
            'starts_at' => 'datetime',
        ];
    }
}
