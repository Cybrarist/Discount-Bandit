<?php

namespace App\Classes;

use App\Classes\Stores\Amazon;
use App\Classes\Stores\Argos;
use App\Classes\Stores\Ebay;
use App\Classes\Stores\Walmart;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use App\Models\User;
use App\Notifications\ProductDiscount;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class MainStore
{

    const  user_agents = [
        'w10_chrome_114' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36",
        'w10_edge_114' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 Edg/114.0.1823.67",
        'w10_firefox_115' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0",
        'w10_opera_100' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36 OPR/100.0.0.0",
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_2) AppleWebKit/601.3.9 (KHTML, like Gecko) Version/9.0.2 Safari/601.3.9',
        'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/118.0'
//        TODO Implement Mobile Crawling
//        'Mozilla/5.0 (Linux; Android 13; SM-S901B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
//        'Mozilla/5.0 (Linux; Android 13; SM-S901U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
//        'Mozilla/5.0 (Linux; Android 13; SM-S908U) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/111.0.0.0 Mobile Safari/537.36',
//        'Mozilla/5.0 (Linux; Android 13; SM-G998B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
//        'Mozilla/5.0 (Linux; Android 13; SM-A515F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
//        'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
//        'Mozilla/5.0 (Linux; Android 13; Pixel 7 Pro) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
//        'Mozilla/5.0 (Linux; Android 12; moto g stylus 5G) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36v ',
//        'Mozilla/5.0 (Linux; Android 10; VOG-L29) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36',
//        ' Mozilla/5.0 (iPhone12,1; U; CPU iPhone OS 13_0 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Mobile/15E148 Safari/602.1',
//        ' Mozilla/5.0 (iPhone12,1; U; CPU iPhone OS 13_0 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Mobile/15E148 Safari/602.1',
//        'Mozilla/5.0 (Linux; Android 11; Lenovo YT-J706X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36',
//        'Mozilla/5.0 (Linux; Android 7.0; SM-T827R4 Build/NRD90M) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.116 Safari/537.36',

    ];

    const   argos_agents=[
        "Mozilla/5.0 (Windows; U; Windows NT 6.1; ko-KR) AppleWebKit/533.20.25  Version/5.0.4 Safari/533.20.27"

    ];
    public string $product_url;
    public string $name;
    public string $image;
    public string $price;
    public string $seller;
    public string $rating;
    public int $no_of_rates;
    public int $shipping_price;
    public bool $in_stock;
    public string $condition;



    //shared across all the classes inherted
    public ?\DOMDocument $document = null;
    public $xml;
    public $total_record;

    abstract public function crawling_process();

    /**
     * Helper Function for crawling
     */
    abstract public static function prepare_url($domain , $store, $ref);

    public static function get_numbers_only_with_dots($string)
    {
        return preg_replace('/[^0-9.]/', '', $string);
    }

    /**
     *  abstract functions for crawler  functions to get the data from the url
     */
    abstract public function get_name();
    abstract public function get_image();
    abstract public function get_price();
    abstract public function get_stock();
    abstract public function get_no_of_rates();
    abstract public function get_rate();
    abstract public function get_seller();
    abstract public function get_shipping_price();




    /**
     *  functions for crawler for notification decision
     */
    abstract public function check_notification();
    public function stock_available(){
        //check if the stock option is enabled, also the previous crawl was out of stock  and the current is in stock
        return $this->total_record->stock && !$this->total_record->in_stock && $this->in_stock;
    }
    public function price_crawled_and_different_from_database(){
        //check that we have the crawled price, and that is different from the database.
        return  $this->price &&
            $this->price != -1 &&
            $this->price != $this->total_record->price &&
            $this->total_record->price;
    }
    abstract public function prepare_sections_to_crawl();

    abstract public function get_condition();


    //static functions that can be accessed anywhere
    public static function get_website($url){

        $random_user_agent=Arr::random(self::user_agents);
        if (Str::contains( $url , "argos.co.uk"  , true))
            $random_user_agent=Arr::random(self::argos_agents);
        return Http::withUserAgent($random_user_agent)
            ->withHeaders([
                'Accept'=> 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'DNT'=>1,
                'Sec-Fetch-User'=>'1',
                'Connection'=>'keep-alive'
            ])
            ->get($url);
    }
    public static function prepare_dom( $response ,& $document , &$xml){
        $document=new \DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML($response);
//        $document->getElementById()
        $xml = simplexml_import_dom($document);
    }
    public static function is_price_lowest_within(int $product_id=null,int $store_id=null, int $days=null , int $price=0)
    {
        //if the current price is lower or equal to prices from x previous days, notify immediately
        $lowest_price_in_database=\DB::table('price_histories')
            ->where('date' , '>=' , Carbon::today()->subDay($days)->toDateString())
            ->where('product_id', '=',  $product_id)
            ->where('store_id', '=',  $store_id)
            ->min('price');

        return ($lowest_price_in_database > $price && $lowest_price_in_database!=0);
    }

    public static function record_price_history (int $product_id , int $store_id ,int $price=0)
    {
        if ($price <=0)
            return;

        try {
            $history=PriceHistory::firstOrCreate([
                'product_id' =>  $product_id,
                'store_id' =>$store_id,
                'date'=>\Carbon\Carbon::today()->toDateString(),
            ],
                [
                    'price'=> $price
                ]
            );

            if ($history->price > $price)
                $history->update([
                    'price'=>$price
                ]);

        }
        catch (\Exception $e){
            Log::error("Couldn't update the price history");
        }
    }


    //shared functions across the stores
    public function crawl_url() {
        $response=self::get_website($this->product_url);
        self::prepare_dom($response,$this->document , $this->xml);
    }
    protected function get_record($product_store_id){
        $this->total_record=\DB::table('product_store')
            ->where('product_store.id' , "=" , $product_store_id)
            ->join('stores', 'stores.id' , '=' , 'product_store.store_id')
            ->join('products' , 'products.id' , '=' , 'product_store.product_id')
            ->select([
                'product_store.*',
                'product_store.id as product_store_id',
                'products.*',
                'products.name as product_name',
                'products.image as product_image',
                'stores.*',
                'stores.name as store_name',
                'stores.image as store_image',
            ])
            ->first();
    }

    public function update_store_product_details( $product_store_id , $data){
        ProductStore::where('id', $product_store_id)->update($data);
    }

    public function update_product_details($product_id, $data){

        Product::find($product_id)->update($data);
    }
    public function notify(){

        try {
            $users=User::first();
            foreach ($users as $user)
                $user->notify(
                    new ProductDiscount(
                        product_name: $this->total_record->product_name ?? $this->name ,
                        store_name: $this->total_record->store_name,
                        price: $this->price / 100,
                        product_url: $this->product_url . $this->total_record->referral,
                        image: $this->total_record->product_image ?? $this->image,
                        currency: get_currencies($this->total_record->currency_id)));
        }
        catch (\Exception $e)
        {
            $this->throw_error("Send Notification");
        }

    }


    //Validation
    public function price_reached_desired(): bool {
        if (!$this->price || $this->price <0)
            return false;
        //if the price is less than the notify price and consider the shipping option
        if ($this->total_record->add_shipping)
            return $this->shipping_price + $this->price  <= $this->total_record->notify_price;
        else
            return $this->price  <= $this->total_record->notify_price;

    }

    public function max_notification_reached(): bool{
        //if max notifications has been sent, no need to continue.

        return  $this->total_record->max_notifications &&
                $this->total_record->notifications_sent > $this->total_record->max_notifications;
    }
    public function notification_snoozed(): bool{
        return $this->total_record->snoozed_until &&  !Carbon::create($this->total_record->snoozed_until)->isPast();
    }



    public function  throw_error($part): void
    {
        Log::error("Couldn't get the $part for the following url : $this->product_url");
    }

    public static function  is_amazon($string)
    {
        return \Str::contains( $string,"amazon" ,true);
    }

    public static function  is_ebay($string)
    {
        return \Str::contains( $string,"ebay" ,true);
    }

    public static function  is_walmart($string)
    {
        return \Str::contains( $string,"walmart" ,true);
    }
    public static function  is_argos($string)
    {
        return \Str::contains( $string,"argos" ,true);
    }

    public static function validate_url(URLHelper $url)
    {
        if (self::is_amazon($url->domain) )
            return  Amazon::validate($url);
        elseif (self::is_ebay($url->domain))
            return Ebay::validate($url);
        elseif (self::is_walmart($url->domain))
            return Walmart::validate($url);
        elseif (self::is_argos($url->domain))
            return Argos::validate($url);

        else
            Notification::make()
                ->danger()
                ->title("Wrong Store")
                ->body("This store doesn't exist in the database, please check the url")
                ->persistent()
                ->send();

    }


    public static function insert_other_store($domain, $product_id, $extra_keys=[], $extra_data=[])
    {

        try {
            $store_id=Store::where('domain', $domain)->first()->id;
            ProductStore::updateOrCreate(
                array_merge([
                    "product_id"=>$product_id,
                    "store_id"=>$store_id
                ],$extra_keys),
               $extra_data
            );
        }
        catch (\Exception $e){
            Notification::make()
                ->danger()
                ->title("Oops, Couldn't link that store")
                ->body("This store doesn't exist in the database, please check the url")
                ->persistent()
                ->send();
        self::throw_error("linking product with error \n $e");
        }


    }


    public static function create_product(URLHelper $url, $group_id=null)
    {
        if (self::is_amazon($url->domain)){
            $product=Product::updateOrCreate([
                "asin"=>$url->get_asin()
            ]);
            $product_store=ProductStore::updateOrCreate([
                "product_id"=>$product->id,
                "store_id"=>Store::where("domain" , $url->domain)->first()->id
            ]);
        } elseif ( self::is_ebay($url->domain)){
            //check if the ebay id exists
            $product_store=\DB::table("product_store")
                                ->where("store_id", "=" , 23)
                                ->where("ebay_id", $url->get_ebay_item_id())
                                ->first();
            if (!$product_store){
                $product=Product::create();
                $product_store=ProductStore::updateOrCreate([
                    "product_id"=>$product->id,
                    "store_id"=>23
                ],[
                    "ebay_id"=>$url->get_ebay_item_id()
                ]);
            }
        } elseif ( self::is_walmart($url->domain)){
            $product=Product::updateOrCreate([
                "walmart_ip"=>$url->get_walmart_ip()
            ]);
            $product_store=ProductStore::updateOrCreate([
                "product_id"=>$product->id,
                "store_id"=>Store::where("domain" , $url->domain)->first()->id
            ]);
        } elseif ( self::is_argos($url->domain)){
            $product=Product::updateOrCreate([
                "argos_id"=>$url->get_argos_product_id()
            ]);
            $product_store=ProductStore::updateOrCreate([
                "product_id"=>$product->id,
                "store_id"=>Store::where("domain" , $url->domain)->first()->id
            ]);
        }
        return $product_store["product_id"];
    }

}


