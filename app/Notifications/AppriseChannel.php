<?php
namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AppriseChannel
{
    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $message = $notification->toApprise($notifiable);

    }
}
