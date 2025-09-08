<?php

namespace App\Helpers;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class UserAgentHelper
{
    const USER_AGENTS = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.3",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.3",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:130.0) Gecko/20100101 Firefox/130.",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.0.0 Safari/537.36",
        "Mozilla/5.0 (X11; Linux i686; rv:130.0) Gecko/20100101 Firefox/130.0",
        //        "argos" => "Mozilla/5.0 (Windows; U; Windows NT 6.1; ko-KR) AppleWebKit/533.20.25  Version/5.0.4 Safari/533.20.27",
        //        "noon" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0",
        //        "other" => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:137.0) Gecko/20100101 Firefox/137.0",
    ];

    public static function get_random_user_agent()
    {

        return Arr::random(self::USER_AGENTS);

        //        return match (true) {
        //            Str::contains($url, ["costco.", "currys.c"], true) => self::USER_AGENTS["other"] ,
        //            Str::contains($url, "noon.com", true) => self::USER_AGENTS["noon"],
        //            Str::contains($url, "argos.co.uk", true) => Arr::random(self::USER_AGENTS["argos"]),
        //            Str::contains($url, "walmart", true) => Str::random(),
        //            Str::contains($url, "homedepot", true) => "",
        //
        //            default => Arr::random(self::USER_AGENTS)
        //        };
    }

}
