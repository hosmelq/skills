# Models: Authenticatable User

Define the application's authenticatable user model and its unit tests, keeping only the authentication, authorization and admin panel packages the project installs.

- `name` is a `PersonNameCast` over `first_name` and `last_name` with no column of its own, so it follows the timestamps in `@property-read`.
- Register the `user` alias in the enforced morph map.
- Tenant membership, ownership and switching follow the current tenant selection reference.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasSqid;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use HosmelQ\AuditLog\Contracts\HasAuditLogIdentity;
use HosmelQ\AuditLog\Support\AuditLogIdentity;
use HosmelQ\NameOfPerson\PersonName;
use HosmelQ\NameOfPerson\PersonNameCast;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read null|string $apple_email
 * @property-read null|string $apple_id
 * @property-read string $email
 * @property-read null|CarbonImmutable $email_verified_at
 * @property-read null|string $first_name
 * @property-read null|string $google_email
 * @property-read null|string $google_id
 * @property-read null|string $last_name
 * @property-read null|string $password
 * @property-read null|string $remember_token
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 * @property-read null|PersonName $name
 */
#[Hidden(['password', 'remember_token'])]
final class User extends Authenticatable implements FilamentUser, HasAuditLogIdentity, MustVerifyEmailContract
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use HasSqid;
    use MustVerifyEmail;
    use Notifiable;

    public function auditLogIdentity(): AuditLogIdentity
    {
        return new AuditLogIdentity(
            id: $this->id,
            type: $this->getMorphClass(),
            name: $this->email,
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return in_array($this->email, config()->array('vetdesk.admin.emails'), true);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'name' => PersonNameCast::class,
            'password' => 'hashed',
        ];
    }

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

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Users
    |--------------------------------------------------------------------------
    |
    | Define a small allowlist of admin users without persisting the role in
    | the database. Provide a comma-separated list of email addresses using
    | the ADMIN_EMAILS environment variable.
    |
    */

    'admin' => [
        'emails' => [
            ...array_filter(
                explode(',', (string) env('ADMIN_EMAILS', '')),
                static fn (string $email): bool => $email !== '',
            ),
        ],
    ],

];
```

```php
<?php

declare(strict_types=1);
use App\Models\User;

it('determines admin status based on config allowlist', function (): void {
    config(['vetdesk.admin.emails' => ['owner@example.com']]);

    $admin = new User(['email' => 'owner@example.com']);
    $user = new User(['email' => 'user@example.com']);

    expect($admin->isAdmin())->toBeTrue()->and($user->isAdmin())->toBeFalse();
});
```
