# Authentication Tests: Verification Code Request

POST JSON code request: complete email validation dataset, mocked code generation (no code-record creation assertion) and on-demand notification addressed to the requested email with the same code model.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Actions\GenerateOneTimePasswordCode;
use App\Models\OneTimePassword;
use App\Notifications\OneTimePasswordNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Mockery\MockInterface;

it('validates fields', function (array $data, array $expected): void {
    $response = postJson(route('api.auth.email.request'), $data);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors($expected);
})->with([
    'email' => [
        'data' => [
            'email' => 'test@',
        ],
        'expected' => [
            'email' => 'The email field must be a valid email address.',
        ],
    ],
    'email:dns' => [
        'data' => [
            'email' => 'test@site.test',
        ],
        'expected' => [
            'email' => 'The email field must be a valid email address.',
        ],
    ],
    'email:strict' => [
        'data' => [
            'email' => 'test()@site.test',
        ],
        'expected' => [
            'email' => 'The email field must be a valid email address.',
        ],
    ],
    'indisposable' => [
        'data' => [
            'email' => 'test@0-mail.com',
        ],
        'expected' => [
            'email' => "This email address can't be used. Please try a different email.",
        ],
    ],
    'max:255 (string)' => [
        'data' => [
            'email' => Str::repeat('a', 256),
        ],
        'expected' => [
            'email' => 'The email field must not be greater than 255 characters.',
        ],
    ],
    'required' => [
        'data' => [],
        'expected' => [
            'email' => 'The email field is required.',
        ],
    ],
]);

it('requests a verification code', function (): void {
    Notification::fake();

    $otp = OneTimePassword::factory()->createOne([
        'code' => '424242',
        'email' => 'casey.sample@gmail.com',
    ]);

    $this->mock(GenerateOneTimePasswordCode::class, function (MockInterface $mock) use ($otp): void {
        $mock->shouldReceive('handle')
            ->once()
            ->with('casey.sample@gmail.com')
            ->andReturn($otp);
    });

    $response = postJson(route('api.auth.email.request'), [
        'email' => 'casey.sample@gmail.com',
    ]);

    $response->assertOk();

    Notification::assertSentOnDemand(
        OneTimePasswordNotification::class,
        function (
            OneTimePasswordNotification $notification,
            array $channels,
            AnonymousNotifiable $notifiable
        ) use ($otp): bool {
            return ($notifiable->routes['mail'] ?? null) === $otp->email && $notification->otp->is($otp);
        }
    );
});
```
