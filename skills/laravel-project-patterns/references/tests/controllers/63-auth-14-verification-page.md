# Authentication Tests: Verification Page

Pest authenticated browser GET: an unverified account receives the email verification Inertia page and HTTP 200. This example covers the unverified-user prompt.

`login($user)` authenticates the given unverified user; use the suite helper that accepts an existing user.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use App\Models\User;
use Inertia\Testing\AssertableInertia;

it('shows the verification page', function (): void {
    $user = User::factory()->unverified()->createOne();

    login($user);

    $response = get(route('verification.notice'));

    $response->assertOk()
        ->assertInertia(function (AssertableInertia $page): void {
            $page->component('auth/VerifyEmail');
        });
});
```
