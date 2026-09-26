# Models: Authentication and Panel Access

Implement an authenticatable user with verification, API tokens, notifications, hidden credentials, hashed passwords, a name value-object cast and strict configured administrator access.

Use the inspected name cast; the synthetic `PersonNameCast` returns `PersonName` from the model’s name fields. `Hidden` affects serialization. The allowlist comparison remains strict and case-sensitive.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Casts\PersonNameCast;
use App\Models\Concerns\HasSqid;
use App\ValueObjects\PersonName;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\HasApiTokens;
use Override;

/**
 * @property-read string $email
 * @property-read PersonName $name
 * @property-read string $password
 */
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, MustVerifyEmailContract
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasSqid;
    use MustVerifyEmail;
    use Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return in_array($this->email, Config::array('admin.emails'), true);
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
}
```
