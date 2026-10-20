# Actions: Move Action

Reorder a sortable record after another record of its sort group, or to the start when no predecessor is given.

- Record an `AuditLogEvent` case, added alphabetically, inside the transaction, targeting the record and its tenant.

```php
<?php

declare(strict_types=1);

namespace App\Actions\VisitStatuses;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Models\VisitStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class MoveVisitStatus
{
    public function handle(VisitStatus $visitStatus, null|VisitStatus $afterVisitStatus): void
    {
        DB::transaction(function () use ($afterVisitStatus, $visitStatus): void {
            if ($afterVisitStatus instanceof VisitStatus) {
                $visitStatus->moveAfter($afterVisitStatus);
            } else {
                $visitStatus->moveToStart();
            }

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($visitStatus->getPrevious(), 'sort_order');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($visitStatus->getChanges(), 'sort_order');

            audit_log(AuditLogEvent::VisitStatusMoved)
                ->changes(before: $before, after: $after)
                ->target($visitStatus)
                ->tenant($visitStatus->clinic_id)
                ->record();
        });
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Actions\VisitStatuses\MoveVisitStatus;
use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;

it('moves a visit status after another visit status', function (): void {
    $clinic = Clinic::factory()->createOne();

    [$firstVisitStatus, $secondVisitStatus, $thirdVisitStatus] = VisitStatus::factory()
        ->count(3)
        ->recycle($clinic)
        ->create();

    $otherStageVisitStatus = VisitStatus::factory()->recycle($clinic)->createOne([
        'stage' => VisitStage::Completed,
    ]);

    resolve(MoveVisitStatus::class)->handle($firstVisitStatus, $secondVisitStatus);

    $checkedInVisitStatusIds = $clinic->visitStatuses()
        ->where('stage', VisitStage::CheckedIn)
        ->ordered()
        ->pluck('id')
        ->all();
    $completedVisitStatusIds = $clinic->visitStatuses()
        ->where('stage', VisitStage::Completed)
        ->ordered()
        ->pluck('id')
        ->all();

    expect($checkedInVisitStatusIds)->toBe([
        $secondVisitStatus->id,
        $firstVisitStatus->id,
        $thirdVisitStatus->id,
    ])->and($completedVisitStatusIds)->toBe([$otherStageVisitStatus->id]);
});

it('moves a visit status to the start when no predecessor is supplied', function (): void {
    $clinic = Clinic::factory()->createOne();

    [$firstVisitStatus, $secondVisitStatus] = VisitStatus::factory()
        ->count(2)
        ->recycle($clinic)
        ->create();

    resolve(MoveVisitStatus::class)->handle($secondVisitStatus, null);

    $visitStatusIds = $clinic->visitStatuses()->ordered()->pluck('id')->all();

    expect($visitStatusIds)->toBe([
        $secondVisitStatus->id,
        $firstVisitStatus->id,
    ]);
});
```
