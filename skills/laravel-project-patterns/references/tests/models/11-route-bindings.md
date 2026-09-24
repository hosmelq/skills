# Model Tests: Public Identifier Route Binding

Feature tests for model route binding through SubstituteBindings: reject raw IDs, accept public identifiers, preserve an explicit field, scope children and resolve soft-deleted rows on a `withTrashed()` route.

`ExampleRecord` is a migrated test support model with `id`, `parent_id`, `deleted_at`, a computed `public_id` and a `children()` relation. Use the existing fixture equivalent.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Tests\Support\Models\ExampleRecord;

it('resolves route bindings by public identifier', function (): void {
    $model = ExampleRecord::query()->create();

    Route::middleware(SubstituteBindings::class)
        ->get('/_test/{model}', fn (ExampleRecord $model): string => $model->public_id);

    $response = get('/_test/'.$model->id);

    $response->assertNotFound();

    $response = get('/_test/'.$model->public_id);

    $response->assertOk();
});

it('preserves explicit binding fields', function (): void {
    $model = ExampleRecord::query()->create();

    Route::middleware(SubstituteBindings::class)
        ->get('/_test/{model:id}', fn (ExampleRecord $model): int => $model->id);

    $response = get('/_test/'.$model->id);

    $response->assertOk();
});

it('scopes child bindings by public identifier', function (): void {
    $parent = ExampleRecord::query()->create();
    $unrelatedParent = ExampleRecord::query()->create();
    $child = ExampleRecord::query()->create(['parent_id' => $parent->id]);

    Route::middleware(SubstituteBindings::class)
        ->get(
            '/_test/{model}/children/{child}',
            fn (ExampleRecord $model, ExampleRecord $child): string => $child->public_id,
        )
        ->scopeBindings();

    $response = get('/_test/'.$parent->public_id.'/children/'.$child->public_id);

    $response->assertOk();

    $response = get('/_test/'.$unrelatedParent->public_id.'/children/'.$child->public_id);

    $response->assertNotFound();
});

it('resolves soft-deleted route bindings when enabled', function (): void {
    $model = ExampleRecord::query()->create([
        'deleted_at' => now(),
    ]);

    Route::middleware(SubstituteBindings::class)
        ->get('/_test/{model}', fn (ExampleRecord $model): string => $model->public_id)
        ->withTrashed();

    $response = get('/_test/'.$model->public_id);

    $response->assertOk();
});
```
