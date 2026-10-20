<?php

declare(strict_types=1);

namespace App\Actions\Clinics\Inputs;

use App\Enums\CountryCode;
use App\Enums\CurrencyCode;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateClinicInput extends Data
{
    public function __construct(
        public readonly string $consultationFee,
        public readonly CountryCode $countryCode,
        public readonly string $name,
        public readonly bool|Optional $acceptsEmergencies,
        public readonly CurrencyCode|Optional $currencyCode,
        public readonly null|Optional|string $phoneNumber,
        public readonly int|Optional $visitDurationMinutes,
    ) {}
}
