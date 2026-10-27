# API: Google Sign-In Session

Exchange a Google identity token for a Sanctum token: verify it with the Google API client for the configured client id, then sign in the user linked to the Google subject, or create a verified account named from the token, refusing an email that already belongs to another sign-in method.

- Inject `Google_Client` so tests replace it with a mock whose `verifyIdToken()` returns the payload or `false`.
- Match users by the provider subject, not the email, and keep the provider email in its own column.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class GoogleAuthenticatedSessionRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'id_token' => ['required', 'string'],
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
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\mock;
use function Pest\Laravel\postJson;

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Mockery\MockInterface;

it('fails when the identity token cannot be verified', function (): void {
    mock(Google_Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('setClientId')->once();
        $mock->shouldReceive('verifyIdToken')->once()->with('invalid-token')->andReturnFalse();
    });

    $response = postJson(route('api.auth.google.login'), [
        'id_token' => 'invalid-token',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => 'Unable to verify the supplied Google credentials.',
        ]);
});

it('keeps the account tied to the same google identity even if google reports a different email', function (): void {
    $user = User::factory()->createOne([
        'email' => 'john@example.com',
        'google_email' => 'john@example.com',
        'google_id' => '123',
    ]);

    mock(Google_Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('setClientId')->once();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->andReturn([
                'email' => 'john.doe@example.com',
                'sub' => '123',
            ]);
    });

    $response = postJson(route('api.auth.google.login'), [
        'id_token' => 'id-token',
    ]);

    $response->assertOk()->assertJsonPath('user.id', $user->sqid);

    expect($user->refresh())
        ->email->toBe('john@example.com')
        ->google_email->toBe('john.doe@example.com')
        ->tokens->toHaveCount(1);
});

it('asks the user to continue with their existing sign in when the email is already registered', function (): void {
    User::factory()->createOne([
        'email' => 'john@example.com',
    ]);

    mock(Google_Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('setClientId')->once();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->andReturn([
                'email' => 'john@example.com',
                'sub' => '123',
            ]);
    });

    $response = postJson(route('api.auth.google.login'), [
        'id_token' => 'id-token',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors([
            'id_token' => "You can't sign in with Google on this account. Use your email to continue.",
        ]);
});

it('creates an account and signs the user in when no matching identity exists', function (): void {
    mock(Google_Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('setClientId')->once();
        $mock->shouldReceive('verifyIdToken')
            ->once()
            ->andReturn([
                'email' => 'john@example.com',
                'family_name' => 'Doe',
                'given_name' => 'John',
                'sub' => '123',
            ]);
    });

    $response = postJson(route('api.auth.google.login'), [
        'id_token' => 'id-token',
    ]);

    $response->assertOk()
        ->assertJson(function (AssertableJson $json): void {
            $json->whereType('access_token', 'string')
                ->where('user.email', 'john@example.com')
                ->etc();
        });

    expect(User::query()->where('email', 'john@example.com')->sole())
        ->email_verified_at->not->toBeNull()
        ->first_name->toBe('John')
        ->google_email->toBe('john@example.com')
        ->google_id->toBe('123')
        ->last_name->toBe('Doe')
        ->tokens->toHaveCount(1);
});
```
