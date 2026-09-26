# Controllers: Authenticate with a Provider Client

Verify the token for the configured client, resolve provider identity, update its email if changed, and reject automatic linking to an existing local email. This example does not check `email_verified` or `hd`. Apply Google's email-trust rules before marking local email verified or matching accounts by email; identify accounts by `sub`.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\GoogleAuthenticatedSessionRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Google_Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleAuthenticatedSessionController
{
    public function __invoke(
        GoogleAuthenticatedSessionRequest $request,
        Google_Client $googleClient
    ): JsonResponse {
        $googleClient->setClientId(Config::string('services.google.client_id'));

        /**
         * @var array{
         *     email: string,
         *     family_name: null|string,
         *     given_name: null|string,
         *     sub: string,
         * }|false $payload
         */
        $payload = $googleClient->verifyIdToken((string) $request->string('id_token'));

        throw_if($payload === false, ValidationException::withMessages([
            'id_token' => __('auth.providers.google.invalid_credentials'),
        ]));

        $user = User::query()->firstWhere('google_id', $payload['sub']);

        if ($user !== null) {
            if ($user->google_email !== $payload['email']) {
                $user->update(['google_email' => $payload['email']]);
            }

            $token = $user->createToken('Mobile (Google)')->plainTextToken;

            return new JsonResponse([
                'access_token' => Str::of($token)->explode('|')->last(),
                'user' => UserResource::make($user),
            ]);
        }

        $user = User::query()->firstWhere('email', $payload['email']);

        throw_unless($user === null, ValidationException::withMessages([
            'id_token' => __('auth.providers.google.account_conflict'),
        ]));

        $user = User::query()->create([
            'email' => $payload['email'],
            'email_verified_at' => now(),
            'first_name' => $payload['given_name'] ?? null,
            'google_email' => $payload['email'],
            'google_id' => $payload['sub'],
            'last_name' => $payload['family_name'] ?? null,
        ]);

        $token = $user->createToken('Mobile (Google)')->plainTextToken;

        return new JsonResponse([
            'access_token' => Str::of($token)->explode('|')->last(),
            'user' => UserResource::make($user),
        ]);
    }
}
```
