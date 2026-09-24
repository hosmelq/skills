# Action Tests: Validate and Update a Profile

Integration tests for profile input validation and update: named invalid-field datasets, ValidationException error bag and exact messages for the listed fields, persisted name/email, cleared verification timestamp and verification notification.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

it('validates fields', function (array $data, array $expected): void {
    $user = User::factory()->createOne();

    expect(fn () => resolve(UpdateUserProfileInformation::class)->update($user, $data))
        ->toThrow(function (ValidationException $exception) use ($expected): void {
            $messages = Arr::only($exception->validator->errors()->messages(), array_keys($expected));

            expect($exception->errorBag)
                ->toBe('updateProfileInformation')
                ->and($messages)
                ->toBe($expected);
        });
})->with([
    'email' => [
        'data' => [
            'email' => 'test@',
        ],
        'expected' => [
            'email' => ['The email field must be a valid email address.'],
        ],
    ],
    'max:255 (string)' => [
        'data' => [
            'first_name' => Str::repeat('a', 256),
            'last_name' => Str::repeat('a', 256),
        ],
        'expected' => [
            'first_name' => ['The first name field must not be greater than 255 characters.'],
            'last_name' => ['The last name field must not be greater than 255 characters.'],
        ],
    ],
    'required' => [
        'data' => [
            'email' => '',
            'first_name' => '',
            'last_name' => '',
        ],
        'expected' => [
            'email' => ['The email field is required.'],
            'first_name' => ['The first name field is required.'],
            'last_name' => ['The last name field is required.'],
        ],
    ],
    'string' => [
        'data' => [
            'first_name' => 123,
            'last_name' => 123,
        ],
        'expected' => [
            'first_name' => ['The first name field must be a string.'],
            'last_name' => ['The last name field must be a string.'],
        ],
    ],
    'unique' => [
        'data' => fn (): array => [
            'email' => User::factory()->createOne()->email,
        ],
        'expected' => [
            'email' => ['The email has already been taken.'],
        ],
    ],
]);

it('updates profile information', function (): void {
    Notification::fake();

    $user = User::factory()->createOne([
        'email' => 'old@example.com',
        'first_name' => 'Old',
        'last_name' => 'Name',
    ]);

    resolve(UpdateUserProfileInformation::class)->update($user, [
        'email' => 'new@example.com',
        'first_name' => 'Sam',
        'last_name' => 'Example',
    ]);

    assertDatabaseHas(User::class, [
        'id' => $user->id,
        'email' => 'new@example.com',
        'email_verified_at' => null,
        'first_name' => 'Sam',
        'last_name' => 'Example',
    ]);

    Notification::assertSentTo($user, VerifyEmail::class);
});
```
