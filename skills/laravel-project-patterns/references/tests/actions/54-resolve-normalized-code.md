# Action Tests: Resolve a Normalized Code

Integration tests for code lookup: return null for missing, soft-deleted, inactive or other-tenant matches; resolve the same active record from formatted, lowercase, spaced and compact inputs through a dataset.

```php
<?php

declare(strict_types=1);

use App\Actions\ResolveActiveCabinet;
use App\Models\Cabinet;
use App\Models\Team;

it('returns null when no active assignment matches the raw code', function (): void {
    $team = Team::factory()->createOne();

    $resolvedCabinet = resolve(ResolveActiveCabinet::class)->handle($team, 'DEMO-008120');

    expect($resolvedCabinet)->toBeNull();
});

it('does not resolve soft deleted matching assignments', function (): void {
    $cabinet = Cabinet::factory()->trashed()->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => Cabinet::normalizeCode('DEMO-008120'),
    ]);

    $resolvedCabinet = resolve(ResolveActiveCabinet::class)
        ->handle($cabinet->member->team, 'DEMO-008120');

    expect($resolvedCabinet)->toBeNull();
});

it('does not resolve deactivated matching assignments', function (): void {
    $cabinet = Cabinet::factory()->deactivated()->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => Cabinet::normalizeCode('DEMO-008120'),
    ]);

    $resolvedCabinet = resolve(ResolveActiveCabinet::class)
        ->handle($cabinet->member->team, 'DEMO-008120');

    expect($resolvedCabinet)->toBeNull();
});

it('does not resolve matching assignments from another tenant', function (): void {
    $team = Team::factory()->createOne();

    Cabinet::factory()->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => Cabinet::normalizeCode('DEMO-008120'),
    ]);

    $resolvedCabinet = resolve(ResolveActiveCabinet::class)->handle($team, 'DEMO-008120');

    expect($resolvedCabinet)->toBeNull();
});

it('resolves active assignments from normalized raw input', function (string $code): void {
    $cabinet = Cabinet::factory()->createOne([
        'code' => 'DEMO-008120',
        'normalized_code' => Cabinet::normalizeCode('DEMO-008120'),
    ]);

    $resolvedCabinet = resolve(ResolveActiveCabinet::class)
        ->handle($cabinet->member->team, $code);

    expect($resolvedCabinet->is($cabinet))->toBeTrue();
})->with([
    'formatted' => 'DEMO-008120',
    'lowercase' => 'demo-008120',
    'spaced' => 'DEMO 008120',
    'compact' => 'DEMO008120',
]);
```
