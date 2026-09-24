# Action Tests: Generate a Scoped Code

Integration tests for code generation from tenant format settings: normalized collisions, retries, inactive collisions, reuse after soft deletion or under another tenant, and exhausted attempts. The NanoID client is mocked.

```php
<?php

declare(strict_types=1);

use App\Actions\GenerateCabinetCode;
use App\Enums\CodeAlphabet;
use App\Exceptions\CannotGenerateCabinetCode;
use App\Models\Cabinet;
use App\Models\Team;
use Hidehalo\Nanoid\Client;
use Mockery\MockInterface;

it('generates an assignment code from tenant format settings', function (): void {
    $team = Team::factory()->createOne([
        'code_format_alphabet_type' => CodeAlphabet::Numbers,
        'code_format_length' => Team::DEFAULT_CODE_LENGTH,
        'code_format_prefix' => 'DEMO-',
    ]);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->once()
            ->with(
                CodeAlphabet::Numbers->alphabet(),
                Team::DEFAULT_CODE_LENGTH,
            )
            ->andReturn('008120');
    });

    $code = resolve(GenerateCabinetCode::class)->handle($team);

    expect($code)->toBe('DEMO-008120');
});

it('retries until a unique normalized assignment code is found', function (): void {
    $team = Team::factory()->createOne([
        'code_format_prefix' => 'DEMO-',
    ]);

    Cabinet::factory()->recycle($team)->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => 'DEMO008120',
    ]);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->andReturnValues(['008120', '008121']);
    });

    $code = resolve(GenerateCabinetCode::class)->handle($team);

    expect($code)->toBe('DEMO-008121');
});

it('does not reuse codes from deactivated assignments', function (): void {
    $team = Team::factory()->createOne([
        'code_format_prefix' => 'DEMO-',
    ]);

    Cabinet::factory()->recycle($team)->deactivated()->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => 'DEMO008120',
    ]);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->andReturnValues(['008120', '008121']);
    });

    $code = resolve(GenerateCabinetCode::class)->handle($team);

    expect($code)->toBe('DEMO-008121');
});

it('ignores soft deleted assignments when checking generated codes', function (): void {
    $team = Team::factory()->createOne([
        'code_format_prefix' => 'DEMO-',
    ]);

    Cabinet::factory()->recycle($team)->trashed()->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => 'DEMO008120',
    ]);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->once()
            ->andReturn('008120');
    });

    $code = resolve(GenerateCabinetCode::class)->handle($team);

    expect($code)->toBe('DEMO-008120');
});

it('ignores matching normalized codes from other tenants', function (): void {
    $team = Team::factory()->createOne([
        'code_format_prefix' => 'DEMO-',
    ]);

    Cabinet::factory()->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => 'DEMO008120',
    ]);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->once()
            ->andReturn('008120');
    });

    $code = resolve(GenerateCabinetCode::class)->handle($team);

    expect($code)->toBe('DEMO-008120');
});

it('throws when unable to generate a unique assignment code after max attempts', function (): void {
    $team = Team::factory()->createOne([
        'code_format_prefix' => 'DEMO-',
    ]);

    Cabinet::factory()->recycle($team)->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => 'DEMO008120',
    ]);

    $this->mock(Client::class, function (MockInterface $mock): void {
        $mock->shouldReceive('formattedId')
            ->times(GenerateCabinetCode::MAX_RETRY_ATTEMPTS)
            ->andReturn('008120');
    });

    resolve(GenerateCabinetCode::class)->handle($team);
})->throws(
    CannotGenerateCabinetCode::class,
    'Unable to generate unique cabinet code after 20 attempts.',
);
```
