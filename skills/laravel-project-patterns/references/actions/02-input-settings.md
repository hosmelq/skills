# Action Inputs: Optional Settings

Create and update settings inputs with required tenant identity, typed enum settings and optional boolean and integer values. Keep defaults in the model when omitted.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Teams\Inputs;

use App\Enums\AssignmentMode;
use App\Enums\CodeAlphabet;
use App\Enums\CountryCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateTeamInput extends Data
{
    public function __construct(
        public readonly AssignmentMode|Optional $assignmentMode,
        public readonly bool|Optional $cabinetsEnabled,
        public readonly CodeAlphabet|Optional $codeFormatAlphabetType,
        public readonly int|Optional $codeFormatLength,
        public readonly null|Optional|string $codeFormatPrefix,
        public readonly null|Optional|string $contactEmail,
        public readonly null|Optional|string $contactPhoneNumber,
        public readonly CountryCode $countryCode,
        public readonly string $name,
        public readonly string $timezone,
        public readonly Optional|UnitSystem $unitSystem,
        public readonly Optional|WeightUnit $weightUnit,
        public readonly bool|Optional $workOrdersEnabled,
    ) {
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Teams\Inputs;

use App\Enums\AssignmentMode;
use App\Enums\CodeAlphabet;
use App\Enums\CountryCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class UpdateTeamInput extends Data
{
    public function __construct(
        public readonly AssignmentMode|Optional $assignmentMode,
        public readonly bool|Optional $cabinetsEnabled,
        public readonly CodeAlphabet|Optional $codeFormatAlphabetType,
        public readonly int|Optional $codeFormatLength,
        public readonly null|Optional|string $codeFormatPrefix,
        public readonly null|Optional|string $contactEmail,
        public readonly null|Optional|string $contactPhoneNumber,
        public readonly CountryCode|Optional $countryCode,
        public readonly Optional|string $name,
        public readonly Optional|string $timezone,
        public readonly Optional|UnitSystem $unitSystem,
        public readonly Optional|WeightUnit $weightUnit,
        public readonly bool|Optional $workOrdersEnabled,
    ) {
    }
}
```
