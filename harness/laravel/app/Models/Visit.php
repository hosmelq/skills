<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\VisitFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $attending_veterinarian_id
 * @property-read int $clinic_id
 * @property-read int $pet_id
 * @property-read null|int $referring_veterinarian_id
 * @property-read int $room_id
 * @property-read null|string $notes
 * @property-read CarbonImmutable $scheduled_at
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read User $attendingVeterinarian
 * @property-read Clinic $clinic
 * @property-read Pet $pet
 * @property-read null|User $referringVeterinarian
 */
final class Visit extends Model
{
    /** @use HasFactory<VisitFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function attendingVeterinarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'attending_veterinarian_id');
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * @return BelongsTo<Pet, $this>
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function referringVeterinarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referring_veterinarian_id');
    }
}
