<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WeightUnit;
use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\PetFactory;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property-read int $id
 * @property-read int $client_id
 * @property-read int $clinic_id
 * @property-read null|int $body_condition_score
 * @property-read null|CarbonImmutable $deceased_at
 * @property-read null|string $deceased_reason
 * @property-read string $name
 * @property-read null|CarbonImmutable $registered_at
 * @property-read null|string $weight
 * @property-read null|WeightUnit $weight_unit
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|CarbonImmutable $deleted_at
 * @property-read Client $client
 * @property-read Clinic $clinic
 * @property-read Collection<int, Visit> $visits
 */
final class Pet extends Model implements HasAuditLogIdentity
{
    /** @use HasFactory<PetFactory> */
    use HasFactory;

    use HasSqid;
    use SoftDeletes;

    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->name,
        );
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return HasMany<Visit, $this>
     */
    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'deceased_at' => 'datetime',
            'registered_at' => 'datetime',
            'weight' => 'decimal:4',
            'weight_unit' => WeightUnit::class,
        ];
    }
}
