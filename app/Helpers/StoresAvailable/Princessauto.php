<?php

namespace App\Helpers\StoresAvailable;

use Error;
use Exception;
use Illuminate\Support\Str;

class Princessauto extends StoreTemplate
{
    const string MAIN_URL="https://www.store/en/product/product_id" ;

    private $json_data;
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
            $this->json_data=json_decode($this->document->getElementById("CC-schema-org-server")->textContent);
        }catch (Error | exception ) {
            $this->log_error("Crawling Princess Auto");
        }

    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name = $this->json_data->name;
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->json_data->image;
        }
        catch ( Error | Exception ) {
            $this->log_error("Product Image First Method");
        }

    }

    public function get_price(): void
    {
        try {
            $this->price=  (float) $this->json_data->offers[0]->price;
            return ;
        }
        catch ( Error | \Exception  $e ) {
            $this->log_error("Price First Method");
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock = Str::contains($this->json_data->offers[0]->availability , "InStock" , true);
        }catch (\Exception $e){
            $this->log_error( "Stock Availability First Method");
        }
    }

    public function get_no_of_rates(): void {}

    public function get_rate(): void{}

    public function get_seller(): void{$this->seller="Princess Auto";}

    public function get_shipping_price(): void {}

    public function get_condition():void
    {
        try {
            $this->in_stock = Str::contains($this->json_data->offers[0]->itemCondition , "NewCondition" , true);
        }catch (\Exception $e){
            $this->log_error( "Stock Availability First Method");
        }
    }



    public static function get_variations($url) : array {return [];}


    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }
    function is_system_detected_as_robot(): bool { return false;}

}
