# Middleware: Decode Sqids

Decode Sqids sent in request fields to integer ids before validation, so `exists` rules and queries receive ids while clients only see Sqids.

- Register it as the `sqids` alias in `bootstrap/app.php` and list the fields per route or controller, such as `sqids:move_after_id`.
- Invalid values become `0`, so `exists` rules reject them; missing and `null` fields stay unchanged.
- Test middleware through temporary routes in the feature suite.

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Sqid;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class DecodeSqids
{
    private const int INVALID_ID = 0;

    public function __construct(private Sqid $sqid) {}

    /**
     * @param Closure(Request): Response $next
     */
    public function handle(Request $request, Closure $next, string ...$fields): Response
    {
        foreach ($fields as $field) {
            $value = $request->input($field);

            if (! $request->exists($field) || $value === null) {
                continue;
            }

            $request->merge([
                $field => is_string($value)
                    ? $this->sqid->decode($value) ?? self::INVALID_ID
                    : self::INVALID_ID,
            ]);
        }

        return $next($request);
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\postJson;

use App\Support\Sqid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

it('decodes configured sqid request fields', function (): void {
    Route::middleware('sqids:move_after_id,room_id')
        ->post('/_test', fn (Request $request): array => $request->only([
            'move_after_id',
            'room_id',
        ]));

    $sqid = resolve(Sqid::class);
    $response = postJson('/_test', [
        'move_after_id' => $sqid->encode(456),
        'room_id' => $sqid->encode(123),
    ]);

    $response->assertExactJson([
        'move_after_id' => 456,
        'room_id' => 123,
    ]);
});

it('normalizes invalid identifiers without changing nullable or missing fields', function (): void {
    Route::middleware('sqids:missing_id,move_after_id,nullable_id,room_id')
        ->post('/_test', fn (Request $request): array => $request->only([
            'missing_id',
            'move_after_id',
            'nullable_id',
            'room_id',
        ]));

    $response = postJson('/_test', [
        'move_after_id' => 123,
        'nullable_id' => null,
        'room_id' => 'invalid',
    ]);

    $response->assertExactJson([
        'move_after_id' => 0,
        'nullable_id' => null,
        'room_id' => 0,
    ]);
});
```
