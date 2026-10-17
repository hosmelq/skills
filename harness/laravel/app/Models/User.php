<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use HosmelQ\NameOfPerson\PersonName;
use HosmelQ\NameOfPerson\PersonNameCast;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read null|int $current_clinic_id
 * @property-read null|string $apple_email
 * @property-read null|string $apple_id
 * @property-read string $email
 * @property-read null|CarbonImmutable $email_verified_at
 * @property-read null|string $first_name
 * @property-read null|string $google_email
 * @property-read null|string $google_id
 * @property-read null|string $last_name
 * @property-read null|string $password
 * @property-read null|string $remember_token
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|PersonName $name
 * @property-read Collection<int, Clinic> $clinics
 * @property-read null|Clinic $currentClinic
 * @property-read Collection<int, Clinic> $ownedClinics
 */
#[Hidden(['password', 'remember_token'])]
final class User extends Authenticatable implements FilamentUser, HasAuditLogIdentity, MustVerifyEmailContract
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use HasSqid;
    use MustVerifyEmail;
    use Notifiable;

    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->email,
        );
    }

    public function belongsToClinic(null|Clinic $clinic): bool
    {
        if (! $clinic instanceof Clinic) {
            return false;
        }

        if ($this->ownsClinic($clinic)) {
            return true;
        }

        return $this->clinics->contains($clinic);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    /**
     * @return BelongsToMany<Clinic, $this, Membership, 'membership'>
     */
    public function clinics(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class)
            ->as('membership')
            ->using(Membership::class)
            ->withTimestamps();
    }

    /**
     * @return BelongsTo<Clinic, $this>
     */
    public function currentClinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class, 'current_clinic_id');
    }

    public function defaultClinic(): null|Clinic
    {
        return $this->currentClinic ?? $this->clinics()
            ->oldest('clinic_user.created_at')
            ->oldest('clinic_user.id')
            ->first();
    }

    public function isAdmin(): bool
    {
        return in_array($this->email, config()->array('vetdesk.admin.emails'), true);
    }

    public function isCurrentClinic(Clinic $clinic): bool
    {
        if ($this->currentClinic === null) {
            return false;
        }

        return $this->currentClinic->id === $clinic->id;
    }

    /**
     * @return HasMany<Clinic, $this>
     */
    public function ownedClinics(): HasMany
    {
        return $this->hasMany(Clinic::class, 'owner_id');
    }

    public function ownsClinic(null|Clinic $clinic): bool
    {
        if (! $clinic instanceof Clinic) {
            return false;
        }

        return $this->id === $clinic->owner_id;
    }

    public function switchClinic(Clinic $clinic): bool
    {
        if (! $this->belongsToClinic($clinic)) {
            return false;
        }

        $this->update([
            'current_clinic_id' => $clinic->id,
        ]);

        $this->setRelation('currentClinic', $clinic);

        setPermissionsTeamId($clinic->id);

        $this->unsetRelation('permissions')->unsetRelation('roles');

        return true;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'name' => PersonNameCast::class,
            'password' => 'hashed',
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
