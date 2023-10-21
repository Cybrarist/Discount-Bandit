<?php

namespace App\Classes;

use Filament\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class URLHelper
{
    public string $final_url;
    public string $domain;
    public string $path;

    public function __construct($url) {
        $parsed_url=parse_url($url);

        $this->domain=\Str::lower(\Str::replace("www.", "" ,  $parsed_url['host']));
        $this->path=$parsed_url['path'] ?? "";
        $this->final_url="https://$this->domain$this->path";
    }

    public function fill_data(& $data): void
    {
        if (MainStore::is_amazon($this->domain))
            $data=\Arr::add($data , 'asin' , $this->get_asin());
        elseif (MainStore::is_ebay($this->domain))
            $data=\Arr::add($data , 'ebay_id' , $this->get_ebay_item_id());
    }
    public function  get_asin(): string
    {
        $this->path=\Str::replace("/gp/product/" , "/dp/" , $this->path , false);
        return Str::remove("/" , Str::squish(explode("/dp/" , $this->path)[1]) );
    }

    public function  get_ebay_item_id(){
        return explode("/itm/" , $this->path)[1];
    }
}
