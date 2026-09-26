# Middleware: Request Identifiers

Decode only configured request fields before continuing. Missing fields stay absent and `null` stays `null`; nonstrings and strings rejected by `Sqid::decode()` become integer `0`. The helper requires one number and canonical re-encoding; a valid encoding of zero also produces `0`. Validate input and check record access downstream.

Inject the project's `Sqid` helper through the constructor. Pass field names through the middleware alias, for example `sqids:move_after_id,service_plan_id`.

```php
<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Sqid;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DecodeSqids
{
    private const int INVALID_ID = 0;

    public function __construct(private readonly Sqid $sqid)
    {
    }

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
