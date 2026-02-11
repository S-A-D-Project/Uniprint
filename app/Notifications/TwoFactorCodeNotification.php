<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $code)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = (string) config('app.name');

        return (new MailMessage)
            ->subject($appName . ' verification code')
            ->greeting('Verification Required')
            ->line('Use the verification code below to complete your sign in:')
            ->line('')
            ->line('Code: ' . $this->code)
            ->line('')
            ->line('This code will expire in 15 minutes.')
            ->line('If you did not attempt to sign in, you can ignore this message.');
    }
}
