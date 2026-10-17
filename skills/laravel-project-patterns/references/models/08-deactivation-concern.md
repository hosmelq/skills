# Models: Deactivation

Let records be deactivated and reactivated through a nullable `deactivated_at` timestamp with the `HasDeactivation` concern, which adds state checks and `active`/`deactivated` scopes; covers using the concern in a model and creating it.

- Include `HasDeactivation` in the model's `uses traits` test.
- Test the concern in the integration suite through the test-only model from the Sqid concern reference.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasDeactivation;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Override;

/**
 * @property-read null|CarbonImmutable $deactivated_at
 */
final class Room extends Model
{
    use HasDeactivation;

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

namespace App\Models\Concerns;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property-read null|CarbonImmutable $deactivated_at
 */
trait HasDeactivation
{
    public function deactivate(): void
    {
        if ($this->isActive()) {
            $this->update(['deactivated_at' => now()]);
        }
    }

    public function isActive(): bool
    {
        return $this->deactivated_at === null;
    }

    public function isDeactivated(): bool
    {
        return $this->deactivated_at !== null;
    }

    public function reactivate(): void
    {
        if ($this->isDeactivated()) {
            $this->update(['deactivated_at' => null]);
        }
    }

    /**
     * @param Builder<static> $builder
     */
    #[Scope]
    protected function active(Builder $builder): void
    {
        $builder->whereNull($builder->qualifyColumn('deactivated_at'));
    }

    /**
     * @param Builder<static> $builder
     */
    #[Scope]
    protected function deactivated(Builder $builder): void
    {
        $builder->whereNotNull($builder->qualifyColumn('deactivated_at'));
    }
}
```

```php
<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Tests\Support\Models\TestModel;

it('scopes active and deactivated models', function (): void {
    $activeModel = TestModel::query()->create();
    $deactivatedModel = TestModel::query()->create(['deactivated_at' => now()]);
    $activeModelIds = TestModel::query()->active()->pluck('id')->all();
    $deactivatedModelIds = TestModel::query()->deactivated()->pluck('id')->all();

    expect($activeModelIds)
        ->toBe([$activeModel->id])
        ->and($deactivatedModelIds)
        ->toBe([$deactivatedModel->id]);
});

it('deactivates a model', function (): void {
    $model = TestModel::query()->create();

    $model->deactivate();

    $model->refresh();

    expect($model)->isDeactivated()->toBeTrue()->deactivated_at->not->toBeNull();
});

it('does not deactivate a deactivated model', function (): void {
    $model = TestModel::query()->create(['deactivated_at' => CarbonImmutable::today()->subDay()]);

    $model->deactivate();

    $model->refresh();

    expect($model)
        ->isDeactivated()->toBeTrue()
        ->deactivated_at->toEqual(CarbonImmutable::today()->subDay());
});

it('reactivates a model', function (): void {
    $model = TestModel::query()->create(['deactivated_at' => now()]);

    $model->reactivate();

    $model->refresh();

    expect($model)->isActive()->toBeTrue()->deactivated_at->toBeNull();
});
```
