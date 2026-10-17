# Models: Attribute Defaults

Give new model instances the default attribute values the application relies on before the record is saved, and test them on an unsaved instance.

- Declare defaults in `$attributes` with `#[Override]`; Rector adds that attribute to methods only.
- Sort keys alphabetically and keep a matching cast for each default.
- Use enum cases for enum columns. Extract a value to a public typed constant when other code also needs it.
- Keep the matching database default in the migration.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CurrencyCode;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read bool $accepts_emergencies
 * @property-read CurrencyCode $currency_code
 * @property-read int $visit_duration_minutes
 */
final class Clinic extends Model
{
    #[Override]
    protected $attributes = [
        'accepts_emergencies' => false,
        'currency_code' => CurrencyCode::USD,
        'visit_duration_minutes' => 30,
    ];

    #[Override]
    protected function casts(): array
    {
        return [
            'accepts_emergencies' => 'boolean',
            'currency_code' => CurrencyCode::class,
            'visit_duration_minutes' => 'integer',
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Enums\CurrencyCode;
use App\Models\Clinic;

it('sets model defaults', function (): void {
    $clinic = new Clinic();

    expect($clinic)
        ->accepts_emergencies->toBeFalse()
        ->currency_code->toBe(CurrencyCode::USD)
        ->visit_duration_minutes->toBe(30);
});
```
