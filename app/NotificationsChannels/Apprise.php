<?php

namespace App\NotificationsChannels;


use Illuminate\Support\Facades\Http;


class Apprise
{
    protected $http_client;

    public function __construct()
    {
        $this->http_client = new Http();
    }


    public function send(array $notification_content )
    {
        return $this->request($notification_content);
    }


    protected function request( array $notification_content)
    {
        $url=env('APPRISE_URL');

        if (!$url)
            return null;

        $response = Http::withHeaders([
                "Content-Type"=>"application/json",
                "Cache: no",
            ])
            ->post($url, $notification_content);

        $body = json_decode($response->getBody(), true);

        return $body;
    }

}
