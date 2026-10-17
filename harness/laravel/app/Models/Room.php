<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasDeactivation;
use App\Models\Concerns\HasSqid;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read int $id
 * @property-read int $clinic_id
 * @property-read int $capacity
 * @property-read string $code
 * @property-read null|CarbonImmutable $deactivated_at
 * @property-read string $name
 * @property-read string $normalized_code
 * @property-read null|string $notes
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Clinic $clinic
 */
final class Room extends Model
{
    use HasDeactivation;

    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    use HasSqid;

    public static function normalizeCode(string $code): string
    {
        return str($code)
            ->upper()
            ->replaceMatches('/[^A-Z0-9]/', '')
            ->toString();
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'deactivated_at' => 'datetime',
        ];
    }
}
