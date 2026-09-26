# Controllers: Dispatch a Validated Decision

Inject the reviewing user and dispatch each allowed enum decision to its action and toast. Translate already-reviewed failures; preserve the unreachable-state exception after validated input.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use function App\__;

use App\Actions\Cabinets\ApproveEnrollment;
use App\Actions\Cabinets\RejectEnrollment;
use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotReviewEnrollment;
use App\Http\Requests\UpdateEnrollmentStatusRequest;
use App\Models\Enrollment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\ValidationException;
use LogicException;

class EnrollmentStatusController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:update,enrollment'),
        ];
    }

    public function update(
        UpdateEnrollmentStatusRequest $request,
        Team $team,
        Enrollment $enrollment,
        #[CurrentUser] User $reviewedByUser,
        ApproveEnrollment $approveEnrollment,
        RejectEnrollment $rejectEnrollment,
    ): RedirectResponse {
        /** @var EnrollmentStatus $status */
        $status = $request->enum('status', EnrollmentStatus::class);

        try {
            if ($status === EnrollmentStatus::Approved) {
                $approveEnrollment->handle($enrollment, $reviewedByUser);

                return back()->toast(__('enrollment.approved.title'));
            }

            if ($status === EnrollmentStatus::Rejected) {
                $rejectEnrollment->handle($enrollment, $reviewedByUser);

                return back()->toast(__('enrollment.rejected.title'));
            }
        } catch (CannotReviewEnrollment) {
            throw ValidationException::withMessages([
                'enrollment' => __('enrollment.validation.already_reviewed'),
            ]);
        }

        throw new LogicException('A pending enrollment cannot be reviewed as pending.');
    }
}
```
