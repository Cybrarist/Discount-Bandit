<?php

namespace App\NotificationsChannels;
use Illuminate\Notifications\Notification;

class AppriseChannel
{

    protected  $apprise;

    public function __construct(Apprise $apprise)
    {
        $this->apprise = $apprise;
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification)
    {
        $message = $notification->toApprise($notifiable);

       return $this->apprise->send(notification_content: $message["content"]);
    }
}
