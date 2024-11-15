<?php

namespace App\Helpers\StoresAvailable;

use Error;
use Exception;
use Illuminate\Support\Str;

class Ajio extends StoreTemplate
{
    const string MAIN_URL="https://www.store/p/product_id" ;

    private $meta_items;
    private  $product_schema;

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
            $this->product_schema=$this->get_product_schema(script_type: 'application/ld+json');
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
        catch (Error | Exception $exception){
            $this->log_error("Product Name First Method", $exception->getMessage());
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->product_schema["image"];
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }
    }

    public function get_price(): void
    {
        try {
            $this->price=  (float) $this->product_schema['offers']['price'];
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }
    }

    public function get_used_price(): void {}

    public function get_stock(): void {}

    public function get_no_of_rates(): void {}

    public function get_rate(): void{}

    public function get_seller(): void { $this->seller="Ajio";}

    public function get_shipping_price(): void {}

    public function get_condition() {}

    //toDO
    public static function get_variations($url) : array { return  []; }


    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }

    //todo didn't encounter it yet
    function is_system_detected_as_robot(): bool { return false;}
}
