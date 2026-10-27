# Requests: Partial Update Request

Validate a PATCH that may send any subset of fields, with a rule in `after()` that depends on the record's state and adds a field error.

- Mark optional fields `sometimes`, keeping `required` on fields that cannot be cleared.
- Skip the `after()` check when the field is absent or already failed, so each field reports one error.
- Test validation through the endpoint in the controller's feature test, inside the `describe` block of the action.

```php
<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\VisitStage;
use App\Models\Clinic;
use App\Models\VisitStatus;
use Closure;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Stringable;

final class UpdateVisitStatusRequest extends FormRequest
{
    /**
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! $this->has('stage')) {
                    return;
                }

                $visitStatus = $this->route('visit_status');

                assert($visitStatus instanceof VisitStatus);

                if (
                    ! $visitStatus->is_initial
                    || $this->enum('stage', VisitStage::class) === VisitStage::CheckedIn
                ) {
                    return;
                }

                $validator->errors()->add(
                    'stage',
                    trans()->string('visit_status.validation.initial_must_be_checked_in'),
                );
            },
        ];
    }

    /**
     * @return array<string, list<string|Stringable>>
     */
    public function rules(
        #[RouteParameter('clinic')] Clinic $clinic,
        #[RouteParameter('visit_status')] VisitStatus $visitStatus,
    ): array {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique(VisitStatus::class)
                    ->ignore($visitStatus)
                    ->where('clinic_id', $clinic->id)
                    ->withoutTrashed(),
            ],
            'stage' => ['sometimes', 'required', Rule::enum(VisitStage::class)],
        ];
    }
}
```

```php
<?php

declare(strict_types=1);

use function Pest\Laravel\patch;

use App\Enums\VisitStage;
use App\Models\VisitStatus;

describe('update', function (): void {
    it('returns only the enum error when updating an initial status with an invalid stage', function (): void {
        $visitStatus = VisitStatus::factory()->initial()->createOne();

        login(clinic: $visitStatus->clinic);

        $response = patch(route('clinics.visit-statuses.update', [
            'clinic' => $visitStatus->clinic,
            'visit_status' => $visitStatus,
        ]), [
            'stage' => 'invalid',
        ]);

        $response->assertRedirectBackWithErrors([
            'stage' => 'The selected stage is invalid.',
        ]);

        expect(session('errors')->getBag('default')->get('stage'))->toBe([
            'The selected stage is invalid.',
        ]);
    });

    it('prevents changing the initial status away from checked in', function (): void {
        $visitStatus = VisitStatus::factory()->initial()->createOne();

        login(clinic: $visitStatus->clinic);

        $response = patch(route('clinics.visit-statuses.update', [
            'clinic' => $visitStatus->clinic,
            'visit_status' => $visitStatus,
        ]), [
            'stage' => VisitStage::Completed(),
        ]);

        $response->assertRedirectBackWithErrors([
            'stage' => 'The initial visit status must use the Checked in stage.',
        ]);
    });
});
```
