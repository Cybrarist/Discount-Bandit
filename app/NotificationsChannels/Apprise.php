<?php

namespace App\NotificationsChannels;

use Illuminate\Support\Facades\Http;

class Apprise
{
    protected $http_client;

    public function __construct()
    {
        $this->http_client = new Http;
    }

    public function send(array $notification_content)
    {
        return $this->request($notification_content);
    }

    protected function request(array $notification_content)
    {
        if (! config('settings.apprise_url')) {
            return null;
        }

        $response = Http::withHeaders([
            "Content-Type" => "application/json",
            "Cache: no",
        ])
            ->post(config('settings.apprise_url'), $notification_content);

        return json_decode($response->getBody(), true);
    }
}
