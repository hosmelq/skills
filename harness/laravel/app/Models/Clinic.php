<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\ClinicFactory;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;
use Spatie\Sluggable\Attributes\Sluggable;

/**
 * @property-read int $id
 * @property-read int $owner_id
 * @property-read bool $accepts_emergencies
 * @property-read string $consultation_fee
 * @property-read CountryCode $country_code
 * @property-read CurrencyCode $currency_code
 * @property-read null|float $latitude
 * @property-read string $name
 * @property-read null|array<string, mixed> $opening_hours
 * @property-read null|PhoneNumber $phone_number
 * @property-read string $slug
 * @property-read null|CarbonImmutable $verified_at
 * @property-read int $visit_duration_minutes
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read Collection<int, Client> $clients
 * @property-read User $owner
 * @property-read Collection<int, Room> $rooms
 * @property-read Collection<int, User> $users
 * @property-read Collection<int, VisitStatus> $visitStatuses
 */
#[RouteKey('slug')]
#[Sluggable(from: 'name', to: 'slug', onUpdate: false)]
final class Clinic extends Model implements HasAuditLogIdentity
{
    /** @use HasFactory<ClinicFactory> */
    use HasFactory;

    use HasSqid;

    #[Override]
    protected $attributes = [
        'accepts_emergencies' => false,
        'currency_code' => CurrencyCode::USD,
        'visit_duration_minutes' => 30,
    ];

    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->name,
        );
    }

    /**
     * @return HasMany<Client, $this>
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return HasMany<Room, $this>
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * @return BelongsToMany<User, $this, Membership, 'membership'>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->as('membership')
            ->using(Membership::class)
            ->withTimestamps();
    }

    /**
     * @return HasMany<VisitStatus, $this>
     */
    public function visitStatuses(): HasMany
    {
        return $this->hasMany(VisitStatus::class);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'accepts_emergencies' => 'boolean',
            'consultation_fee' => 'decimal:2',
            'country_code' => CountryCode::class,
            'currency_code' => CurrencyCode::class,
            'latitude' => 'float',
            'opening_hours' => 'array',
            'phone_number' => E164PhoneNumberCast::class,
            'verified_at' => 'datetime',
            'visit_duration_minutes' => 'integer',
        ];
    }
}
