# Actions: Ensure Action

Idempotently guarantee a tenant's required record: return the existing one, promote a suitable record through another action, or create a default.

```php
<?php

declare(strict_types=1);

namespace App\Actions\VisitStatuses;

use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Support\Facades\DB;

final readonly class EnsureInitialVisitStatus
{
    public function __construct(private SetInitialVisitStatus $setInitialVisitStatus) {}

    public function handle(Clinic $clinic): VisitStatus
    {
        return DB::transaction(function () use ($clinic): VisitStatus {
            $initialVisitStatus = $clinic->visitStatuses()
                ->where('is_initial', true)
                ->where('stage', VisitStage::CheckedIn)
                ->whereNull('deactivated_at')
                ->first();

            if ($initialVisitStatus instanceof VisitStatus) {
                return $initialVisitStatus;
            }

            $checkedInVisitStatus = $clinic->visitStatuses()
                ->where('stage', VisitStage::CheckedIn)
                ->whereNull('deactivated_at')
                ->ordered()
                ->first();

            if ($checkedInVisitStatus instanceof VisitStatus) {
                return $this->setInitialVisitStatus->handle($checkedInVisitStatus);
            }

            return $clinic->visitStatuses()->create([
                'is_initial' => true,
                'name' => trans()->string('visit_status.defaults.initial_name'),
                'stage' => VisitStage::CheckedIn,
            ]);
        });
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\assertDatabaseHas;

use App\Actions\VisitStatuses\EnsureInitialVisitStatus;
use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;

it('creates the initial visit status for a clinic', function (): void {
    $clinic = Clinic::factory()->createOne();
    $visitStatus = resolve(EnsureInitialVisitStatus::class)->handle($clinic);

    assertDatabaseHas(VisitStatus::class, [
        'id' => $visitStatus->id,
        'clinic_id' => $clinic->id,
        'is_initial' => true,
        'name' => 'Checked in',
        'stage' => VisitStage::CheckedIn,
    ]);

    expect($clinic->visitStatuses()->count())->toBe(1);
});

it('is idempotent when an initial status already exists', function (): void {
    $visitStatus = VisitStatus::factory()->initial()->createOne();
    $resolvedVisitStatus = resolve(EnsureInitialVisitStatus::class)->handle($visitStatus->clinic);

    expect($resolvedVisitStatus->is($visitStatus))->toBeTrue()
        ->and($visitStatus->clinic->visitStatuses()->count())->toBe(1);
});

it('promotes an existing active checked-in status instead of creating a duplicate', function (): void {
    $visitStatus = VisitStatus::factory()->createOne();
    $laterVisitStatus = VisitStatus::factory()->recycle($visitStatus->clinic)->createOne();
    $resolvedVisitStatus = resolve(EnsureInitialVisitStatus::class)->handle(
        $visitStatus->clinic,
    );

    assertDatabaseHas(VisitStatus::class, [
        'id' => $visitStatus->id,
        'is_initial' => true,
    ]);
    assertDatabaseHas(VisitStatus::class, [
        'id' => $laterVisitStatus->id,
        'is_initial' => false,
    ]);

    expect($resolvedVisitStatus->is($visitStatus))->toBeTrue()
        ->and($visitStatus->clinic->visitStatuses()->count())->toBe(2);
});
```
