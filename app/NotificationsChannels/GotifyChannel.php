<?php

namespace App\NotificationsChannels;

use Illuminate\Notifications\Notification;

class GotifyChannel
{
    protected $gotify;

    public function __construct(Gotify $gotify)
    {
        $this->gotify = $gotify;
    }

    /**
     * Send the given notification.
     */
    public function send(object $notifiable, Notification $notification)
    {
        $message = $notification->toGotify($notifiable);

        return $this->gotify->send(notification_title: $message["headers"], notification_content: $message["content"]);
    }
}
