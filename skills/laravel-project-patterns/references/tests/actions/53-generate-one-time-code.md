# Action Tests: Generate a One-Time Code

Integration tests for a persisted one-time code: exact expiry, replacement for the same email, preservation of another email, collisions and bounded retries. The generator is mocked; the query checks absence in its scope, which alone does not distinguish physical from soft deletion.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\GenerateOneTimePasswordCode;
use App\Enums\NanoIDAlphabet;
use App\Exceptions\CannotGenerateOneTimePasswordCode;
use App\Models\OneTimePassword;
use Hidehalo\Nanoid\Client;
use Mockery\MockInterface;

it('creates a fresh one-time password for the given email', function (): void {
    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->once()
            ->with(NanoIDAlphabet::Numbers(), 6)
            ->andReturn('111111');
    });

    resolve(GenerateOneTimePasswordCode::class)->handle('alex@example.com');

    assertDatabaseHas(OneTimePassword::class, [
        'code' => '111111',
        'email' => 'alex@example.com',
        'expires_at' => (string) now()->addMinutes(GenerateOneTimePasswordCode::EXPIRATION_MINUTES),
        'used_at' => null,
    ]);
});

it('deletes existing unused codes for the email', function (): void {
    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')->andReturn('333333');
    });

    [$sameEmailCode, $otherEmailCode] = OneTimePassword::factory()
        ->count(2)
        ->sequence(
            ['code' => '111111', 'email' => 'alex@example.com'],
            ['code' => '222222', 'email' => 'sam@example.com']
        )
        ->create();

    $otp = resolve(GenerateOneTimePasswordCode::class)->handle('alex@example.com');

    $sameEmailCodeExists = OneTimePassword::query()->whereKey($sameEmailCode->id)->exists();
    $otherEmailCodeExists = OneTimePassword::query()->whereKey($otherEmailCode->id)->exists();

    expect($otp->code)->toBe('333333')
        ->and($sameEmailCodeExists)->toBeFalse()
        ->and($otherEmailCodeExists)->toBeTrue();
});

it('retries until a unique code is found', function (): void {
    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->andReturnValues(['111111', '222222', '333333']);
    });

    OneTimePassword::factory()
        ->count(2)
        ->sequence(['code' => '111111'], ['code' => '222222'])
        ->create();

    $otp = resolve(GenerateOneTimePasswordCode::class)->handle('alex@example.com');

    expect($otp->code)->toBe('333333');
});

it('avoids codes assigned to other emails', function (): void {
    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->andReturnValues(['111111', '222222']);
    });

    OneTimePassword::factory()->createOne(['code' => '111111']);

    $otp = resolve(GenerateOneTimePasswordCode::class)->handle('alex@example.com');

    expect($otp->code)->toBe('222222');
});

it('throws when unable to generate a unique code after max attempts', function (): void {
    OneTimePassword::factory()->createOne(['code' => '111111']);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->times(GenerateOneTimePasswordCode::MAX_RETRY_ATTEMPTS)
            ->andReturn('111111');
    });

    resolve(GenerateOneTimePasswordCode::class)->handle('alex@example.com');
})->throws(
    CannotGenerateOneTimePasswordCode::class,
    'Unable to generate one-time password after 20 attempts.',
);
```
