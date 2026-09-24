# Authentication Tests: Signed Identity Token Fixture

Test-only Apple JWT builder for controller login examples: generate an ephemeral RSA key, sign configurable claims and expose matching JWKS for HTTP fakes. Supports audience, issuer, expiry, nonce, email and subject variants without committed key files.

Place under the suite's autoloaded support namespace; these examples use `Tests\Support`. Requires the application's `firebase/php-jwt` and PHP OpenSSL. Set `services.apple.client_id` to a nonempty test client ID such as `com.example.demo`.

```php
<?php

declare(strict_types=1);

namespace Tests\Support;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Config;
use RuntimeException;

final class SignedIdentityToken
{
    private const KEY_ID = 'sample-identity-key';

    private static ?string $privateKey = null;

    private static array $publicKey = [];

    public static function issue(array $overrides = []): array
    {
        self::prepareKey();

        $payload = array_merge([
            'aud' => Config::string('services.apple.client_id'),
            'email' => 'casey@example.com',
            'exp' => now()->addHour()->timestamp,
            'iat' => now()->timestamp,
            'iss' => 'https://appleid.apple.com',
            'nonce' => 'sample-nonce',
            'sub' => 'identity-123',
        ], $overrides);

        return [
            'jwks' => ['keys' => [self::$publicKey]],
            'payload' => $payload,
            'token' => JWT::encode($payload, self::$privateKey, 'RS256', self::KEY_ID),
        ];
    }

    private static function prepareKey(): void
    {
        if (self::$privateKey !== null) {
            return;
        }

        $key = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($key === false || ! openssl_pkey_export($key, $privateKey)) {
            throw new RuntimeException('Cannot generate the test signing key.');
        }

        $details = openssl_pkey_get_details($key);

        if ($details === false || ! isset($details['rsa'])) {
            throw new RuntimeException('Cannot read the test public key.');
        }

        self::$publicKey = [
            'alg' => 'RS256',
            'e' => self::base64Url($details['rsa']['e']),
            'kid' => self::KEY_ID,
            'kty' => 'RSA',
            'n' => self::base64Url($details['rsa']['n']),
            'use' => 'sig',
        ];
        self::$privateKey = $privateKey;
    }

    private static function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
```

The key stays in memory for this PHP process. Each call accepts claim overrides; invalid-claim tests still use a correctly signed token, isolating claim validation from signature failure.
