<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use Error;
use Exception;
use Illuminate\Support\Str;

class Nykaa extends StoreTemplate
{
    const string MAIN_URL="https://www.store/random/p/product_id" ;

    const string OTHER_BUYING_OPTIONS="https://www.store/gp/product/ajax?asin=product_id&m=&sourcecustomerorglistid=&sourcecustomerorglistitemid=&pc=dp&experienceId=aodAjaxMain";

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

            $this->meta_items= $this->get_meta_items();

            $this->product_schema=json_decode(Str::of($this->document->textContent)
                ->after('"dataLayer"')
                ->before('"productPage"')
                ->prepend('{"dataLayer"')
                ->append("}")
                ->replaceLast("," , "")
                ->toString(),true)["dataLayer"]["product"];

        }catch (Error | Exception $exception) {
            $this->log_error("Crawling", $exception->getMessage());
        }

    }
    /**
     * Get the data from the store
     */
    public function get_name(): void
    {

        try {
            $this->name = trim($this->product_schema["name"]);
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name First Method", $exception->getMessage());
        }
        try {
            $this->name = trim( Str::remove(['Buy','Online'] ,$this->document->getElementsByTagName("title")->item(0)->textContent));
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }
        try {
            $this->name= GeneralHelper::get_value_from_meta_tag(meta_items: $this->meta_items , key: "og:title" , attribute: "content");
            $this->name= Str::of($this->name)->remove(['Buy','Online'])->trim()->toString();
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Name Third Method", $exception->getMessage());
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
        try {
            $this->image = GeneralHelper::get_value_from_meta_tag(meta_items: $this->meta_items , key: "og:image" , attribute: "content");
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {
            $this->price=  (float) $this->product_schema['offerPrice'];
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }

        try {
            $this->price=  (float) GeneralHelper::get_value_from_meta_tag(meta_items: $this->meta_items, key: 'product:price:amount' , attribute: 'content');
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price Second Method",$exception->getMessage());
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock = $this->product_schema['inStock'];
            return;
        }catch (Exception $exception){
            $this->log_error( "Stock Availability First Method",$exception->getMessage());
        }
        try {
            $this->in_stock = $this->product_schema['stockStatus'] == "instock";

        }catch (Exception $exception){
            $this->log_error( "Stock Availability Second Method",$exception->getMessage());
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates= (int) $this->product_schema['ratingCount'];
        }
        catch (Error | Exception $exception)
        {
            $this->log_error("No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            //check if the store is amazon poland or not
            $this->rating= $this->product_schema['rating'];
        }
        catch (Error | Exception $exception )
        {
            $this->log_error("The Rate", $exception->getMessage());
        }

    }

    public function get_seller(): void { $this->seller="Nykaa";}

    public function get_shipping_price(): void {}

    public function get_condition() {}

    //toDO
    public static function get_variations($url) : array
    {
        return  [];
    }


    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id", "random"],
            [$domain , $product, Str::random()],
            self::MAIN_URL);
    }

    //todo didn't encounter it yet
    function is_system_detected_as_robot(): bool { return false;}
}
