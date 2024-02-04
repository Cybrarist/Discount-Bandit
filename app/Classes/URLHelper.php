<?php

namespace App\Classes;

use App\Classes\Stores\Amazon;
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
        $remove_ref_if_exists=explode("/ref" , $parsed_url['path'] ?? "");
        $this->path= $remove_ref_if_exists[0] ?? "";
        $this->final_url="https://$this->domain$this->path";

    }

    public function fill_data(& $data): void
    {
        if (MainStore::is_amazon($this->domain))
            $data=\Arr::add($data , 'asin' , $this->get_asin());
        elseif (MainStore::is_ebay($this->domain))
            $data=\Arr::add($data , 'ebay_id' , $this->get_ebay_item_id());
        elseif (MainStore::is_walmart($this->domain))
            $data=\Arr::add($data , 'walmart_ip' , $this->get_walmart_ip());
        elseif (MainStore::is_argos($this->domain))
            $data=\Arr::add($data , 'argos_id' , $this->get_argos_product_id());
        elseif ( MainStore::is_diy($this->domain))
            $data["key"]=$this->get_diy_id();
    }

    public function  get_asin(): string
    {
        $this->path=Str::replace( "/gp/product/" , "/dp/" , $this->path , false);
        return Str::remove("/" , Str::squish(explode("/dp/" , $this->path)[1]) );
    }
    public function  get_diy_id(): string {

        $this->path=Str::remove(["/departments/","_BQ.prd"] , $this->path);
        $is_two_parts=explode("/" , $this->path);

        if (sizeof($is_two_parts) > 2)
            throw new \Exception("wrong url");
        else
            return (sizeof($is_two_parts) > 1) ? $is_two_parts[1] : $is_two_parts[0];
    }
    public function  get_ebay_item_id(){
        return explode("/itm/" , $this->path)[1];
    }
    public function  get_walmart_ip(): string
    {
        return Str::remove("/" , Str::squish(  \Arr::last(explode("/" , $this->path))) );
    }
    public function  get_argos_product_id(): string
    {
        return Str::remove("/" , Str::squish(  \Arr::last(explode("/" , $this->path))) );
    }



}
