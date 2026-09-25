# Middleware Tests: Request Identifier Decoding

Feature tests for configured Sqid fields: decode two IDs, normalize a noncanonical uppercase fixture to zero, and normalize numeric or invalid string input while preserving null and absent fields. Zero is the middleware’s invalid sentinel, without asserting an HTTP validation error.

The case-change precondition requires a fixture whose encoded value changes when uppercased.

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Support\Sqid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

it('decodes configured sqid request fields', function (): void {
    Route::middleware('sqids:move_after_id,service_plan_id')
        ->post('/_test', fn (Request $request): array => $request->only([
            'move_after_id',
            'service_plan_id',
        ]));

    $sqid = resolve(Sqid::class);

    $response = postJson('/_test', [
        'move_after_id' => $sqid->encode(456),
        'service_plan_id' => $sqid->encode(123),
    ]);

    $response->assertExactJson([
        'move_after_id' => 456,
        'service_plan_id' => 123,
    ]);
});

it('normalizes a differently cased sqid to zero', function (): void {
    Route::middleware('sqids:service_plan_id')
        ->post('/_test', fn (Request $request): array => $request->only(['service_plan_id']));

    $encodedSqid = resolve(Sqid::class)->encode(123);
    $uppercaseSqid = Str::upper($encodedSqid);

    expect($uppercaseSqid)->not->toBe($encodedSqid);

    $response = postJson('/_test', ['service_plan_id' => $uppercaseSqid]);

    $response->assertExactJson(['service_plan_id' => 0]);
});

it('normalizes invalid identifiers and preserves null or absent fields', function (): void {
    Route::middleware('sqids:missing_id,move_after_id,nullable_id,service_plan_id')
        ->post('/_test', fn (Request $request): array => $request->only([
            'missing_id',
            'move_after_id',
            'nullable_id',
            'service_plan_id',
        ]));

    $response = postJson('/_test', [
        'move_after_id' => 123,
        'nullable_id' => null,
        'service_plan_id' => 'invalid',
    ]);

    $response->assertExactJson([
        'move_after_id' => 0,
        'nullable_id' => null,
        'service_plan_id' => 0,
    ]);
});
```
