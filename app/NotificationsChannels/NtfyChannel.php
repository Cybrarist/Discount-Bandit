<?php

namespace App\NotificationsChannels;

use Illuminate\Notifications\Notification;

class NtfyChannel
{
    protected $ntfy;

    public function __construct(Ntfy $ntfy)
    {
        $this->ntfy = $ntfy;
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification)
    {
        $message = $notification->toNtfy($notifiable);

        return $this->ntfy->send(notification: $message["headers"], notification_content: $message["content"], user: $notifiable);
    }
}
