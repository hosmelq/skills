# Models: Accessors and Mutators

Add a computed attribute derived from other attributes, or normalize a value when it is assigned, with `Attribute` methods and unit tests for each result.

- List a computed attribute in `@property-read` after the timestamps, with the other attributes that have no column, alphabetically.
- When the model imports `Illuminate\Support\Collection` for an accessor, import Eloquent's collection as `EloquentCollection` for its relations.
- Normalize on assignment when every write must store the same form.
- Test each fallback branch of a computed attribute and the normalized value after assignment.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Override;
use Propaganistas\LaravelPhone\Casts\E164PhoneNumberCast;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * @property-read null|string $email
 * @property-read null|string $first_name
 * @property-read null|string $last_name
 * @property-read null|PhoneNumber $phone_number
 * @property-read string $display_name
 * @property-read EloquentCollection<int, Pet> $pets
 */
final class Client extends Model implements HasAuditLogIdentity
{
    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->display_name,
        );
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
```

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $email
 */
final class Invitation extends Model
{
    /**
     * @return Attribute<string, string>
     */
    protected function email(): Attribute
    {
        return Attribute::make(set: fn (string $value): string => str($value)->lower()->toString());
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Models\Client;

it('builds a display name from name, email, or phone number', function (): void {
    $emailClient = new Client([
        'email' => 'john@example.com',
        'phone_number' => '+505 8888 8888',
    ]);
    $namedClient = new Client([
        'email' => 'john@example.com',
        'first_name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '+505 8888 8888',
    ]);
    $phoneClient = new Client([
        'phone_number' => '+505 8888 8888',
    ]);

    expect($namedClient->display_name)->toBe('John Doe')
        ->and($emailClient->display_name)->toBe('john@example.com')
        ->and($phoneClient->display_name)->toBe('+50588888888');
});
```

```php
<?php

declare(strict_types=1);

use App\Models\Invitation;

it('normalizes email addresses when assigned', function (): void {
    $invitation = new Invitation();

    $invitation->email = 'Mariela@Example.com';

    expect($invitation->email)->toBe('mariela@example.com');
});
```
