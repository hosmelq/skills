# Controllers: Single Action Controller

Handle an operation that is not a resource method, such as reordering, with an invokable controller that resolves the related record inside the tenant and the record's group, responding 404 when it is outside them.

- Decode Sqid inputs with the `sqids` middleware and read the validated ids with a typed `@var` array shape.
- Its tests stay at file scope.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\VisitStatuses\MoveVisitStatus;
use App\Http\Requests\MoveVisitStatusRequest;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

final class MoveVisitStatusController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,visit_status'),
            new Middleware('sqids:move_after_id'),
        ];
    }

    public function __invoke(
        MoveVisitStatusRequest $request,
        Clinic $clinic,
        VisitStatus $visitStatus,
        MoveVisitStatus $moveVisitStatus,
    ): RedirectResponse {
        /** @var array{move_after_id?: null|int} $validated */
        $validated = $request->validated();
        $moveAfterVisitStatus = null;

        if (($validated['move_after_id'] ?? null) !== null) {
            $moveAfterVisitStatus = $clinic->visitStatuses()
                ->where('stage', $visitStatus->stage)
                ->whereKey($validated['move_after_id'])
                ->firstOrFail();
        }

        $moveVisitStatus->handle($visitStatus, $moveAfterVisitStatus);

        return Inertia::flash('toast', [
            'title' => trans()->string('visit_status.moved.title'),
        ])->back();
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;

it('returns not found for predecessor visit statuses from another stage group', function (): void {
    $clinic = Clinic::factory()->createOne();
    $visitStatus = VisitStatus::factory()->recycle($clinic)->createOne();
    $moveAfterVisitStatus = VisitStatus::factory()->recycle($clinic)->createOne([
        'stage' => VisitStage::Completed,
    ]);

    login(clinic: $clinic);

    $response = patch(route('clinics.visit-statuses.move', [
        'clinic' => $clinic,
        'visit_status' => $visitStatus,
    ]), [
        'move_after_id' => $moveAfterVisitStatus->sqid,
    ]);

    $response->assertNotFound();
});

it('moves a visit status after another status within the selected stage group', function (): void {
    $clinic = Clinic::factory()->createOne();

    [$firstVisitStatus, $secondVisitStatus] = VisitStatus::factory()
        ->count(2)
        ->recycle($clinic)
        ->create();

    login(clinic: $clinic);

    $response = patch(route('clinics.visit-statuses.move', [
        'clinic' => $clinic,
        'visit_status' => $firstVisitStatus,
    ]), [
        'move_after_id' => $secondVisitStatus->sqid,
    ]);

    $response->assertRedirect()->assertInertiaFlash('toast.title', 'Visit status moved');

    expect($clinic->visitStatuses()->ordered()->pluck('id')->all())->toBe([
        $secondVisitStatus->id,
        $firstVisitStatus->id,
    ]);
});
```
