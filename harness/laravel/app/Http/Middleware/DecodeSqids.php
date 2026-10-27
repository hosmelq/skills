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
