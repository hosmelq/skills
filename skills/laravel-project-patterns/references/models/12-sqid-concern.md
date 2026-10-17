# Models: Sqid Concern

Create the `HasSqid` concern that exposes a computed `sqid`, finds and scopes models by it and resolves route bindings with it, together with the `Sqid` service, its container binding, the test-only model and the concern's tests. Models that only use the existing concern need the new model reference instead.

- Test the concern through `Tests\Support\Models\TestModel` on a table from `tests/migrations`, which the test case adds to its fresh-migration paths and whose alias it adds to the enforced morph map. Integration tests cover lookups; feature tests cover route bindings.

```php
<?php

declare(strict_types=1);

namespace App\Support;

use Sqids\SqidsInterface;

final readonly class Sqid
{
    public function __construct(private SqidsInterface $sqids) {}

    public function decode(string $sqid): null|int
    {
        $numbers = $this->sqids->decode($sqid);

        if (count($numbers) !== 1 || $this->encode($numbers[0]) !== $sqid) {
            return null;
        }

        return $numbers[0];
    }

    public function encode(int $id): string
    {
        return $this->sqids->encode([$id]);
    }
}
```

```php
<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Sqids
    |--------------------------------------------------------------------------
    |
    | Sqids are computed from internal integer identifiers before they are
    | exposed publicly. The package's default alphabet and blocklist remain
    | unchanged, while this value sets the minimum public identifier length
    | across every model that uses the HasSqid concern.
    |
    */

    'sqids' => [
        'min_length' => 10,
    ],

];
```

```php
<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use Override;
use Sqids\Sqids;
use Sqids\SqidsInterface;

final class SqidsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @return list<class-string>
     */
    #[Override]
    public function provides(): array
    {
        return [SqidsInterface::class];
    }

    #[Override]
    public function register(): void
    {
        $this->app->singleton(fn (): SqidsInterface => new Sqids(
            minLength: config()->integer('vetdesk.sqids.min_length'),
        ));
    }
}
```

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

```php
<?php

declare(strict_types=1);

namespace Tests\Support\Models;

use App\Models\Concerns\HasDeactivation;
use App\Models\Concerns\HasSqid;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Table('test_models', timestamps: false)]
final class TestModel extends Model implements HasMedia
{
    use HasDeactivation;
    use HasSqid;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * @return HasMany<TestModel, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'deactivated_at' => 'datetime',
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('test_models', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('parent_id')->nullable()->index();

            $table->timestamp('deactivated_at')->nullable();
            $table->softDeletes();
        });
    }
};
```

```php
<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithCachedConfig;
use Illuminate\Foundation\Testing\WithCachedRoutes;
use Illuminate\Support\Facades\Http;
use Tests\Support\Models\TestModel;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase {
        migrateFreshUsing as baseMigrateFreshUsing;
    }
    use WithCachedConfig;
    use WithCachedRoutes;

    protected function migrateFreshUsing(): array
    {
        return array_merge($this->baseMigrateFreshUsing(), [
            '--path' => [
                'database/migrations',
                'tests/migrations',
            ],
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        Relation::enforceMorphMap([
            'test_model' => TestModel::class,
        ]);
    }
}
```

```php
<?php

declare(strict_types=1);
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Support\Models\TestModel;

it('computes sqids without persisting them', function (): void {
    $model = TestModel::query()->create();

    expect($model->sqid)
        ->toBeString()
        ->toHaveLength(10)
        ->and($model->getAttributes())
        ->not->toHaveKey('sqid');
});

it('finds models by sqid', function (): void {
    $model = TestModel::query()->create();

    expect($model->is(TestModel::findBySqid($model->sqid)))
        ->toBeTrue()
        ->and(TestModel::findBySqid('invalid'))
        ->toBeNull();
});

it('throws 404 when model is not found by sqid', function (): void {
    TestModel::findOrFailBySqid('invalid');
})->throws(ModelNotFoundException::class);
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\get;

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;
use Tests\Support\Models\TestModel;

it('uses sqids to resolve route bindings', function (): void {
    $model = TestModel::query()->create();

    Route::middleware(SubstituteBindings::class)
        ->get('/_test/{model}', fn (TestModel $model): string => $model->sqid);

    $response = get('/_test/'.$model->id);

    $response->assertNotFound();

    $response = get('/_test/'.$model->sqid);

    $response->assertOk();
});

it('preserves explicit binding fields', function (): void {
    $model = TestModel::query()->create();

    Route::middleware(SubstituteBindings::class)
        ->get('/_test/{model:id}', fn (TestModel $model): int => $model->id);

    $response = get('/_test/'.$model->id);

    $response->assertOk();
});

it('uses sqids inside scoped child bindings', function (): void {
    $parent = TestModel::query()->create();
    $unrelatedParent = TestModel::query()->create();
    $child = TestModel::query()->create(['parent_id' => $parent->id]);

    Route::middleware(SubstituteBindings::class)
        ->get(
            '/_test/{model}/children/{child}',
            fn (TestModel $model, TestModel $child): string => $child->sqid,
        )
        ->scopeBindings();

    $response = get('/_test/'.$parent->sqid.'/children/'.$child->sqid);

    $response->assertOk();

    $response = get('/_test/'.$unrelatedParent->sqid.'/children/'.$child->sqid);

    $response->assertNotFound();
});
```
