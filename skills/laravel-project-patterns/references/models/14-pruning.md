# Models: Prunable Records

Implement a Prunable model that selects unused expired records or records used at least one month ago. Keep the two grouped branches and a single captured clock value.

This expression assumes `Date::use(CarbonImmutable::class)` in the bootstrap, so `subMonth()` does not mutate the captured clock. The model declares eligibility; scheduling pruning is separate.

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
class OneTimePassword extends Model
{
    use Prunable;

    /**
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        $now = now();

        return static::query()->where(static function (Builder $query) use ($now): void {
            $query->where('expires_at', '<=', $now)
                ->whereNull('used_at');
        })->orWhere(static function (Builder $query) use ($now): void {
            $query->whereNotNull('used_at')
                ->where('used_at', '<=', $now->subMonth());
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
