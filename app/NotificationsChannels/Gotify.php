<?php

namespace App\NotificationsChannels;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gotify
{
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Http;
    }

    public function send(array $notification, string $notification_content, User $user)
    {
        $url = "{$user->notification_settings['gotify_url']}/message?token={$user->notification_settings['gotify_token']}";

        $data = [
            "title" => $notification['title'],
            "message" => Str::replace("<br>", "\n", $notification_content),
        ];

        Http::asJson()->post($url, $data);
    }
}
