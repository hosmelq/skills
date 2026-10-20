# Actions: Set Initial Action

Make one record the tenant's single flagged record, clearing the flag on the others, after checking the model predicate.

- Throw a domain exception with a named constructor when the predicate fails.
- Clear the flag on every other record, including deactivated and soft deleted ones, before setting it.
- Record an `AuditLogEvent` case, added alphabetically, inside the transaction, targeting the record and its tenant.

```php
<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

final class CannotSetInitialVisitStatus extends Exception
{
    public static function becauseItIsNotActiveCheckedIn(): self
    {
        return new self('Only an active checked-in visit status can be initial.');
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\VisitStatuses;

use function HosmelQ\AuditLog\audit_log;

use App\Enums\AuditLogEvent;
use App\Exceptions\CannotSetInitialVisitStatus;
use App\Models\VisitStatus;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final readonly class SetInitialVisitStatus
{
    public function handle(VisitStatus $visitStatus): VisitStatus
    {
        return DB::transaction(function () use ($visitStatus): VisitStatus {
            if (! $visitStatus->canBeInitial()) {
                throw CannotSetInitialVisitStatus::becauseItIsNotActiveCheckedIn();
            }

            VisitStatus::query()
                ->withTrashed()
                ->where('clinic_id', $visitStatus->clinic_id)
                ->where('is_initial', true)
                ->update(['is_initial' => false]);

            $visitStatus->update(['is_initial' => true]);

            /** @var array<string, null|bool|float|int|string> $before */
            $before = Arr::only($visitStatus->getPrevious(), 'is_initial');
            /** @var array<string, null|bool|float|int|string> $after */
            $after = Arr::only($visitStatus->getChanges(), 'is_initial');

            audit_log(AuditLogEvent::VisitStatusInitialSet)
                ->changes(before: $before, after: $after)
                ->target($visitStatus)
                ->tenant($visitStatus->clinic_id)
                ->record();

            return $visitStatus->refresh();
        });
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\VisitStatuses\SetInitialVisitStatus;
use App\Enums\VisitStage;
use App\Exceptions\CannotSetInitialVisitStatus;
use App\Models\VisitStatus;

it('rejects visit statuses that cannot be initial', function (array $attributes): void {
    $visitStatus = VisitStatus::factory()->createOne($attributes);

    expect(fn () => resolve(SetInitialVisitStatus::class)->handle($visitStatus))
        ->toThrow(
            CannotSetInitialVisitStatus::class,
            'Only an active checked-in visit status can be initial.',
        );

    assertDatabaseHas(VisitStatus::class, [
        'id' => $visitStatus->id,
        'is_initial' => false,
    ]);
})->with([
    'deactivated' => [['deactivated_at' => '2026-03-30 15:33:00']],
    'other stage' => [['stage' => VisitStage::Completed]],
]);

it('sets an active checked-in visit status as the only initial status', function (): void {
    $initialVisitStatus = VisitStatus::factory()->initial()->createOne();
    $newInitialVisitStatus = VisitStatus::factory()
        ->recycle($initialVisitStatus->clinic)
        ->createOne();
    $resolvedVisitStatus = resolve(SetInitialVisitStatus::class)->handle($newInitialVisitStatus);

    assertDatabaseHas(VisitStatus::class, [
        'id' => $initialVisitStatus->id,
        'is_initial' => false,
    ]);
    assertDatabaseHas(VisitStatus::class, [
        'id' => $newInitialVisitStatus->id,
        'is_initial' => true,
    ]);

    expect($resolvedVisitStatus->is($newInitialVisitStatus))->toBeTrue();
});
```
