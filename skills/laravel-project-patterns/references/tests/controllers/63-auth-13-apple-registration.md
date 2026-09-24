# Authentication Tests: New Apple Identity

POST JSON Apple login: reject a new subject without an email claim, then create a verified account with submitted profile names. Preserve linked identity fields and token response/count.

Use the [signed token fixture](63-auth-08-apple-token-fixture.md) if the suite lacks an equivalent builder. Configure `services.apple.client_id` for the test application.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Support\SignedIdentityToken;

it('rejects a new identity without an email claim', function (): void {
    $jwt = SignedIdentityToken::issue([
        'email' => null,
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

it('creates and authenticates a new account', function (): void {
    $jwt = SignedIdentityToken::issue([
        'email' => 'casey@example.com',
        'sub' => 'identity-123',
    ]);

    Http::fake([
        'https://appleid.apple.com/auth/keys' => Http::response($jwt['jwks']),
    ]);

    $response = postJson(route('api.auth.apple.login'), [
        'first_name' => 'Casey',
        'id_token' => $jwt['token'],
        'last_name' => 'Reed',
        'nonce' => 'sample-nonce',
    ]);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json): void {
            $json->whereType('access_token', 'string')
                ->where('user.email', 'casey@example.com');
        });

    $user = User::query()->where('email', 'casey@example.com')->sole();

    expect($user)
        ->apple_email->toBe('casey@example.com')
        ->apple_id->toBe('identity-123')
        ->email->toBe('casey@example.com')
        ->email_verified_at->not->toBeNull()
        ->first_name->toBe('Casey')
        ->last_name->toBe('Reed')
        ->tokens->toHaveCount(1);
});
```
