<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use function HosmelQ\AuditLog\audit_log;
use function Safe\json_decode;
use function Safe\json_encode;

use App\Actions\Users\CreateUser;
use App\Actions\Users\Inputs\CreateUserInput;
use App\Enums\AuditLogEvent;
use App\Http\Requests\Api\AppleAuthenticatedSessionRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Throwable;

final class AppleAuthenticatedSessionController
{
    /**
     * @return array<string, Key>
     */
    private function retrieveKeySet(): array
    {
        /** @var array<mixed> $keys */
        $keys = cache()->remember('apple-keys', now()->addMinutes(5), function (): array {
            /** @var array<mixed> */
            return Http::retry(3, 200)
                ->timeout(5)
                ->get('https://appleid.apple.com/auth/keys')
                ->throw()
                ->json();
        });

        return JWK::parseKeySet($keys);
    }

    public function __invoke(
        AppleAuthenticatedSessionRequest $request,
        CreateUser $createUser,
    ): JsonResponse {
        try {
            /** @var array{aud: string, email?: string, exp: int, iss: string, nonce: string, sub: string} $payload */
            $payload = json_decode(json_encode(JWT::decode(
                $request->string('id_token')->toString(),
                $this->retrieveKeySet(),
            )), true);
        } catch (Throwable) {
            throw ValidationException::withMessages([
                'id_token' => trans()->string('auth.providers.apple.invalid_credentials'),
            ]);
        }

        throw_if(
            $payload['aud'] !== config()->string('services.apple.client_id'),
            ValidationException::withMessages([
                'id_token' => trans()->string('auth.providers.apple.invalid_credentials'),
            ]),
        );

        throw_if($payload['exp'] < now()->timestamp, ValidationException::withMessages([
            'id_token' => trans()->string('auth.providers.apple.invalid_credentials'),
        ]));

        throw_if(
            $payload['iss'] !== 'https://appleid.apple.com',
            ValidationException::withMessages([
                'id_token' => trans()->string('auth.providers.apple.invalid_credentials'),
            ]),
        );

        throw_unless(
            hash_equals($request->string('nonce')->toString(), $payload['nonce']),
            ValidationException::withMessages([
                'id_token' => trans()->string('auth.providers.apple.invalid_credentials'),
            ]),
        );

        return DB::transaction(function () use ($createUser, $payload, $request): JsonResponse {
            $email = $payload['email'] ?? null;
            $user = User::query()->firstWhere('apple_id', $payload['sub']);

            if ($user !== null) {
                if ($email !== null && $user->apple_email !== $email) {
                    $user->update(['apple_email' => $email]);

                    /** @var array<string, null|bool|float|int|string> $before */
                    $before = Arr::except($user->getPrevious(), 'updated_at');
                    /** @var array<string, null|bool|float|int|string> $after */
                    $after = Arr::except($user->getChanges(), 'updated_at');

                    audit_log(AuditLogEvent::UserUpdated)
                        ->changes(before: $before, after: $after)
                        ->actor($user)
                        ->target($user)
                        ->tenant($user->current_clinic_id ?? '')
                        ->record();
                }

                $token = $user->createToken('Mobile (Apple)')->plainTextToken;

                event(new Login('sanctum', $user, false));

                return new JsonResponse([
                    'access_token' => str($token)->explode('|')->last(),
                    'user' => UserResource::make($user),
                ]);
            }

            throw_if($email === null, ValidationException::withMessages([
                'id_token' => trans()->string('auth.providers.apple.invalid_credentials'),
            ]));

            $user = User::query()->firstWhere('email', $email);

            throw_unless($user === null, ValidationException::withMessages([
                'id_token' => trans()->string('auth.providers.apple.account_conflict'),
            ]));

            $user = $createUser->handle(CreateUserInput::from([
                'apple_email' => $email,
                'apple_id' => $payload['sub'],
                'email' => $email,
                'email_verified_at' => now(),
                'first_name' => $request->string('first_name')->toString(),
                'last_name' => $request->string('last_name')->toString(),
            ]));
            $token = $user->createToken('Mobile (Apple)')->plainTextToken;

            event(new Login('sanctum', $user, false));
            event(new Verified($user));

            return new JsonResponse([
                'access_token' => str($token)->explode('|')->last(),
                'user' => UserResource::make($user),
            ]);
        });
    }
}
