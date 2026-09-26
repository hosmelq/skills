# Action Inputs: Required, Nullable and Omitted Fields

Spatie Data input classes for mapped snake_case fields, required create values and partial updates with nullable strings, enums and coordinates. Optional preserves omission; explicit null clears a nullable field.

Use the inspected field list. `MapName` maps input and output; `transform()` omits `Optional` while keeping supplied null, false, zero and empty strings. Construct through `::from()` so missing Optional fields are initialized.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Facilities\Inputs;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateFacilityInput extends Data
{
    public function __construct(
        public readonly null|Optional|string $address1,
        public readonly null|Optional|string $address2,
        public readonly null|Optional|string $city,
        public readonly CountryCode $countryCode,
        public readonly null|float|Optional $latitude,
        public readonly null|float|Optional $longitude,
        public readonly string $name,
        public readonly null|Optional|string $phoneNumber,
        public readonly null|Optional|string $postalCode,
        public readonly null|Optional|string $provinceCode,
        public readonly FacilityType $type,
    ) {
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Facilities\Inputs;

use App\Enums\CountryCode;
use App\Enums\FacilityType;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class UpdateFacilityInput extends Data
{
    public function __construct(
        public readonly null|Optional|string $address1,
        public readonly null|Optional|string $address2,
        public readonly null|Optional|string $city,
        public readonly CountryCode|Optional $countryCode,
        public readonly null|float|Optional $latitude,
        public readonly null|float|Optional $longitude,
        public readonly Optional|string $name,
        public readonly null|Optional|string $phoneNumber,
        public readonly null|Optional|string $postalCode,
        public readonly null|Optional|string $provinceCode,
        public readonly FacilityType|Optional $type,
    ) {
    }
}
```
