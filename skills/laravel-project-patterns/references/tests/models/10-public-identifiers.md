# Model Tests: Computed Public Identifiers

Model public identifiers are computed rather than stored: default and explicit route keys, lookup success and invalid input, query scope and missing-model exception.

The fictional support model uses `HasPublicId`, a ten-character codec and these lookup helpers. Inspect the actual codec contract; `ModelNotFoundException` is not itself an HTTP response.

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Support\Models\ExampleRecord;

it('computes public identifiers without persisting them', function (): void {
    $model = ExampleRecord::query()->create();

    expect($model->public_id)
        ->toBeString()
        ->toHaveLength(10)
        ->and($model->getAttributes())
        ->not->toHaveKey('public_id');
});

it('defaults the route key to the public identifier', function (): void {
    expect(new ExampleRecord()->getRouteKeyName())->toBe('public_id');
});

it('finds models by public identifier', function (): void {
    $model = ExampleRecord::query()->create();

    expect($model->is(ExampleRecord::findByPublicId($model->public_id)))
        ->toBeTrue()
        ->and(ExampleRecord::findByPublicId('invalid'))
        ->toBeNull();
});

it('honors an explicit primary route key', function (): void {
    $model = new #[RouteKey('id')] class extends ExampleRecord {};

    expect($model->getRouteKeyName())->toBe('id');
});

it('scopes queries by public identifier', function (): void {
    $model = ExampleRecord::query()->create();

    expect(ExampleRecord::query()->wherePublicId($model->public_id)->first()?->is($model))
        ->toBeTrue();
});

it('throws when the public identifier cannot be found', function (): void {
    ExampleRecord::findOrFailByPublicId('invalid');
})->throws(ModelNotFoundException::class);
```
