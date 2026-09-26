# Action Inputs: Relation IDs and Measurements

Required relation IDs, nullable optional relation IDs, enum units and integer quantities in action inputs. Scalar IDs are resolved inside the action; they are not model instances.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Cabinets\Inputs;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateCabinetInput extends Data
{
    public function __construct(
        public readonly null|Optional|string $label,
        public readonly int $servicePlanId,
    ) {
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderLines\Inputs;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreateWorkOrderLineInput extends Data
{
    public function __construct(
        public readonly null|CurrencyCode|Optional $currencyCode,
        public readonly null|Optional|string $declaredUnitValue,
        public readonly string $description,
        public readonly null|LengthUnit|Optional $dimensionUnit,
        public readonly null|Optional|string $height,
        public readonly null|int|Optional $itemGroupId,
        public readonly null|Optional|string $length,
        public readonly int $quantity,
        public readonly null|Optional|string $weight,
        public readonly null|Optional|WeightUnit $weightUnit,
        public readonly null|Optional|string $width,
    ) {
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\WorkOrderLines\Inputs;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class UpdateWorkOrderLineInput extends Data
{
    public function __construct(
        public readonly null|CurrencyCode|Optional $currencyCode,
        public readonly null|Optional|string $declaredUnitValue,
        public readonly Optional|string $description,
        public readonly null|LengthUnit|Optional $dimensionUnit,
        public readonly null|Optional|string $height,
        public readonly null|int|Optional $itemGroupId,
        public readonly null|Optional|string $length,
        public readonly int|Optional $quantity,
        public readonly null|Optional|string $weight,
        public readonly null|Optional|WeightUnit $weightUnit,
        public readonly null|Optional|string $width,
    ) {
    }
}
```
