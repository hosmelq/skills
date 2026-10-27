# API: One-Time Password Session

Exchange a valid one-time password for a Sanctum token: consume the code, sign in the user with that email or create a verified account, issue the token and fire the framework's auth events, rejecting expired, used or unknown codes with the same error.

- Lock the whole exchange in one transaction.
- Return the token without its id prefix and the user resource.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Models\OneTimePassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

final class EmailOtpLoginRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'digits:6', Rule::exists(OneTimePassword::class)],
            'email' => ['required', 'string', 'max:255', 'email:strict,dns'],
        ];
    }
}
```

```php
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
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Enums\AuditLogEvent;
use App\Models\OneTimePassword;
use App\Models\User;
use HosmelQ\AuditLog\Data\AuditLogData;
use HosmelQ\AuditLog\Facades\AuditLog;

it('rejects an expired verification code', function (): void {
    OneTimePassword::factory()->expired()->createOne([
        'code' => '111111',
        'email' => 'john@gmail.com',
    ]);

    $response = postJson(route('api.auth.email.login'), [
        'code' => '111111',
        'email' => 'john@gmail.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'code' => 'The selected code is invalid.',
        ]);
});

it('can authenticate with a valid email one-time password and create a user when none exists', function (): void {
    OneTimePassword::factory()->createOne([
        'code' => '111111',
        'email' => 'john@gmail.com',
    ]);

    $response = postJson(route('api.auth.email.login'), [
        'code' => '111111',
        'email' => 'john@gmail.com',
    ]);

    $response->assertOk()->assertJsonPath('user.email', 'john@gmail.com');

    expect(User::query()->where('email', 'john@gmail.com')->sole())
        ->email_verified_at->not->toBeNull()
        ->tokens->toHaveCount(1);
});

it('can authenticate with a valid email one-time password for an existing user', function (): void {
    AuditLog::fake();

    $otp = OneTimePassword::factory()->createOne([
        'code' => '111111',
        'email' => 'john@gmail.com',
    ]);
    $user = User::factory()
        ->unverified()
        ->createOne([
            'email' => 'john@gmail.com',
        ]);
    $response = postJson(route('api.auth.email.login'), [
        'code' => '111111',
        'email' => 'john@gmail.com',
    ]);

    $response->assertOk()->assertJsonPath('user.id', $user->sqid);

    expect($response->json('access_token'))->toBeString()
        ->and($otp->refresh()->used_at)->not->toBeNull()
        ->and($user->refresh()->email_verified_at)->not->toBeNull();

    AuditLog::assertRecorded(
        fn (AuditLogData $log): bool => $log->event === AuditLogEvent::AuthOtpUsed->value
            && $log->targets[0]->id === (string) $otp->id
            && $log->changes?->before['used_at'] === null
            && $log->changes?->after['used_at'] === now()->toDateTimeString(),
    );
});
```
