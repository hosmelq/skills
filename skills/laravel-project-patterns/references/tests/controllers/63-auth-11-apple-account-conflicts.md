# Authentication Tests: Apple Account Conflicts

POST JSON Apple login: reject a registered email with no linked identity and an email linked to another subject. Preserve both fixtures despite their shared 422 response.

Use the [signed token fixture](63-auth-08-apple-token-fixture.md) if the suite lacks an equivalent builder. Configure `services.apple.client_id` for the test application.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Tests\Support\SignedIdentityToken;

it('rejects a registered email without a linked identity', function (): void {
    User::factory()->createOne([
        'email' => 'casey@example.com',
    ]);

    $jwt = SignedIdentityToken::issue([
        'email' => 'casey@example.com',
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
            'id_token' => 'Cannot sign in with Apple for this account. Use email to continue.',
        ]);
});

it('rejects an email linked to another identity', function (): void {
    User::factory()->createOne([
        'apple_id' => 'other-identity',
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

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Cannot sign in with Apple for this account. Use email to continue.',
        ]);
});
```
