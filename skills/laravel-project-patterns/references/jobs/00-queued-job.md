# Jobs: Queued Job

Run work for many records in the background, such as backfilling every tenant after adding a role or permission, by iterating in chunks and delegating each record to the actions the application already uses.

- Implement `ShouldQueueAfterCommit` so the job never runs before the transaction that dispatched it commits.
- Inject actions into `handle()` and iterate with `eachById()`.
- Check the queue contract in a unit test and run `handle()` with real actions in the integration suite.

```php
<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Clinics\EnsureClinicRoles;
use App\Actions\VisitStatuses\EnsureInitialVisitStatus;
use App\Models\Clinic;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Foundation\Queue\Queueable;

final class EnsureClinicDefaults implements ShouldQueueAfterCommit
{
    use Queueable;

    public function handle(
        EnsureClinicRoles $ensureClinicRoles,
        EnsureInitialVisitStatus $ensureInitialVisitStatus,
    ): void {
        Clinic::query()->eachById(
            function (Clinic $clinic) use ($ensureClinicRoles, $ensureInitialVisitStatus): void {
                $ensureClinicRoles->handle($clinic);
                $ensureInitialVisitStatus->handle($clinic);
            },
        );
    }
}
```

```php
<?php

declare(strict_types=1);

use App\Jobs\EnsureClinicDefaults;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

it('implements ShouldQueueAfterCommit', function (): void {
    expect(new EnsureClinicDefaults())->toBeInstanceOf(ShouldQueueAfterCommit::class);
});
```

```php
<?php

declare(strict_types=1);

use App\Enums\MembershipRole;
use App\Jobs\EnsureClinicDefaults;
use App\Models\Clinic;
use Spatie\Permission\Models\Role;

it('ensures the roles and initial visit status of every clinic', function (): void {
    $clinics = Clinic::factory()->count(2)->create();

    Role::query()->delete();

    dispatch_sync(new EnsureClinicDefaults());

    foreach ($clinics as $clinic) {
        expect(
            Role::query()->where('clinic_id', $clinic->id)->pluck('name')->sort()->values()->all(),
        )
            ->toBe(MembershipRole::values())
            ->and($clinic->visitStatuses()->where('is_initial', true)->exists())
            ->toBeTrue();
    }
});
```
