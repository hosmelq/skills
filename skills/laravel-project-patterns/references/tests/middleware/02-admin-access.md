# Middleware Tests: Administrator Access

Feature tests for an email allowlist: guests and authenticated users outside the list receive 403; an authenticated listed user receives 200. `login($user)` authenticates the supplied fixture.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\User;
use Illuminate\Support\Facades\Route;

it('forbids guests', function (): void {
    Route::middleware('admin')->get('/_test', fn (): string => 'ok');

    $response = get('/_test');

    $response->assertForbidden();
});

it('forbids non-admin users', function (): void {
    config(['admin.emails' => ['owner@example.com']]);

    Route::middleware('admin')->get('/_test', fn (): string => 'ok');

    $user = User::factory()->createOne(['email' => 'user@example.com']);

    login($user);

    $response = get('/_test');

    $response->assertForbidden();
});

it('allows admins to proceed', function (): void {
    config(['admin.emails' => ['admin@example.com']]);

    Route::middleware('admin')->get('/_test', fn (): string => 'ok');

    $user = User::factory()->createOne(['email' => 'admin@example.com']);

    login($user);

    $response = get('/_test');

    $response->assertOk();
});
```
