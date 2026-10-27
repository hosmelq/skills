<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use function HosmelQ\AuditLog\audit_log;

use App\Actions\Users\CreateUser;
use App\Actions\Users\Inputs\CreateUserInput;
use App\Enums\AuditLogEvent;
use App\Http\Requests\Api\GoogleAuthenticatedSessionRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Google_Client;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class GoogleAuthenticatedSessionController
{
    public function __invoke(
        GoogleAuthenticatedSessionRequest $request,
        CreateUser $createUser,
        Google_Client $googleClient,
    ): JsonResponse {
        $googleClient->setClientId(config()->string('services.google.client_id'));

        /** @var array{email: string, family_name?: string, given_name?: string, sub: string}|false $payload */
        $payload = $googleClient->verifyIdToken($request->string('id_token')->toString());

        throw_if($payload === false, ValidationException::withMessages([
            'id_token' => trans()->string('auth.providers.google.invalid_credentials'),
        ]));

        return DB::transaction(function () use ($createUser, $payload): JsonResponse {
            $user = User::query()->firstWhere('google_id', $payload['sub']);

            if ($user !== null) {
                if ($user->google_email !== $payload['email']) {
                    $user->update(['google_email' => $payload['email']]);

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

                $token = $user->createToken('Mobile (Google)')->plainTextToken;

                event(new Login('sanctum', $user, false));

                return new JsonResponse([
                    'access_token' => str($token)->explode('|')->last(),
                    'user' => UserResource::make($user),
                ]);
            }

            $user = User::query()->firstWhere('email', $payload['email']);

            throw_unless($user === null, ValidationException::withMessages([
                'id_token' => trans()->string('auth.providers.google.account_conflict'),
            ]));

            $user = $createUser->handle(CreateUserInput::from([
                'email' => $payload['email'],
                'email_verified_at' => now(),
                'first_name' => $payload['given_name'] ?? null,
                'google_email' => $payload['email'],
                'google_id' => $payload['sub'],
                'last_name' => $payload['family_name'] ?? null,
            ]));
            $token = $user->createToken('Mobile (Google)')->plainTextToken;

            event(new Login('sanctum', $user, false));
            event(new Verified($user));

            return new JsonResponse([
                'access_token' => str($token)->explode('|')->last(),
                'user' => UserResource::make($user),
            ]);
        });
    }
}
