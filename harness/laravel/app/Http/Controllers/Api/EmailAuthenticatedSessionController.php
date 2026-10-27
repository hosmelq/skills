<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use function HosmelQ\AuditLog\audit_log;

use App\Actions\Users\CreateUser;
use App\Actions\Users\Inputs\CreateUserInput;
use App\Enums\AuditLogEvent;
use App\Http\Requests\Api\EmailOtpLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\OneTimePassword;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class EmailAuthenticatedSessionController
{
    public function __invoke(EmailOtpLoginRequest $request, CreateUser $createUser): JsonResponse
    {
        return DB::transaction(function () use ($createUser, $request): JsonResponse {
            $email = $request->string('email')->toString();
            $otp = OneTimePassword::query()
                ->where('code', $request->string('code')->toString())
                ->where('email', $email)
                ->where('expires_at', '>', now())
                ->whereNull('used_at')
                ->first();
            throw_if($otp === null, ValidationException::withMessages([
                'code' => trans()->string('validation.exists', [
                    'attribute' => trans()->string('validation.attributes.code'),
                ]),
            ]));

            $otp->update(['used_at' => now()]);

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($otp->getPrevious(), 'used_at');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($otp->getChanges(), 'used_at');

            audit_log(AuditLogEvent::AuthOtpUsed)
                ->changes(before: $before, after: $after)
                ->target($otp)
                ->record();

            $user = User::query()->firstWhere('email', $email);

            if ($user === null) {
                $user = $createUser->handle(CreateUserInput::from([
                    'email' => $email,
                    'email_verified_at' => now(),
                ]));
            } elseif ($user->email_verified_at === null) {
                $user->update(['email_verified_at' => now()]);
            }

            $token = $user->createToken('Mobile')->plainTextToken;

            event(new Login('sanctum', $user, false));

            if ($user->wasRecentlyCreated || $user->wasChanged('email_verified_at')) {
                event(new Verified($user));
            }

            return new JsonResponse([
                'access_token' => str($token)->explode('|')->last(),
                'user' => UserResource::make($user),
            ]);
        });
    }
}
