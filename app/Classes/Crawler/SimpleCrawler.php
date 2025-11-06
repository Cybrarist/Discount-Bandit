<?php

namespace App\Classes\Crawler;

use Dom\HTMLDocument;
use Illuminate\Support\Facades\Http;

class SimpleCrawler
{
    public string $url;

    public array $headers = [
        'Accept' => '*/*',
        'DNT' => 1,
        'Sec-Fetch-User' => 1,
        'Connection' => 'closed',
        "Accept-Encoding" => "gzip, deflate",
    ];

    public array $timeout;


    public HTMLDocument $dom;

    public function __construct(string $url, array $extra_headers = [], $user_agent = "", int $timeout = 10, array $settings = [])
    {
        $response = Http::withUserAgent($user_agent)
            ->timeout($timeout)
            ->withHeaders(
                $extra_headers + $this->headers
            )->{array_key_exists("method", $settings) ? $settings['method'] : 'get'}($url, $settings);



        $this->dom = HTMLDocument::createFromString($response->body(), LIBXML_NOERROR);

    }
}
