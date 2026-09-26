# Controllers: Return an API Action Result

Inject the current user, delegate the action, map its failure message to the enrollment field and return the API resource.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Cabinets\RequestEnrollment;
use App\Exceptions\CannotRequestEnrollment;
use App\Http\Resources\Api\EnrollmentResource;
use App\Models\Team;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Validation\ValidationException;

class EnrollmentController
{
    public function store(
        Team $team,
        #[CurrentUser] User $user,
        RequestEnrollment $requestEnrollment,
    ): EnrollmentResource {
        try {
            $enrollment = $requestEnrollment->handle($team, $user);
        } catch (CannotRequestEnrollment $exception) {
            throw ValidationException::withMessages([
                'cabinet_enrollment' => $exception->getMessage(),
            ]);
        }

        return EnrollmentResource::make($enrollment);
    }
}
```
