# Models: Scoped Sorting and Status Eligibility

Implement Spatie Sortable models with a team-scoped or base-status-and-team-scoped sort query. Status eligibility requires an active, nontrashed model in the allowed base state.

These examples require `order_column_name => 'sort_order'` and `sort_when_creating => true` in `config/eloquent-sortable.php`. Grouping constrains the sort query; it does not enforce database uniqueness.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasDeactivation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class ItemGroup extends Model implements Sortable
{
    use HasDeactivation;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @return Builder<static>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()->where('team_id', $this->team_id);
    }

    #[Override]
    protected function casts(): array
    {
        return ['deactivated_at' => 'datetime'];
    }
}
```

This status predicate also checks `trashed()`. Do not replace it with the timestamp-only concern. The two false defaults are independent.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BaseStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class WorkOrderStatus extends Model implements Sortable
{
    use SoftDeletes;
    use SortableTrait;

    #[Override]
    protected $attributes = [
        'is_initial' => false,
        'is_member_visible' => false,
    ];

    /**
     * @return Builder<static>
     */
    public function buildSortQuery(): Builder
    {
        return static::query()
            ->where('base_status', $this->base_status)
            ->where('team_id', $this->team_id);
    }

    public function canBeInitial(): bool
    {
        return $this->isActive() && $this->base_status === BaseStatus::Open;
    }

    public function isActive(): bool
    {
        return $this->deactivated_at === null && ! $this->trashed();
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'base_status' => BaseStatus::class,
            'deactivated_at' => 'datetime',
            'is_initial' => 'boolean',
            'is_member_visible' => 'boolean',
        ];
    }
}
```
