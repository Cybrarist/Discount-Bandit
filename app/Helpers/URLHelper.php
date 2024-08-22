<?php

namespace App\Helpers;

use App\Models\Currency;
use App\Models\Store;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

class URLHelper
{
    public string $final_url;
    public string $domain;
    public string $top_host;
    private string $path;
    public string $product_unique_key= "";

    public ?Store $store;

    public function __construct(private string $url) {
        Context::add("website" , $this->url);

        try {
            //parse the url
            $parsed_url=parse_url($url);

            $this->domain=Str::lower(Str::remove("www.",$parsed_url['host']));

            $this->store=Store::whereDomain($this->domain)->first();

            //check the store exists
            throw_if(!$this->store , new \Exception("This store doesn't exist in the database, please check the url"));

            $remove_ref_if_exists=explode("/ref" , $parsed_url['path'] ?? "");
            $this->path= $remove_ref_if_exists[0] ?? "";
            $this->final_url="https://$this->domain$this->path";

            $this->top_host= explode('.', $parsed_url["host"])[0];

            self::get_key();
        }
        catch (Exception $exception){


            Notification::make()
                ->danger()
                ->title("Wrong Store")
                ->body($exception->getMessage())
                ->persistent()
                ->send();

            return ;
        }

    }



    public  function get_key()
    {
        $this->product_unique_key= match (explode("." , $this->domain)[0]){
            "amazon"=> $this->get_asin(),
            "ebay"=> $this->get_ebay_item_id(),
            "walmart"=> $this->get_walmart_ip(),
            "argos"=> $this->get_argos_product_id(),
            "fnac"=> $this->get_fnac_key(),
            "noon"=> $this->get_noon_key(),
            "costco"=>$this->get_costco_key(),
            "currys"=>$this->get_currys_key(),
            "diy"=>$this->get_diy_id(),
            "canadiantire"=>$this->get_canadiantire_key(),
            "princessauto"=>$this->get_pricessauto_key(),
            "mediamarkt"=>$this->get_mediamarket_key(),
            default=> ""
        };

    }


    public static function get_product_original_url()
    {

    }


    //todo move all of the following to the classes as static method
    //methods to collect unique products keys
    public function  get_asin(): string
    {
        $this->path=Str::replace( "/gp/product/" , "/dp/" , $this->path , false);

        $dp_temp= explode("/dp/" , $this->path);

        if (sizeof($dp_temp)<=1)
            return "";

        $temp=$dp_temp[1];

        $check_slashes_after_dp=explode("/" , $temp);
        if ($check_slashes_after_dp)
            $temp=$check_slashes_after_dp[0];

        return Str::remove("/" ,Str::squish( $temp) );
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
//        return Str::remove("/" , Str::squish(  Arr::last(explode("/" , $this->path))) );
        return explode('/ip/' , $this->path)[1];
    }
    public function  get_argos_product_id(): string
    {
        return Str::remove("/" , Str::squish(  Arr::last(explode("/" , $this->path))) );
    }

    public function get_fnac_key(): string
    {
        $paths=explode("/" , $this->path);

        if (sizeof($paths) > 2 && $paths[2] !="")
            return Str::lower($paths[2]) ;

        throw new \Exception("wrong formula");
    }

    public function get_noon_key(): string
    {

        $paths=explode("/" , $this->path);

        //update the store for noon, since they use path instead of custom domain per country
        $this->store=match (Str::lower(explode("-" , $paths[1])[0])) {
            "uae" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code" , "AED")->first()->id)->first(),
            "egypt" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code" , "EGP")->first()->id)->first(),
            "saudi" => Store::whereDomain($this->domain)->whereCurrencyId(Currency::where("code" , "SAR")->first()->id)->first(),
        };

        return Str::lower($paths[sizeof($paths)-3]);


        throw new \Exception("wrong formula");
    }

    public function get_costco_key(): string
    {
        return match ($this->domain)
        {
            "costco.com","costco.ca"=>Str::replace(["." , "html"] , "" , explode(".product" , $this->path)[1]),
            "costco.com.mx","costco.co.uk","costco.co.kr","costco.com.tw","costco.co.jp","costco.com.au","costco.is"=>explode("/p/" , $this->path)[1],
        };
    }

    public function get_currys_key(): string
    {

        $paths= explode("/" , $this->path);
        $sections=explode("-" , $paths[2]);

        return Str::remove(".html" ,Arr::last($sections));
    }
    public function get_canadiantire_key(): string
    {
        $paths= explode("/pdp" , $this->path);
        $sections=explode("-" , $paths[1]);

        return Str::remove(".html" ,Arr::last($sections));
    }

    public function get_pricessauto_key():string
    {
        $temp=explode("/product/" , $this->path)[1];
        return Str::remove("/" ,Str::squish( $temp) );
    }

    public function get_mediamarket_key()
    {
        $temp=explode("-" , $this->path);
        $product_key=explode(".html" ,  end($temp))[0];

        return $product_key;
    }
}
