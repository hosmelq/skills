<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MembershipRole;
use Carbon\CarbonImmutable;
use Database\Factories\InvitationFactory;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $clinic_id
 * @property-read int $invited_by_user_id
 * @property-read null|CarbonImmutable $accepted_at
 * @property-read string $email
 * @property-read CarbonImmutable $expires_at
 * @property-read MembershipRole $role
 * @property-read string $token
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Clinic $clinic
 * @property-read User $invitedByUser
 */
final class Invitation extends Model implements HasAuditLogIdentity
{
    /** @use HasFactory<InvitationFactory> */
    use HasFactory;

    public static function findByToken(string $token): null|self
    {
        return self::query()
            ->where('token', hash('sha256', $token))
            ->whereNull('accepted_at')
            ->first();
    }


    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->email,
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
     * @return BelongsTo<User, $this>
     */
    public function invitedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isBefore(now());
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
            'role' => MembershipRole::class,
        ];
    }

    /**
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value): string => str($value)->lower()->toString());
    }
}
