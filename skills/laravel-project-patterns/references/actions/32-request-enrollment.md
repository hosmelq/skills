# Actions: Request or Resubmit Enrollment

Check feature and verified email, reuse member identity and existing enrollment, reset rejected requests, and provision approved results inside the transaction. Preserve existing pending or approved review metadata.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Cabinets;

use App\Enums\AssignmentMode;
use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotRequestEnrollment;
use App\Models\Enrollment;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RequestEnrollment
{
    public function __construct(private readonly ProvisionMemberCabinets $provisionMemberCabinets)
    {
    }

    public function handle(Team $team, User $user): Enrollment
    {
        if (! $team->cabinets_enabled) {
            throw CannotRequestEnrollment::becauseCabinetsAreDisabled();
        }

        if ($user->email_verified_at === null) {
            throw CannotRequestEnrollment::becauseEmailIsNotVerified();
        }

        return DB::transaction(function () use ($team, $user): Enrollment {
            $member = $team->members()->firstOrCreate(
                ['email' => $user->email],
                [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                ],
            );

            $requestedStatus = $team->assignment_mode === AssignmentMode::Instant
                ? EnrollmentStatus::Approved
                : EnrollmentStatus::Pending;

            $enrollmentAttributes = [
                'requested_by_user_id' => $user->id,
                'reviewed_by_user_id' => null,
                'requested_at' => now(),
                'reviewed_at' => null,
                'status' => $requestedStatus,
            ];

            $enrollment = $member->enrollment()->firstOrCreate(
                ['team_id' => $team->id],
                $enrollmentAttributes,
            );

            if ($enrollment->status === EnrollmentStatus::Rejected) {
                $enrollment->update($enrollmentAttributes);
            }

            if ($enrollment->status === EnrollmentStatus::Approved) {
                $this->provisionMemberCabinets->handle($enrollment);
            }

            return $enrollment;
        });
    }
}
```
