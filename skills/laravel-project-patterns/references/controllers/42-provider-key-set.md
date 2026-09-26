# Controllers: Authenticate with a Cached Key Set

Cache public keys, decode the signed token and enforce audience, expiration, issuer and nonce. Existing identities may omit email; new accounts require it and reject an email conflict. This example does not check `email_verified`. Apply Apple's email-trust rules before marking local email verified or matching accounts by email; identify accounts by `sub` and preserve the required nonce encoding.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use function App\__;
use function Safe\json_decode;
use function Safe\json_encode;

use App\Http\Requests\Api\AppleAuthenticatedSessionRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class AppleAuthenticatedSessionController
{
    /**
     * @return array<string, Key>
     */
    private function retrieveKeySet(): array
    {
        /** @var array<mixed> $keys */
        $keys = Cache::remember('apple-keys', now()->addMinutes(5), function (): array {
            /** @var array<mixed> */
            return Http::retry(3, 200)
                ->timeout(5)
                ->get('https://appleid.apple.com/auth/keys')
                ->throw()
                ->json();
        });

        return JWK::parseKeySet($keys);
    }

    public function __invoke(AppleAuthenticatedSessionRequest $request): JsonResponse
    {
        try {
            /**
             * @var array{
             *     aud: string,
             *     email?: string,
             *     exp: int,
             *     iss: string,
             *     nonce: string,
             *     sub: string,
             * } $payload
             */
            $payload = json_decode(json_encode(JWT::decode(
                (string) $request->string('id_token'),
                $this->retrieveKeySet(),
            )), true);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'id_token' => __('auth.providers.apple.invalid_credentials'),
            ]);
        }

        throw_if(
            $payload['aud'] !== Config::string('services.apple.client_id'),
            ValidationException::withMessages([
                'id_token' => __('auth.providers.apple.invalid_credentials'),
            ]),
        );

        throw_if($payload['exp'] < now()->timestamp, ValidationException::withMessages([
            'id_token' => __('auth.providers.apple.invalid_credentials'),
        ]));

        throw_if(
            $payload['iss'] !== 'https://appleid.apple.com',
            ValidationException::withMessages([
                'id_token' => __('auth.providers.apple.invalid_credentials'),
            ]),
        );

        throw_unless(
            hash_equals((string) $request->string('nonce'), $payload['nonce']),
            ValidationException::withMessages([
                'id_token' => __('auth.providers.apple.invalid_credentials'),
            ]),
        );

        $email = $payload['email'] ?? null;
        $user = User::query()->firstWhere('apple_id', $payload['sub']);

        if ($user !== null) {
            if ($email !== null && $user->apple_email !== $email) {
                $user->update(['apple_email' => $email]);
            }

            $token = $user->createToken('Mobile (Apple)')->plainTextToken;

            return new JsonResponse([
                'access_token' => Str::of($token)->explode('|')->last(),
                'user' => UserResource::make($user),
            ]);
        }

        throw_if($email === null, ValidationException::withMessages([
            'id_token' => __('auth.providers.apple.invalid_credentials'),
        ]));

        $user = User::query()->firstWhere('email', $email);

        throw_unless($user === null, ValidationException::withMessages([
            'id_token' => __('auth.providers.apple.account_conflict'),
        ]));

        $user = User::query()->create([
            'apple_email' => $email,
            'apple_id' => $payload['sub'],
            'email' => $email,
            'email_verified_at' => now(),
            'first_name' => (string) $request->string('first_name'),
            'last_name' => (string) $request->string('last_name'),
        ]);

        $token = $user->createToken('Mobile (Apple)')->plainTextToken;

        return new JsonResponse([
            'access_token' => Str::of($token)->explode('|')->last(),
            'user' => UserResource::make($user),
        ]);
    }
}
```
