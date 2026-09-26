# Models: Phone Casts and Display Name

Implement raw or E164 phone storage and a computed display_name accessor: filled name parts, then email, then formatted phone, then an empty string.

The region list comes from the inspected enum API. `??` preserves an empty email instead of falling through to the phone. Both phone casts return a `PhoneNumber` object when set.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CountryCode;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Override;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * @property-read string $display_name
 * @property-read null|string $email
 * @property-read null|string $first_name
 * @property-read null|string $last_name
 * @property-read null|PhoneNumber $phone_number
 */
class Member extends Model
{
    #[Override]
    protected function casts(): array
    {
        return [
            'phone_number' => E164PhoneNumberCast::class.':'.implode(',', CountryCode::values()),
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
```

A fixed region is a separate contract. `RawPhoneNumberCast` preserves raw storage; substitute the project’s actual region.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Override;
use Propaganistas\LaravelPhone\Casts\RawPhoneNumberCast;

class Team extends Model
{
    #[Override]
    protected function casts(): array
    {
        return ['contact_phone_number' => RawPhoneNumberCast::class.':US'];
    }
}
```
