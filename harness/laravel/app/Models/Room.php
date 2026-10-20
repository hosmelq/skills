<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasDeactivation;
use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\RoomFactory;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
final class Room extends Model implements HasAuditLogIdentity
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

    #[Override]
    protected function casts(): array
    {
        return [
            'deactivated_at' => 'datetime',
        ];
    }
}
