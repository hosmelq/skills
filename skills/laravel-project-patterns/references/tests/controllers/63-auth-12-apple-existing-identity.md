# Authentication Tests: Existing Apple Identity

Pest POST JSON Apple login: authenticate an existing subject, accept a missing email claim for that linked subject, and update provider email without changing the account email. Preserve user ID and token counts.

Use the [signed token fixture](63-auth-08-apple-token-fixture.md) if the suite lacks an equivalent builder. Configure `services.apple.client_id` for the test application.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Support\SignedIdentityToken;

it('authenticates an existing identity', function (): void {
    $user = User::factory()->createOne([
        'apple_email' => 'casey@example.com',
        'apple_id' => 'identity-123',
        'email' => 'casey@example.com',
    ]);

    $jwt = SignedIdentityToken::issue([
        'email' => 'casey@example.com',
        'sub' => 'identity-123',
    ]);

    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response($jwt['jwks']),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'id_token' => $jwt['token'],
        'nonce' => 'sample-nonce',
    ]);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json) use ($user): void {
            $json->whereType('access_token', 'string')
                ->where('user.id', $user->public_id);
        });

    expect($user->tokens)->toHaveCount(1);
});

it('authenticates an existing identity without an email claim', function (): void {
    $user = User::factory()->createOne([
        'apple_email' => 'casey@example.com',
        'apple_id' => 'identity-123',
        'email' => 'casey@example.com',
    ]);

    $jwt = SignedIdentityToken::issue([
        'email' => null,
        'sub' => 'identity-123',
    ]);

    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response($jwt['jwks']),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'id_token' => $jwt['token'],
        'nonce' => 'sample-nonce',
    ]);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json) use ($user): void {
            $json->whereType('access_token', 'string')
                ->where('user.id', $user->public_id);
        });

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'apple_email' => 'casey@example.com',
    ]);

    expect($user->tokens)->toHaveCount(1);
});

it('keeps the account email when the identity email changes', function (): void {
    $user = User::factory()->createOne([
        'apple_email' => 'casey@example.com',
        'apple_id' => 'identity-123',
        'email' => 'casey@example.com',
    ]);

    $jwt = SignedIdentityToken::issue([
        'email' => 'updated@example.com',
        'sub' => 'identity-123',
    ]);

    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response($jwt['jwks']),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'id_token' => $jwt['token'],
        'nonce' => 'sample-nonce',
    ]);

    $response->assertOk();

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'apple_email' => 'updated@example.com',
        'apple_id' => 'identity-123',
        'email' => 'casey@example.com',
    ]);

    expect($user->tokens)->toHaveCount(1);
});
```
