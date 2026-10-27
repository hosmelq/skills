# Requests: Move Request

Validate the predecessor sent when reordering a sortable record: a Sqid the `sqids` controller middleware decodes to an id, which must exist in the tenant, not be trashed and not be the record itself.

- Name the field in `validation.attributes` so messages read naturally.
- The endpoint is invokable, so its tests stay at file scope.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Clinic;
use App\Models\VisitStatus;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Stringable;

final class MoveVisitStatusRequest extends FormRequest
{
    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(
        #[RouteParameter('clinic')] Clinic $clinic,
        #[RouteParameter('visit_status')] VisitStatus $visitStatus,
    ): array {
        return [
            'move_after_id' => [
                'nullable',
                Rule::notIn([$visitStatus->id]),
                Rule::exists(VisitStatus::class, 'id')
                    ->where('clinic_id', $clinic->id)
                    ->withoutTrashed(),
            ],
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Models\VisitStatus;

it('rejects moving a visit status after itself', function (): void {
    $visitStatus = VisitStatus::factory()->createOne();

    login(clinic: $visitStatus->clinic);

    $response = patch(route('clinics.visit-statuses.move', [
        'clinic' => $visitStatus->clinic,
        'visit_status' => $visitStatus,
    ]), [
        'move_after_id' => $visitStatus->sqid,
    ]);

    $response->assertRedirectBackWithErrors([
        'move_after_id' => 'The selected previous item is invalid.',
    ]);
});

it('rejects visit statuses from another clinic as the predecessor', function (): void {
    $visitStatus = VisitStatus::factory()->createOne();
    $moveAfterVisitStatus = VisitStatus::factory()->createOne();

    login(clinic: $visitStatus->clinic);

    $response = patch(route('clinics.visit-statuses.move', [
        'clinic' => $visitStatus->clinic,
        'visit_status' => $visitStatus,
    ]), [
        'move_after_id' => $moveAfterVisitStatus->sqid,
    ]);

    $response->assertRedirectBackWithErrors([
        'move_after_id' => 'The selected previous item is invalid.',
    ]);
});
```
