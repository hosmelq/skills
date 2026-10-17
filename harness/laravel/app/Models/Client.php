<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\ClientFactory;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Override;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * @property-read int $id
 * @property-read int $clinic_id
 * @property-read null|string $email
 * @property-read null|string $first_name
 * @property-read null|string $last_name
 * @property-read null|PhoneNumber $phone_number
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|CarbonImmutable $deleted_at
 * @property-read string $display_name
 * @property-read EloquentCollection<int, ClientAddress> $addresses
 * @property-read Clinic $clinic
 * @property-read null|ClientAddress $defaultAddress
 * @property-read EloquentCollection<int, Pet> $pets
 */
final class Client extends Model implements HasAuditLogIdentity
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory;

    use HasSqid;
    use SoftDeletes;

    /**
     * @return HasMany<ClientAddress, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class)
            ->withAttributes(['clinic_id' => $this->clinic_id], asConditions: false);
    }

    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->display_name,
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
     * @return HasOne<ClientAddress, $this>
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(ClientAddress::class)->where('is_default', true);
    }

    /**
     * @return HasMany<Pet, $this>
     */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class)
            ->withAttributes(['clinic_id' => $this->clinic_id], asConditions: false);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'phone_number' => E164PhoneNumberCast::class,
        ];
    }

    /**
     * @return Attribute<string, never>
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(function (): string {
            $fullName = Collection::make([$this->first_name, $this->last_name])
                ->filter(fn (null|string $value): bool => filled($value))
                ->implode(' ');

            return $fullName !== ''
                ? $fullName
                : $this->email ?? $this->phone_number?->formatE164() ?? '';
        });
    }
}
