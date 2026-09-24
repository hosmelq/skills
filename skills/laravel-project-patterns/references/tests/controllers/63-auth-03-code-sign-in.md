# Authentication Tests: Verification Code Sign-In

Pest POST JSON email login: a valid one-time code creates a verified account or verifies an existing unverified account. Preserve consumed-code timestamps and the new-account token response.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Models\OneTimePassword;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

it('creates and authenticates a new account', function (): void {
    $otp = OneTimePassword::factory()->createOne([
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);

    $response = postJson(route('api.auth.email.login'), [
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json): void {
            $json->whereType('access_token', 'string')
                ->whereType('user.id', 'string');
        });

    $otp->refresh();

    $user = User::query()->where('email', 'casey.sample@gmail.com')->sole();

    expect($otp->used_at)->not->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull();
});

it('authenticates and verifies an existing account', function (): void {
    $otp = OneTimePassword::factory()->createOne([
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);
    $user = User::factory()->unverified()->createOne([
        'email' => 'casey.sample@gmail.com',
    ]);

    $response = postJson(route('api.auth.email.login'), [
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);

    $response->assertOk();

    $otp->refresh();
    $user->refresh();

    expect($otp->used_at)->not->toBeNull()
        ->and($user->email_verified_at)->not->toBeNull();
});
```
