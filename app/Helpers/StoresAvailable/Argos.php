<?php

namespace App\Helpers\StoresAvailable;

use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Argos extends StoreTemplate
{
    const string MAIN_URL="https://store/product/product_id" ;
    private  $core_product;
    private $right_column;
    private $accordions;

    private $json_data;



    public function __construct(private int $product_store_id) {
        parent::__construct($product_store_id);
    }

    public function prepare_sections_to_crawl(): void {

        //get the center column to get the related data for it
        $this->core_product=$this->xml->xpath("//section[contains(@class , 'pdp-core')]")[0];
        //get the right column to get the seller and other data
        $this->right_column=$this->xml->xpath("//section[contains(@class , 'pdp-right')]")[0];
        $this->accordions=$this->xml->xpath("//section[contains(@class , 'pdp-accordions')]")[0];

        //json data
        $this->json_data=json_decode( str_replace("undefined" , "false" , explode("=" , $this->xml->xpath("body//script[2]")[0]->__toString() , 2)[1]) , true);
        $this->json_data=Arr::only($this->json_data , "productStore")["productStore"]["data"];
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void {

        try {
            $this->name = $this->json_data["productName"];
            return;
        }
        catch ( Error | Exception $e) {
            $this->log_error("Product Name First Method");
        }

        try {
            $remove_buy = explode("Buy" ,$this->document->getElementsByTagName("title")->item(0)->textContent)[1];
            $this->name= trim(explode('|' , $remove_buy)[0]) ;
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name Second Method");
        }

        try {
            $this->name = trim($this->core_product->xpath("//span[@data-test='product-title'][1]")[0]
                ->__toString());
        }
        catch ( Error | Exception $e) {
            $this->log_error("Product Name Third Method");
        }

    }

    public function get_image(): void {

        try {
            $this->image=$this->json_data["media"]["images"][0];
            return;
        }
        catch ( Error | Exception $e) {
            $this->log_error("The Image First Method");
        }

        try {
            $this->image="https:" . $this->core_product->xpath("//*[@data-test='component-media-gallery']//img[1]")[0]->attributes()->{'src'}->__toString();
        }
        catch ( Error | Exception $e) {
            $this->log_error("The Image Second Method");
        }

    }

    public function get_price(): void {

        try {
            $this->price= (float)  $this->json_data["prices"]["attributes"]["now"];
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("First Method Price");
        }

        try {
            $this->price=  (float) substr($this->right_column->xpath("//li[@itemprop ='price']//h2")[0]->__toString(), 2);
            return;
        }
        catch (Error | \Exception $e ) {
            $this->log_error("Price Second");
        }

        try {
            $this->price= (float) $this->right_column->xpath("//li[@itemprop ='price']")[0]->attributes()->{'content'}->__toString();
            return;
        }
        catch (Error | \Exception $e )
        {
            $this->log_error( "Price Third");
        }
    }

    public function get_used_price(): void {$this->price_used=0;}

    public function get_stock(): void {
        try {
            $this->in_stock= $this->json_data["attributes"]["deliverable"];
        }
        catch (\Exception $e){
            $this->log_error( "Stock");
        }
    }

    public function get_no_of_rates(): void {
        try {
            $this->no_of_rates= (int) $this->json_data["ratingSummary"]["attributes"]["reviewCount"];
            return;
        }
        catch (Error | Exception $e)
        {
            $this->log_error("No. Of Rates First Method");
        }

        try {
            $this->no_of_rates = (int) $this->core_product->xpath("//span[@itemprop='ratingCount']")[0]->__toString();
        }
        catch (Error | Exception $e)
        {
            $this->log_error("No. Of Rates Second Method");
        }
    }

    public function get_rate(): void {
        try {
            $this->rating= round((float) $this->json_data["ratingSummary"]["attributes"]["avgRating"],1);
            return;
        }
        catch (Error | Exception $e )
        {
            $this->log_error("The Rate First Method");
        }

    }

    public function get_seller(): void {$this->seller="Argos";}

    public function get_shipping_price(): void {
        try {
            if ($this->json_data["attributes"]["freeDelivery"])
                $this->shipping_price=0;
            else
                $this->shipping_price=  (float) $this->json_data["attributes"]["deliveryPrice"];

        }
        catch (Error  | Exception $e)
        {
            $this->log_error("Shipping Price");
        }
    }





    public static function get_variations($url) : array {
        $response=parent::get_website($url);

        parent::prepare_dom($response ,$document ,$xml);

        //json data
        $json_data=json_decode( str_replace("undefined" , "false" , explode("=" , $xml->xpath("body//script[2]")[0]->__toString() , 2)[1]) , true);
        $json_data=Arr::only($json_data , "productStore")["productStore"]["data"];

        try {
            $variants=$json_data["variants"]["attributes"]["variants"];
            foreach ($variants as $variant) {
                foreach ($variant["attributes"] as $single_attribute) {
                    $option_string = $single_attribute["value"] . " - ";
                }
                $options[$variant["partNumber"]] =  Str::beforeLast($option_string , " - ") ;
            }

            return $options ?? [];

        } catch (Exception){
            Notification::make()
                ->danger()
                ->title("Existing Product")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();
        }

        return  [];
    }


    public static function prepare_url($domain, $product , ?Store $store = null): array|string {
        return Str::replace(["store", "product_id"], [$domain , Str::upper($product)], self::MAIN_URL);
    }


    function crawler(): void { parent::crawl_url();}

    public function get_condition() {}
}
