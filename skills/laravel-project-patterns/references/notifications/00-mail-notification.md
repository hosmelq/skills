# Notifications: Mail Notification

Send a translated mail notification, queued after the surrounding transaction commits, built from the public model it receives.

- Implement `ShouldQueueAfterCommit` so the job never runs before the data it reads is committed.
- Keep every string in the translation file of its feature.
- Send to addresses without a user through `Notification::route('mail', $email)`.
- Test the message by building it in the feature suite, not by sending it.

```php
<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\OneTimePassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class OneTimePasswordNotification extends Notification implements ShouldQueueAfterCommit
{
    use Queueable;

    public function __construct(public readonly OneTimePassword $otp) {}

    public function toMail(): MailMessage
    {
        return new MailMessage()
            ->subject(trans()->string('auth.one_time_password.subject'))
            ->greeting(trans()->string('auth.one_time_password.greeting'))
            ->line(trans()->string('auth.one_time_password.line', ['code' => $this->otp->code]))
            ->line(trans()->string('auth.one_time_password.ignore'));
    }

    /**
     * @return list<string>
     */
    public function via(): array
    {
        return ['mail'];
    }
}
```

```php
<?php

declare(strict_types=1);

return [

    'one_time_password' => [
        'greeting' => 'Here is your sign-in code',
        'ignore' => "If you didn't request this, you can ignore this email.",
        'line' => 'Use this code to finish signing in: :code',
        'subject' => 'Your sign-in code',
    ],

];
```

```php
<?php

declare(strict_types=1);

use App\Models\OneTimePassword;
use App\Notifications\OneTimePasswordNotification;

it('sends the sign-in code by mail', function (): void {
    $otp = OneTimePassword::factory()->makeOne([
        'code' => '123456',
    ]);
    $notification = new OneTimePasswordNotification($otp);
    $message = $notification->toMail();

    expect($notification->via())->toBe(['mail'])
        ->and($message->subject)->toBe('Your sign-in code')
        ->and($message->greeting)->toBe('Here is your sign-in code')
        ->and($message->introLines)->toBe([
            'Use this code to finish signing in: 123456',
            "If you didn't request this, you can ignore this email.",
        ]);
});
```
