# Notifications: Queue a Login Code

Keep the code model readonly, use `ShouldQueue` and `Queueable`, and return only the mail channel. The example leaves queue connection and transaction timing to application configuration.

```php
<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\OneTimePassword;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OneTimePasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly OneTimePassword $otp)
    {
    }

    public function toMail(): MailMessage
    {
        return new MailMessage()
            ->subject('Your login code')
            ->greeting('Here is your login code')
            ->line('Use this code to log in: '.$this->otp->code)
            ->line("If you didn't request this, you can ignore this email.");
    }

    /**
     * @return array<int, string>
     */
    public function via(): array
    {
        return ['mail'];
    }
}
```

The [request-code controller](../controllers/39-request-code.md) generates the model and routes this notification to an email. See [queued notifications](https://laravel.com/docs/13.x/notifications#queueing-notifications).
