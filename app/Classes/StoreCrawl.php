<?php

namespace App\Classes;

use App\Enums\StatusEnum;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StoreCrawl
{

    private array $user_agents = [
        'w10_chrome_114' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36",
        'w10_edge_114' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.67",
        'w10_firefox_115' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0",
        'w10_opera_100' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 OPR/100.0.0.0"
    ];
    protected Product $product;
    protected Store $store;
    protected string $title;
    protected string $image;
    protected string $seller;
    protected string $rating;
    protected string $condition;
    protected int $no_of_rates;
    protected int $price;
    protected int $shipping_price;


    protected \DOMDocument $document;
    protected $xml;


    public function __construct($url_to_scan)
    {

        //Craw the link provided by the CrawlJob
        try {
            $response = Http::withUserAgent(\Arr::random($this->user_agents))
                ->get($url_to_scan);

            $this->document = new \DOMDocument();
            $internalErrors = libxml_use_internal_errors(true);
            $this->document->loadHTML($response);
            $this->xml = simplexml_import_dom($this->document);


        } catch (\Exception  $e) {
            \Log::error("Couldn't craw the following url: $url_to_scan\n
            with the following error to share: \n
            $e");
        }
    }


    protected function start_processing()
    {

        $this->get_title();
        $this->get_image();
        $this->get_price();
        $this->get_no_of_rates();
        $this->get_rate();
        $this->get_seller();
        $this->get_condition();
        $this->is_notifiable();
        $this->get_shipping_price();
        $this->update_product_details();
    }


    protected function get_title()
    {
    }
    protected function get_image()
    {
    }

    public function get_price()
    {
    }

    public function get_no_of_rates()
    {
    }

    protected function get_rate()
    {
    }

    protected function get_shipping_price(){}

    protected function get_seller()
    {
    }

    protected function update_product_details()
    {
        if (is_amazon($this->store->host))
            $this->product->updateOrCreate(
                ['id' => $this->product->id],
                [
                    'name' => $this->title,
                    'image' => $this->image,
                ]);
        elseif (!is_amazon($this->store->host) && $this->product->name==null)
        {
            $this->product->updateOrCreate(
                ['id' => $this->product->id],
                [
                    'name' => $this->title,
                    'image' => $this->image,
                ]);
        }
    }

    public function update_store_product_details(){}


    private function is_notifiable()
    {
//        //get the settings from the table.
//        $product_store=\DB::table('product_store')->where('id' , $this->product_store_id)->get();


//        if (
//            ($check_if_shipping_price_ignored->add_shipping &&
//                $this->price + $this->shipping_price <= $check_if_shipping_price_ignored->notify_price &&
//                $this->price != $check_if_shipping_price_ignored->price)
//            ||
//            ($this->price <= $check_if_shipping_price_ignored->notify_price&&
//                $this->price != $check_if_shipping_price_ignored->price)
//        ) {
////            TODO Implement notification
//        }

    }


    protected function throw_this_error($exception , $part )
    {
        Log::error("Couldn't Get the $part for product" . $this->product->title  ." for store " . $this->store->host .  " \n
            please share the following information with product and Store\n
            $exception");

    }

    public function get_condition(){}



    private function notify()
    {
        Http::post(env('APPRISE_SERVER_URL'), [
            "urls"=>env("APPRISE_NOTIFICATION_URL"),
            "title"=>"New Discount For Product A",
            "body"=>"New price is 100, get it from here https://hello.com",
        ]);
    }
}


