# Models: Sqid Identity and Route Binding

Implement HasSqid with a computed sqid, nullable/throwing lookup, qualified route binding and a whereSqid scope. Explicit non-Sqid route fields delegate to the parent binder.

Requires the project’s `App\Support\Sqid` codec: `encode(int): string`, and `decode(string): ?int` rejecting noncanonical or multi-number inputs. The accessor requires an integer model key. Preserve the public Sqid API names.

```php
<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\Sqid;
use Illuminate\Contracts\Database\Eloquent\Builder as BuilderContract;
use Illuminate\Database\Eloquent\Attributes\RouteKey;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use LogicException;

/**
 * @property-read string $sqid
 */
trait HasSqid
{
    public static function findBySqid(string $sqid): null|static
    {
        return static::query()->whereSqid($sqid)->first();
    }

    /**
     * @throws ModelNotFoundException<$this>
     */
    public static function findOrFailBySqid(string $sqid): static
    {
        return static::query()->whereSqid($sqid)->firstOrFail();
    }

    public function getRouteKeyName(): string
    {
        $routeKey = static::resolveClassAttribute(RouteKey::class);

        return $routeKey instanceof RouteKey ? $routeKey->key : 'sqid';
    }

    /**
     * @param BuilderContract|Model $query
     * @param mixed $value
     * @param null|string $field
     */
    public function resolveRouteBindingQuery($query, $value, $field = null): BuilderContract
    {
        $sqidFields = ['sqid', $this->qualifyColumn('sqid')];
        $routeKeyField = $field ?? $this->getRouteKeyName();

        if (! in_array($routeKeyField, $sqidFields, true)) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        $id = is_string($value)
            ? resolve(Sqid::class)->decode($value)
            : null;

        return $query->where($this->qualifyColumn($this->getKeyName()), $id);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function sqid(): Attribute
    {
        return Attribute::get(function (): string {
            $id = $this->getKey();

            throw_unless(is_int($id), LogicException::class, 'Sqids require an integer model key.');

            return resolve(Sqid::class)->encode($id);
        });
    }

    /**
     * @param Builder<static> $builder
     */
    #[Scope]
    protected function whereSqid(Builder $builder, string $sqid): void
    {
        $builder->whereKey(resolve(Sqid::class)->decode($sqid));
    }
}
```
