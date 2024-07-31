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

class Diy extends StoreTemplate
{
    const MAIN_URL="https://store/departments/random/product_id_BQ.prd" ;
    private  $left_column;
    private $right_column;
    private $json_data;

    public function __construct(private int $product_store_id) {

        //get the current
        parent::get_record($product_store_id);

        //get the final url
        $this->product_url= self::prepare_url(
            domain: $this->current_record->store->domain,
            product: $this->current_record->key,
            store: $this->current_record->store
        );


        //crawl the url and get the data
        try {
            parent::crawl_url();
            self::prepare_sections_to_crawl();
        }
        catch (\Exception $exception){
            dd($exception);
            Context::add("product", $this->current_record->product);
            Log::error("Couldn't Crawl the website for the following url $this->product_url \n");
            return;
        }


        //crawl the website to get the important information
        self::crawling_process();

        //check for the notification settings
        $this->check_notification();

    }
    public function prepare_sections_to_crawl(){

        //get the right column to get the seller and other data
        $this->right_column=$this->xml->xpath("//div[@id='product-availability']");
        dd($this->right_column);
        //get the left column for the images
        $this->left_column=$this->xml->xpath("//div[@class='slick-list']")[0];

        $this->json_data=json_decode($this->xml->xpath("//script[@data-test-id='product-page-structured-data']")[0]->__toString() , true)["mainEntity"];

    }


    /**
     * Get the data from the store
     */
    public function get_name(){
        try {
            $this->name = $this->json_data["name"];
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }

        try {
            $this->name = Str::squish(explode("|" ,$this->document->getElementsByTagName("title")->item(0)->textContent)[0]);
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name Second Method");
        }

        try {
            $this->name = $this->right_column->xpath("//h1[@id='product-title']")[0];
            return;
        }
        catch ( Error | Exception $e) {
            $this->log_error("Product Name Third Method");
        }

        try {
            $this->name = $this->document->getElementById("product-title")->textContent;
        }
        catch ( Error | Exception $e) {
            $this->log_error("Product Name Fourth Method");
            $this->name = "NA";
        }

    }



    public function get_image(){
        try {
            $this->image = explode("?" , $this->json_data["image"])[0];
            return;
        }
        catch ( Error | Exception $e) {
            $this->log_error("The Image First Method");
        }
        try {
            $this->image = explode("?" ,$this->left_column->xpath("//div[@data-test-id='PrimaryImage']//img")[0]->attributes()->src->__toString() )[0];
        }
        catch ( Error | Exception $e) {
            $this->log_error("The Image Second Method");
            $this->image = "";
        }
    }

    public function get_price(){


        //method 1 to return the price of the product
        try {
            $this->price= (float) $this->json_data["offers"]["price"];
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("First Method Price");
        }
        //method 2 to return the price of the product
        try {
            $this->price= (float) $this->right_column->xpath("//div[@data-test-id='product-primary-price']//div")[0]->__toString();
            return;
        }
        catch (Error | \Exception $e )
        {
            $this->log_error( "Price Second");
            $this->price=0;
        }
    }

    public function get_used_price(){$this->price_used=0;}

    public function get_stock(): void {
        try {
            $this->in_stock=Str::contains($this->json_data["offers"]["availability"] , "instock", true);
            return;
        }catch (\Exception $e){
            $this->log_error( "Stock Availability First Method");
            $this->in_stock=true;
        }
    }
    public function get_no_of_rates(){
        try {
            $ratings=Str::remove(["(",")"," "] , $this->right_column->xpath("//span[@data-test-id='RatingTotalReviews']")[0]->__toString());
            $this->no_of_rates= (int) get_numbers_only_with_dot($ratings);
        }
        catch (Error | Exception $e)
        {
            $this->log_error("No. Of Rates");
            $this->no_of_rates=0;
        }
    }

    public function get_rate(){
        try {
            //get the full stars
            $full_stars=sizeof($this->right_column->xpath("//div[@data-test-id='RatingStars']//i[@title='Full star']"));
            $half_stars=sizeof($this->right_column->xpath("//div[@data-test-id='RatingStars']//i[@title='Half star']"));

            $this->rating=$full_stars + $half_stars/2;
        }
        catch (Error | Exception $e )
        {
            $this->log_error("The Rate");
            $this->rating= -1;
        }
    }

    public function get_seller(): void {$this->seller="DIY";}

    public function get_shipping_price(): void {$this->shipping_price=0;}


    /**
     *  implementation functions for crawler for notification decision
     */

    public static function get_variations($url) : array
    {
        $response=self::get_website($url);
        self::prepare_dom($response ,$document ,$xml);
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

        } catch (\Exception $e){
            Log::error("couldn't get the variation");

            Notification::make()
                ->danger()
                ->title("Existing Product")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();
        }
        return  [];
    }


    public static function prepare_url($domain, $product , ?Store $store = null): string
    {
        return Str::replace(["store", "product_id"], [$domain , Str::upper($product)], self::MAIN_URL);
    }

    public function get_condition(): void { $this->condition="New";}
}
