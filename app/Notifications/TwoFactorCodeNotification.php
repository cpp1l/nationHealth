<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCodeNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $code  One-time login code sent to the user.
     * @param  int  $ttlMinutes  Number of minutes the code stays valid.
     */
    public function __construct(
        private readonly string $code,
        private readonly int $ttlMinutes
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  object  $notifiable
     * @return MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        return new MailMessage()
            ->subject(__('auth.login.two_factor.subject'))
            ->greeting(__('auth.login.two_factor.greeting'))
            ->line(__('auth.login.two_factor.code_line', ['code' => $this->code]))
            ->line(__('auth.login.two_factor.ttl_line', ['minutes' => $this->ttlMinutes]))
            ->line(__('auth.login.two_factor.ignore_line'));
    }
}
