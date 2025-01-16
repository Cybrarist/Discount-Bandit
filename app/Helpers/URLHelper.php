<?php

namespace App\Helpers;

use App\Models\Currency;
use App\Models\Store;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

class URLHelper
{
    public string $final_url;
    public string $domain;
    public string $top_host;
    private string $path;
    public string $product_unique_key= "";

    public ?Store $store;

    public function __construct(private string $url) {
        try {
            //parse the url
            $parsed_url=parse_url($url);

            $this->domain= Str::of($parsed_url['host'])->lower()->remove(['www.' , 'uae.']);


//            Str::lower(Str::remove("www.",$parsed_url['host']));

//            if ($this->domain==="uae.emaxme.com")
//                $this->domain=Str::remove("uae.",$parsed_url['host']);

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



    public function get_key()
    {
        $function_to_be_called= "self::get_" .  explode("." , $this->domain)[0] ."_key";
        $this->product_unique_key=call_user_func($function_to_be_called);
    }

    //todo move all of the following to the classes as static method
    //methods to collect unique products keys
    public function  get_argos_key(): string
    {
        return Str::remove("/" , Str::squish(  Arr::last(explode("/" , $this->path))) );
    }

    public function get_ajio_key(): string {

        $paths=explode("/p/" , $this->path);
        return (sizeof($paths) ==1 ) ?  $paths[0] : $paths[1];
    }

    public function  get_amazon_key(): string
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

    public function get_bestbuy_key():string {
        $temp=explode("/" , $this->path);

        //todo if same after finihsing implementing ca then remove.
        return match ($this->domain){
            'bestbuy.com'=>explode("." ,  Arr::last($temp))[0],
            'bestbuy.ca'=>Arr::last($temp),
        };
    }

    public function get_canadiantire_key(): string {
        $paths= explode("/pdp" , $this->path);
        $sections=explode("-" , $paths[1]);

        return Str::remove(".html" ,Arr::last($sections));
    }

    public function get_costco_key(): string {
        return match ($this->domain)
        {
            "costco.com","costco.ca"=>Str::replace(["." , "html"] , "" , explode(".product" , $this->path)[1]),
            "costco.com.mx","costco.co.uk","costco.co.kr","costco.com.tw","costco.co.jp","costco.com.au","costco.is"=>explode("/p/" , $this->path)[1],
        };
    }

    public function get_currys_key(): string {

        $paths= explode("/" , $this->path);
        $sections=explode("-" , $paths[2]);

        return Str::remove(".html" ,Arr::last($sections));
    }

    public function  get_diy_id(): string {

        $this->path=Str::remove(["/departments/","_BQ.prd"] , $this->path);
        $is_two_parts=explode("/" , $this->path);

        if (sizeof($is_two_parts) > 2)
            throw new \Exception("wrong url");
        else
            return (sizeof($is_two_parts) > 1) ? $is_two_parts[1] : $is_two_parts[0];
    }

    public function  get_ebay_key(){
        return explode("/itm/" , $this->path)[1];
    }

    public function get_emaxme_key(): string {
        return Str::remove(["/",'.html'], $this->path);
    }

    public function get_flipkart_key(): string
    {
        return Str::after($this->path, 'itm');
    }

    public function get_mediamarkt_key(): string {
        $temp=explode("-" , $this->path);
        $product_key=explode(".html" ,  end($temp))[0];

        return $product_key;
    }

    public function get_myntra_key(): string {
        $temp=explode("/" , $this->path);

        $product_key=$temp[sizeof($temp)-2];

        return $product_key;
    }

    public function get_noon_key(): string {

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


    public function get_nykaa_key(): string {

        $paths=explode("/p/" , $this->path);
        return (sizeof($paths) ==1 ) ?  $paths[0] : $paths[1];
    }




    public function get_princessauto_key():string {
        $temp=explode("/product/" , $this->path)[1];
        return Str::remove("/" ,Str::squish( $temp) );
    }

    public function get_snapdeal_key():string {
        $temp=explode("/" , $this->path);
        return Arr::last($temp);
    }

    public function get_target_key()
    {
        if (Str::contains( $this->url,"preselect", true))
            return "A-" . explode("#",explode("preselect=", $this->url)[1])[0];

        $paths= explode("/-/" , $this->path);
        return $paths[1];
    }
    public function get_tatacliq_key()
    {

        $paths= explode("/p-mp" , $this->path);
        return $paths[1];
    }

    public function  get_walmart_key(): string {
//        return Str::remove("/" , Str::squish(  Arr::last(explode("/" , $this->path))) );
        return explode('/ip/' , $this->path)[1];
    }
}
