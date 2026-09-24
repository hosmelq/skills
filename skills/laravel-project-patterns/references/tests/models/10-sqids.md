# Model Tests: Computed Sqids

Model Sqids are computed rather than stored: default and explicit route keys, lookup success and invalid input, query scope and missing-model exception.

The support model uses the application’s `HasSqid` wrapper around Sqids with a ten-character minimum. Inspect that wrapper and its configuration; `ModelNotFoundException` is not itself an HTTP response.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Support\Models\ExampleRecord;

it('computes sqids without persisting them', function (): void {
    $model = ExampleRecord::query()->create();

    expect($model->sqid)
        ->toBeString()
        ->toHaveLength(10)
        ->and($model->getAttributes())
        ->not->toHaveKey('sqid');
});

it('defaults the route key to sqid without a route key attribute', function (): void {
    expect(new ExampleRecord()->getRouteKeyName())->toBe('sqid');
});

it('finds models by sqid', function (): void {
    $model = ExampleRecord::query()->create();

    expect($model->is(ExampleRecord::findBySqid($model->sqid)))
        ->toBeTrue()
        ->and(ExampleRecord::findBySqid('invalid'))
        ->toBeNull();
});

it('honors an explicit primary route key attribute', function (): void {
    $model = new #[RouteKey('id')] class extends ExampleRecord {};

    expect($model->getRouteKeyName())->toBe('id');
});

it('scopes queries by sqid', function (): void {
    $model = ExampleRecord::query()->create();

    expect(ExampleRecord::query()->whereSqid($model->sqid)->first()?->is($model))
        ->toBeTrue();
});

it('throws when sqid cannot be found', function (): void {
    ExampleRecord::findOrFailBySqid('invalid');
})->throws(ModelNotFoundException::class);
```
