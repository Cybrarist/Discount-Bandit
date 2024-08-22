<?php

namespace App\Helpers\StoresAvailable;

use App\Models\Store;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class Canadiantire extends StoreTemplate
{
    const MAIN_URL="https://store/en/pdp/product_id.html" ;
    const API_URL="https://apim.canadiantire.ca/v1/product/api/v1/product/productFamily/";
    private $schema_script;


    public function __construct( int $product_store_id) {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void {
        parent::crawl_url();
    }

    public function prepare_sections_to_crawl(): void {
        try {

            $get_subscription_key=json_decode($this->xml->xpath("//body")[0]->attributes()["data-configs"]->__toString())->{"apim-subscriptionkey"};

            //request to get the new data
            $response=parent::get_website_chrome(
                self::API_URL .
                $this->current_record->key .
                "?baseStoreId=CTR&lang=en_CA&storeId=144&light=true"
                ,
                extra_headers: [
                    "ocp-apim-subscription-key"=>$get_subscription_key,
                    'basesiteid' => 'CTR',
                    'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:129.0) Gecko/20100101 Firefox/129.0',
                ]
            );

            $temp=explode("<pre>"  , $response)[1] ;

            $this->schema_script=json_decode(explode("</pre>"  , $temp)[0], true );

        }catch (Exception $e){
            $this->log_error("Prepareing the crawl", $e->getMessage());
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name =$this->schema_script["name"];
        } catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->schema_script["images"][0]['url'];
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image First Method");
        }

    }

    public function get_price(): void
    {
        try {
            $this->price=(float) $this->schema_script["currentPrice"]["value"];
            return ;
        } catch ( Error | \Exception  $e )
        {
            $this->log_error("Product Price First Method");
        }
        $this->price=0;
    }

    public function get_used_price(){$this->price_used=0;}

    public function get_stock(): void {
        try {
            $this->in_stock= ($this->schema_script["fulfillment"]["availability"]["quantity"] > 0);
            return;
        }catch (\Exception $e){
            $this->log_error("the stock", $e->getMessage());
        }
        $this->in_stock=true;
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates= $this->schema_script["ratingsCount"];
            return;
        }catch (\Exception $e){
            $this->log_error("the Number of rates");
        }
        $this->no_of_rates=0;
    }

    public function get_rate(){

        try {
            $this->rating=$this->schema_script["rating"];
            return;
        }catch (\Exception $e){
            $this->log_error("the Rate");
        }
        $this->rating= -1;
    }

    public function get_seller(): void {$this->seller="Canadian tire";}

    public function get_shipping_price(): void { $this->shipping_price=0;}


    public static function get_variations($url) : array
    {
            Notification::make()
                ->danger()
                ->title("This Store doesn't support variation yet")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();
        return  [];
    }



    public function get_condition(): void{}

    public static function prepare_url($domain, $product, ?Store $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }
}
