# Model Tests: Deactivation Lifecycle

Persisted model deactivation predicates, query scopes, transitions and repeated calls. Refresh after mutations; repeating deactivation preserves its original timestamp.

Use the suite’s isolated database and a test support model with `deactivated_at` and the inspected trait. Freeze time through the existing suite setup.

```php
<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Tests\Support\Models\ExampleRecord;

it('detects active and deactivated models', function (): void {
    $activeModel = ExampleRecord::query()->create();
    $deactivatedModel = ExampleRecord::query()->create(['deactivated_at' => now()]);

    expect($activeModel)
        ->isActive()->toBeTrue()
        ->isDeactivated()->toBeFalse()
        ->and($deactivatedModel)
        ->isActive()->toBeFalse()
        ->isDeactivated()->toBeTrue();
});

it('scopes active and deactivated models', function (): void {
    $activeModel = ExampleRecord::query()->create();
    $deactivatedModel = ExampleRecord::query()->create(['deactivated_at' => now()]);

    $activeModelIds = ExampleRecord::query()->active()->pluck('id')->all();
    $deactivatedModelIds = ExampleRecord::query()->deactivated()->pluck('id')->all();

    expect($activeModelIds)
        ->toBe([$activeModel->id])
        ->and($deactivatedModelIds)
        ->toBe([$deactivatedModel->id]);
});

it('deactivates a model', function (): void {
    $model = ExampleRecord::query()->create();

    $model->deactivate();

    $model->refresh();

    expect($model)
        ->isDeactivated()->toBeTrue()
        ->deactivated_at->not->toBeNull();
});

it('does not deactivate a deactivated model', function (): void {
    $model = ExampleRecord::query()->create(['deactivated_at' => CarbonImmutable::today()->subDay()]);

    $model->deactivate();

    $model->refresh();

    expect($model)
        ->isDeactivated()->toBeTrue()
        ->deactivated_at->toEqual(CarbonImmutable::today()->subDay());
});

it('reactivates a model', function (): void {
    $model = ExampleRecord::query()->create(['deactivated_at' => now()]);

    $model->reactivate();

    $model->refresh();

    expect($model)
        ->isActive()->toBeTrue()
        ->deactivated_at->toBeNull();
});

it('does not reactivate an active model', function (): void {
    $model = ExampleRecord::query()->create();

    $model->reactivate();

    $model->refresh();

    expect($model)
        ->isActive()->toBeTrue()
        ->deactivated_at->toBeNull();
});
```
