# Action Inputs: Decimal Ranges

Decimal strings for create/update range inputs; omitted maximum is distinct from supplied null during updates. Keep persistence precision separate from floating-point comparison guards.

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans\Inputs;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class CreatePlanRateInput extends Data
{
    public function __construct(
        public readonly null|Optional|string $maximumWeight,
        public readonly string $minimumWeight,
        public readonly string $name,
        public readonly string $rate,
    ) {
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\ServicePlans\Inputs;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;

#[MapName(SnakeCaseMapper::class)]
final class UpdatePlanRateInput extends Data
{
    public function __construct(
        public readonly null|Optional|string $maximumWeight,
        public readonly Optional|string $minimumWeight,
        public readonly Optional|string $name,
        public readonly Optional|string $rate,
    ) {
    }
}
```
