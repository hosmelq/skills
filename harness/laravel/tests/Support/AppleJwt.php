<?php

declare(strict_types=1);

namespace Tests\Support;

use function Safe\file_get_contents;
use function Safe\openssl_pkey_get_details;
use function Safe\openssl_pkey_get_public;

use Firebase\JWT\JWT;

final class AppleJwt
{
    /**
     * Identifier assigned to the statically defined JWKS entry.
     */
    private const string KEY_ID = 'test-apple-signing-key';

    /**
     * Issue a signed Apple-style identity token with optional claim overrides.
     *
     * @param array<string, mixed> $overrides
     */
    public static function issueToken(array $overrides = []): string
    {
        $payload = [
            'aud' => config()->string('services.apple.client_id'),
            'email' => 'john@example.com',
            'exp' => now()->addHour()->timestamp,
            'iat' => now()->timestamp,
            'iss' => 'https://appleid.apple.com',
            'nonce' => 'nonce-value',
            'sub' => '123',
            ...$overrides,
        ];

        return JWT::encode(
            $payload,
            file_get_contents(base_path('tests/Support/apple_signing_private.pem')),
            'RS256',
            self::KEY_ID,
        );
    }

    /**
     * Build the JWKS document Apple publishes for the test public key.
     *
     * @return array{keys: list<array<string, string>>}
     */
    public static function jwks(): array
    {
        /** @var array{rsa: array{e: string, n: string}} $details */
        $details = openssl_pkey_get_details(
            openssl_pkey_get_public(
                file_get_contents(base_path('tests/Support/apple_signing_public.pem')),
            ),
        );

        return [
            'keys' => [[
                'alg' => 'RS256',
                'e' => self::base64UrlEncode($details['rsa']['e']),
                'kid' => self::KEY_ID,
                'kty' => 'RSA',
                'n' => self::base64UrlEncode($details['rsa']['n']),
                'use' => 'sig',
            ]],
        ];
    }

    private static function base64UrlEncode(string $value): string
    {
        return str($value)->toBase64()->swap(['+' => '-', '/' => '_'])->rtrim('=')->toString();
    }
}
