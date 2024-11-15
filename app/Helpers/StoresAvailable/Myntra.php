<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use App\Models\Currency;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Myntra extends StoreTemplate
{
    const string MAIN_URL="https://www.store/product_id" ;

    private  $center_column;
    private  $product_schema;



    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void
    {
        parent::crawl_url_chrome();
    }

    public function prepare_sections_to_crawl(): void
    {
        try {
            $this->product_schema=$this->get_product_schema('application/ld+json');
        }catch (Error | Exception $exception) {
            $this->log_error("Crawling Amazon", $exception->getMessage());
        }

    }
    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name = $this->product_schema['name'];
            return;
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Name First Method", $exception->getMessage());
        }

        try {
            $this->name = Str::remove(["Buy"," "] , explode('-' , $this->document->getElementsByTagName("title")->item(0)->textContent)[0]);
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }


    }

    public function get_image(): void
    {
        try {
            $this->image =  $this->product_schema['image'];
            return;
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }
        try {
            $this->image =  $this->xml->xpath('//meta[@itemprop="image"]')[0]->attributes()['content'];
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image Second Method", $exception->getMessage());
        }
    }

    public function get_price(): void
    {
        try {
            $this->price=  (float) $this->product_schema['offers']['price'];
            return;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }
        try {
            $this->price=  (float) GeneralHelper::get_numbers_only_with_dot($this->xml->xpath('//span[@class="pdp-price"]//strong')[0]->__toString());
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock= $this->product_schema['offers']['availability']=="InStock";
        }catch (Exception $exception){
            $this->log_error( "Stock Availability First Method",$exception->getMessage());
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates= (int) GeneralHelper::get_numbers_only_with_dot($this->xml->xpath('//div[@class="index-ratingsCount"]')[0]->__toString());
        }
        catch (Error | Exception $exception)
        {
            $this->log_error("No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            $this->rating= $this->xml->xpath('//div[@class="index-overallRating"]//div')[0]->__toString();
        }
        catch (Error | Exception $exception )
        {
            $this->log_error("The Rate", $exception->getMessage());
        }

    }

    public function get_seller(): void
    {
        try {

            $this->seller= Str::of($this->document->textContent)->after(',"sellers"')->before(',"displayName"')->after('"sellerName":"')->remove('"')->toString();

        }
        catch (Error | Exception $exception )
        {
            $this->log_error( "The Seller Third Method" ,   $exception->getMessage());
            $this->seller="";
        }
    }

    public function get_shipping_price(): void {}

    public function get_condition() {}

    public static function get_variations($url) : array { return [];}


    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }

    function is_system_detected_as_robot(): bool {return false;}

}
