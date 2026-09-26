# Actions: Approve or Reject Enrollment

Pending-only review metadata updates with opposite-state rejection. Approval also provisions already-approved requests; rejection of an already-rejected request leaves it unchanged.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Cabinets;

use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotReviewEnrollment;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ApproveEnrollment
{
    public function __construct(private readonly ProvisionMemberCabinets $provisionMemberCabinets)
    {
    }

    public function handle(Enrollment $enrollment, User $reviewedByUser): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $reviewedByUser): Enrollment {
            if ($enrollment->status === EnrollmentStatus::Rejected) {
                throw CannotReviewEnrollment::becauseItWasRejected();
            }

            if ($enrollment->status === EnrollmentStatus::Pending) {
                $enrollment->update([
                    'reviewed_at' => now(),
                    'reviewed_by_user_id' => $reviewedByUser->id,
                    'status' => EnrollmentStatus::Approved,
                ]);
            }

            $this->provisionMemberCabinets->handle($enrollment);

            return $enrollment;
        });
    }
}
```

```php
<?php

declare(strict_types=1);

namespace App\Actions\Cabinets;

use App\Enums\EnrollmentStatus;
use App\Exceptions\CannotReviewEnrollment;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RejectEnrollment
{
    public function handle(Enrollment $enrollment, User $reviewedByUser): Enrollment
    {
        return DB::transaction(function () use ($enrollment, $reviewedByUser): Enrollment {
            if ($enrollment->status === EnrollmentStatus::Approved) {
                throw CannotReviewEnrollment::becauseItWasApproved();
            }

            if ($enrollment->status === EnrollmentStatus::Pending) {
                $enrollment->update([
                    'reviewed_at' => now(),
                    'reviewed_by_user_id' => $reviewedByUser->id,
                    'status' => EnrollmentStatus::Rejected,
                ]);
            }

            return $enrollment;
        });
    }
}
```
