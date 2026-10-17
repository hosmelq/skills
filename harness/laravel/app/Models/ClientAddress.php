<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CountryCode;
use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\ClientAddressFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * @property-read int $id
 * @property-read int $clinic_id
 * @property-read int $client_id
 * @property-read string $address1
 * @property-read null|string $address2
 * @property-read string $city
 * @property-read null|string $company
 * @property-read CountryCode $country_code
 * @property-read null|string $first_name
 * @property-read bool $is_default
 * @property-read null|string $label
 * @property-read null|string $last_name
 * @property-read null|float $latitude
 * @property-read null|float $longitude
 * @property-read null|PhoneNumber $phone_number
 * @property-read null|string $postal_code
 * @property-read null|string $province_code
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|CarbonImmutable $deleted_at
 * @property-read Clinic $clinic
 * @property-read Client $client
 */
final class ClientAddress extends Model
{
    /** @use HasFactory<ClientAddressFactory> */
    use HasFactory;

    use HasSqid;
    use SoftDeletes;

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

    #[Override]
    protected function casts(): array
    {
        return [
            'country_code' => CountryCode::class,
            'is_default' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'phone_number' => E164PhoneNumberCast::class,
        ];
    }
}
