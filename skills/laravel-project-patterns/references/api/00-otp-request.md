# API: One-Time Password Request

Start a passwordless API sign-in: generate a one-time password for the submitted email, audit it and mail the code, answering the same way whether or not an account exists.

- Throttle the route with a named limiter keyed by email and IP, defined with `RateLimiter::for()` in a service provider.
- Notify the address on demand with `Notification::route()`.
- API tests use the JSON helpers and `assertJsonValidationErrors()`.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class EmailOtpRequest extends FormRequest
{
    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
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

use App\Actions\GenerateOneTimePasswordCode;
use App\Enums\AuditLogEvent;
use App\Http\Requests\Api\EmailOtpRequest;
use App\Notifications\OneTimePasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

final class EmailOtpRequestController
{
    public function __invoke(
        EmailOtpRequest $request,
        GenerateOneTimePasswordCode $generateOneTimePasswordCode,
    ): JsonResponse {
        return DB::transaction(
            function () use ($generateOneTimePasswordCode, $request): JsonResponse {
                $otp = $generateOneTimePasswordCode->handle(
                    $email = $request->string('email')->toString(),
                );

                audit_log(AuditLogEvent::AuthOtpRequested)->target($otp)->record();

                Notification::route('mail', $email)->notify(new OneTimePasswordNotification($otp));

                return new JsonResponse();
            },
        );
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Enums\AuditLogEvent;
use App\Models\OneTimePassword;
use App\Notifications\OneTimePasswordNotification;
use HosmelQ\AuditLog\Data\AuditLogData;
use HosmelQ\AuditLog\Facades\AuditLog;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;

it('validates fields', function (array $data, array $expected): void {
    $response = postJson(route('api.auth.email.request'), $data);

    $response->assertUnprocessable()->assertJsonValidationErrors($expected);
})->with([
    'email' => [
        'data' => [
            'email' => 'test@',
        ],
        'expected' => [
            'email' => 'The email field must be a valid email address.',
        ],
    ],
    'required' => [
        'data' => [],
        'expected' => [
            'email' => 'The email field is required.',
        ],
    ],
]);

it('can request an email one-time password code', function (): void {
    AuditLog::fake();
    Notification::fake();

    $response = postJson(route('api.auth.email.request'), [
        'email' => 'john@gmail.com',
    ]);

    $response->assertOk();

    $otp = OneTimePassword::query()->sole();

    Notification::assertSentOnDemand(
        OneTimePasswordNotification::class,
        function (
            OneTimePasswordNotification $notification,
            array $channels,
            AnonymousNotifiable $notifiable,
        ) use ($otp): bool {
            return ($notifiable->routes['mail'] ?? null) === 'john@gmail.com'
                && $notification->otp->is($otp);
        },
    );

    AuditLog::assertRecorded(
        fn (AuditLogData $log): bool => $log->event === AuditLogEvent::AuthOtpRequested->value
            && $log->targets[0]->id === (string) $otp->id,
    );
});
```
