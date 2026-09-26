# Models: Defaults and Casts

Implement Eloquent model code for settings defaults and enum casts, booleans, integers, nullable decimal strings, arrays and coordinates. New instances receive the model's defaults.

Enum-case defaults and the typed constant follow the inspected Laravel 13 model contract. Omitted nullable values remain null; decimal precision does not make the value a float.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AssignmentMode;
use App\Enums\CodeAlphabet;
use App\Enums\CurrencyCode;
use App\Enums\UnitSystem;
use App\Enums\WeightUnit;
use Illuminate\Database\Eloquent\Model;
use Override;

class Team extends Model
{
    public const int DEFAULT_CODE_LENGTH = 6;

    #[Override]
    protected $attributes = [
        'assignment_mode' => AssignmentMode::RequiresApproval,
        'cabinets_enabled' => false,
        'code_format_alphabet_type' => CodeAlphabet::Alphanumeric,
        'code_format_length' => self::DEFAULT_CODE_LENGTH,
        'currency_code' => CurrencyCode::USD,
        'unit_system' => UnitSystem::Imperial,
        'weight_unit' => WeightUnit::Pounds,
        'work_orders_enabled' => false,
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'assignment_mode' => AssignmentMode::class,
            'cabinets_enabled' => 'boolean',
            'code_format_alphabet_type' => CodeAlphabet::class,
            'code_format_length' => 'integer',
            'currency_code' => CurrencyCode::class,
            'unit_system' => UnitSystem::class,
            'weight_unit' => WeightUnit::class,
            'work_orders_enabled' => 'boolean',
        ];
    }
}
```

Use scale 2 for monetary values and scale 4 for measurements only when those are the inspected column contracts.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CurrencyCode;
use App\Enums\LengthUnit;
use App\Enums\WeightUnit;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read null|string $height
 * @property-read int $quantity
 * @property-read null|string $unit_value
 */
class WorkOrderLine extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'currency_code' => CurrencyCode::class,
            'dimension_unit' => LengthUnit::class,
            'height' => 'decimal:4',
            'length' => 'decimal:4',
            'quantity' => 'integer',
            'unit_value' => 'decimal:2',
            'weight' => 'decimal:4',
            'weight_unit' => WeightUnit::class,
            'width' => 'decimal:4',
        ];
    }
}
```

Keep independent flags, enum defaults and scalar cast forms distinct.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CountryCode;
use Illuminate\Database\Eloquent\Model;
use Override;

class MemberAddress extends Model
{
    #[Override]
    protected $attributes = ['is_default' => false];

    #[Override]
    protected function casts(): array
    {
        return [
            'country_code' => CountryCode::class,
            'is_default' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Model;
use Override;

class Enrollment extends Model
{
    #[Override]
    protected $attributes = ['status' => EnrollmentStatus::Pending];

    #[Override]
    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'status' => EnrollmentStatus::class,
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;

class Facility extends Model
{
    #[Override]
    protected function casts(): array
    {
        return ['opening_hours' => 'array'];
    }
}
```
