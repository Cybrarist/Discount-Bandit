<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class NtfyChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toNtfy($notifiable);

    }
}
