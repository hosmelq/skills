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
