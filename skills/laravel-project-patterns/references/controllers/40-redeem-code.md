# Controllers: Redeem a One-Time Code

Match code, email, future expiry and unused state before marking use. Create or verify the local user, then return the token and resource. This sequence has no lock or transaction and does not demonstrate atomic redemption.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\EmailOtpLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\OneTimePassword;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmailAuthenticatedSessionController
{
    public function __invoke(EmailOtpLoginRequest $request): JsonResponse
    {
        $otp = OneTimePassword::query()
            ->where('code', $request->string('code'))
            ->where('email', $email = $request->string('email'))
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        throw_if($otp === null, ValidationException::withMessages([
            'code' => __('validation.exists', ['attribute' => 'code']),
        ]));

        $otp->update(['used_at' => now()]);

        $user = User::query()->firstWhere('email', $email);

        if ($user === null) {
            $user = User::query()->create([
                'email' => (string) $email,
                'email_verified_at' => now(),
            ]);
        } elseif ($user->email_verified_at === null) {
            $user->update(['email_verified_at' => now()]);
        }

        $token = $user->createToken('Mobile')->plainTextToken;

        return new JsonResponse([
            'access_token' => Str::of($token)->explode('|')->last(),
            'user' => UserResource::make($user),
        ]);
    }
}
```
