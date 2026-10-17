<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MembershipRole;
use Carbon\CarbonImmutable;
use Database\Factories\InvitationFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
 */
final class Invitation extends Model
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
