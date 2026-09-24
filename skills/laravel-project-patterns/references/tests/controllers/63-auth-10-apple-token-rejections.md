# Authentication Tests: Apple Token Rejections

POST JSON Apple login: reject unverifiable tokens, wrong audience, expired tokens, wrong issuer and mismatched nonce using signed JWTs and a fake JWKS response.

Use the [signed token fixture](63-auth-08-apple-token-fixture.md) if the suite lacks an equivalent builder. Configure `services.apple.client_id` for the test application.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use Illuminate\Support\Facades\Http;
use Tests\Support\SignedIdentityToken;

it('rejects an unverifiable identity token', function (): void {
    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response(['keys' => []]),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'id_token' => 'invalid-token',
        'nonce' => 'sample-nonce',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Unable to verify the supplied Apple credentials.',
        ]);
});

it('rejects an identity token for another audience', function (): void {
    $jwt = SignedIdentityToken::issue([
        'aud' => 'different-client-id',
    ]);

    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response($jwt['jwks']),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'id_token' => $jwt['token'],
        'nonce' => 'sample-nonce',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Unable to verify the supplied Apple credentials.',
        ]);
});

it('rejects an expired identity token', function (): void {
    $jwt = SignedIdentityToken::issue([
        'exp' => now()->subMinute()->timestamp,
    ]);

    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response($jwt['jwks']),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'id_token' => $jwt['token'],
        'nonce' => 'sample-nonce',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Unable to verify the supplied Apple credentials.',
        ]);
});

it('rejects an identity token from another issuer', function (): void {
    $jwt = SignedIdentityToken::issue([
        'iss' => 'https://example.com',
    ]);

    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response($jwt['jwks']),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'id_token' => $jwt['token'],
        'nonce' => 'sample-nonce',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Unable to verify the supplied Apple credentials.',
        ]);
});

it('rejects an identity token with a mismatched nonce', function (): void {
    $jwt = SignedIdentityToken::issue([
        'nonce' => 'different-nonce',
    ]);

    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response($jwt['jwks']),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'id_token' => $jwt['token'],
        'nonce' => 'sample-nonce',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Unable to verify the supplied Apple credentials.',
        ]);
});
```
