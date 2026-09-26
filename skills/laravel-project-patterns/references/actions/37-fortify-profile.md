# Actions: Fortify Profile Updates

Validate the profile in its named error bag, ignore the current user for email uniqueness and clear verification only when the email changes. Send verification notification after saving the changed email.

```php
<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param array{email: string, first_name: string, last_name: string} $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'email' => $input['email'],
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
            ])->save();
        }
    }

    /**
     * @param array{email: string, first_name: string, last_name: string} $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'email' => $input['email'],
            'email_verified_at' => null,
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
```
