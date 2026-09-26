# Controllers: Generate and Send a One-Time Code

Delegate code generation, route the notification to the email and return an empty JSON response. Keep the named request throttle on the route.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\GenerateOneTimePasswordCode;
use App\Http\Requests\Api\EmailOtpRequest;
use App\Notifications\OneTimePasswordNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class EmailOtpRequestController
{
    public function __invoke(
        EmailOtpRequest $request,
        GenerateOneTimePasswordCode $generateOneTimePasswordCode
    ): JsonResponse {
        $otp = $generateOneTimePasswordCode->handle(
            $email = (string) $request->string('email')
        );

        Notification::route('mail', $email)
            ->notify(new OneTimePasswordNotification($otp));

        return new JsonResponse();
    }
}
```
