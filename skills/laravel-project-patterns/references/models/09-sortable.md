# Models: Sortable Records

Keep a manual display order with Spatie Eloquent Sortable when records are ordered within a group defined by one or more columns, and test that each group numbers its records independently in the integration suite.

- Include `SortableTrait` in the `uses traits` test.
- The order column is `sort_order`, set through `order_column_name` in `config/eloquent-sortable.php`.

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VisitStage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property-read int $clinic_id
 * @property-read int $sort_order
 * @property-read VisitStage $stage
 */
final class VisitStatus extends Model implements Sortable
{
    use SortableTrait;

    /**
     * @return Builder<static>
     */
    public function buildSortQuery(): Builder
    {
        return self::query()->where('clinic_id', $this->clinic_id)->where('stage', $this->stage);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'stage' => VisitStage::class,
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;

it('assigns sort order within each clinic and stage group', function (): void {
    $firstClinic = Clinic::factory()->createOne();
    $secondClinic = Clinic::factory()->createOne();
    $checkedIn = VisitStatus::factory()
        ->recycle($firstClinic)
        ->createOne(['stage' => VisitStage::CheckedIn]);
    $completed = VisitStatus::factory()
        ->recycle($firstClinic)
        ->createOne(['stage' => VisitStage::Completed]);
    $otherClinic = VisitStatus::factory()
        ->recycle($secondClinic)
        ->createOne(['stage' => VisitStage::CheckedIn]);
    $secondCheckedIn = VisitStatus::factory()
        ->recycle($firstClinic)
        ->createOne(['stage' => VisitStage::CheckedIn]);

    assertDatabaseHas(VisitStatus::class, [
        'id' => $checkedIn->id,
        'sort_order' => 1,
    ]);
    assertDatabaseHas(VisitStatus::class, [
        'id' => $completed->id,
        'sort_order' => 1,
    ]);
    assertDatabaseHas(VisitStatus::class, [
        'id' => $otherClinic->id,
        'sort_order' => 1,
    ]);
    assertDatabaseHas(VisitStatus::class, [
        'id' => $secondCheckedIn->id,
        'sort_order' => 2,
    ]);
});
```
