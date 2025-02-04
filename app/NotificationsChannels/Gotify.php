<?php

namespace App\NotificationsChannels;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class Gotify
{
    protected $httpClient;

    public function __construct()
    {
        $this->httpClient = new Http();
    }

    public function send(array $notification_title, string $notification_content)
    {
        return $this->request($notification_title, $notification_content);
    }

    protected function request(array $notification_title, string $notification_content)
    {
        $baseUrl = env('GOTIFY_BASE_URL', $this->baseUrl);
        $token = env('GOTIFY_TOKEN');
        
        $url = "{$baseUrl}/message?token={$token}";

        $data = [
            "title" => $notification_title['Title'],
            "message" => Str::replace("<br>", "\n", $this->notification_text)
        ];

        Http::asJson()
            ->post($url, $data);
    }
}
