<?php

namespace App\Helpers\StoresAvailable;

use Error;
use Exception;
use Illuminate\Support\Str;

class Flipkart extends StoreTemplate
{
    const string MAIN_URL="https://www.store/random/p/itmproduct_id" ;

    private  $main_schema;
    private  $metas;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void
    {
        parent::crawl_url();
    }

    public function prepare_sections_to_crawl(): void
    {
        try {

            $temp_schemas=json_decode( $this->document->getElementById("jsonLD")->textContent, true);
            foreach ($temp_schemas as $temp_schema)
                if ($temp_schema['@type']=="Product")
                    $this->main_schema=$temp_schema;

            $this->metas = $this->xml->xpath("//meta");
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
            $this->name= Str::before( $this->document->getElementsByTagName("title")->item(0)->textContent , 'Price');
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name First Method", $exception->getMessage());
        }
        try {
            $this->name=$this->main_schema["name"];
            return;
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }


    }

    public function get_image(): void
    {
        try {
            $this->image = $this->main_schema["image"];
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

        try {
            $this->image = $this->xml->xpath("//meta[@property='og:image']")[0]->attributes()['content']->__toString();
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {
            $this->price=  (float) $this->main_schema['offers']['price'];
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }
    }

    //didn't see product with used price
    public function get_used_price(): void {}

    //not supported
    public function get_stock(): void {}

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates= (int) $this->main_schema['aggregateRating']['reviewCount'];
        }
        catch (Error | Exception $exception)
        {
            $this->log_error("No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            $this->rating= (float) $this->main_schema['aggregateRating']['ratingValue'];
        }
        catch (Error | Exception $exception )
        {
            $this->log_error("The Rate", $exception->getMessage());
        }

    }

    public function get_seller(): void { $this->seller="flipkart";}

    public function get_shipping_price(): void {}

    public function get_condition() {}

    //todo needs to be checked with id=is_script
    public static function get_variations($url) : array { return  [];}


    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id", "random"],
            [$domain , $product, Str::random()],
            self::MAIN_URL);
    }


    function is_system_detected_as_robot(): bool {return false;}

}
