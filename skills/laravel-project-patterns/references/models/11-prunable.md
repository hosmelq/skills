# Models: Prunable Records

Delete records automatically once they are no longer useful, with Laravel's `Prunable` trait and a query that selects them, and test which records it selects.

- Group `orWhere` branches in closures.
- Pruning runs only when `routes/console.php` schedules it: `Schedule::command('model:prune')->daily();`.
- Test in the unit suite with records on both sides of every boundary; time is frozen in tests.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Override;

/**
 * @property-read CarbonImmutable $expires_at
 * @property-read null|CarbonImmutable $used_at
 */
final class OneTimePassword extends Model
{
    use Prunable;

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        $now = now();

        return self::query()->where(static function (Builder $query) use ($now): void {
            $query->where('expires_at', '<=', $now)->whereNull('used_at');
        })->orWhere(static function (Builder $query) use ($now): void {
            $query->whereNotNull('used_at')->where('used_at', '<=', $now->subMonth());
        });
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Models\OneTimePassword;

it('identifies expired or month-old used passwords for pruning', function (): void {
    $active = OneTimePassword::factory()->createOne();
    $expiredUnused = OneTimePassword::factory()->expired()->createOne();
    $usedOlder = OneTimePassword::factory()->used()->createOne([
        'used_at' => now()->subMonth(),
    ]);
    $usedRecent = OneTimePassword::factory()->used()->createOne();
    $prunableIds = new OneTimePassword()->prunable()->pluck('id');

    expect($prunableIds)
        ->toContain($expiredUnused->id)
        ->toContain($usedOlder->id)
        ->not->toContain($active->id)
        ->not->toContain($usedRecent->id);
});
```
