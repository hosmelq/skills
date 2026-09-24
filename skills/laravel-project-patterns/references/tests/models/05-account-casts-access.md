# Model Tests: Account Casts and Allowlist

In-memory account casts for immutable dates, a person-name value object and hashed passwords; administrator predicates use a configured email allowlist.

The fictional `PersonName` type is the inspected application cast. `Hash` and `config()` require the application bootstrap even without database rows.

```php
<?php

declare(strict_types=1);

use App\Models\User;
use App\ValueObjects\PersonName;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Hash;

it('correctly casts attributes', function (): void {
    $user = new User([
        'created_at' => '2026-01-15 02:53:10',
        'first_name' => 'John',
        'email_verified_at' => '2026-01-15 02:53:10',
        'password' => 'password',
        'updated_at' => '2026-01-15 02:53:10',
    ]);
    $passwordNeedsRehash = Hash::needsRehash($user->password);

    expect($user)
        ->created_at->toBeInstanceOf(CarbonImmutable::class)
        ->email_verified_at->toBeInstanceOf(CarbonImmutable::class)
        ->name->toBeInstanceOf(PersonName::class)
        ->updated_at->toBeInstanceOf(CarbonImmutable::class)
        ->and($passwordNeedsRehash)->toBeFalse();
});

it('determines administrator status from the configured allowlist', function (): void {
    config(['admin.emails' => ['owner@example.com']]);

    $admin = new User(['email' => 'owner@example.com']);
    $user = new User(['email' => 'user@example.com']);

    expect($admin->isAdmin())->toBeTrue()
        ->and($user->isAdmin())->toBeFalse();
});
```
