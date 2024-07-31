<?php

namespace App\Helpers\StoresAvailable;

use Error;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Ebay extends StoreTemplate
{
    const MAIN_URL="https://store/itm/product_id" ;


    private $right_column;
    private $information;



    public function __construct(private int $product_store_id) {
        parent::__construct($this->product_store_id);
    }

    public function prepare_sections_to_crawl(): void
    {
        try{
            $this->information=json_decode(
                $this->xml
                    ->xpath("//div[contains(@class , 'x-seo-structured-data')]//script")[0]
                    ->__toString());
            $this->information= Arr::keyBy($this->information , '@type')['Product'];

        }catch (Exception){
            $this->log_error("Information Crawling");
        }

        try {
//            //get the center column to get the related data for it
//            $this->left_column=$this->xml->xpath("//div[@id='LeftSummaryPanel']");
            //get the right column to get the seller and other data
            $this->right_column=$this->xml->xpath("//div[@id='RightSummaryPanel']")[0];
        }
        catch (Exception )
        {
            $this->log_error("Crawl The Website");
            return;
        }

    }


    /**
     * Helper Functions
     */


    /**
     * Get the data from the store
     */
    public function get_name(): void {
        try {
            $this->name = $this->information->name;
            return;
        }
        catch ( Exception $e) {
            $this->log_error("First Method Name");
        }

        try {
            $this->name=$this->right_column->xpath("//h1[@class='x-item-title__mainTitle']//span")[0]->__toString();
        }
        catch ( Exception $e) {
            $this->log_error("Second Method Name");
            $this->name="NA";
        }

    }
    public function get_image():void {

        try {
            $this->image = $this->information->image ?? "NA";
        }
        catch ( Exception $e) {
            $this->log_error("Image First Method");
            $this->image = "";
        }
    }
    public function get_price(): void {
        try {
            $this->price=(float) $this->information->offers->price;
        }
        catch (Exception  $e )
        {
            $this->log_error("Price First Method");
        }

    }

    public function get_used_price(){ $this->price_used=0;}
    public function get_stock(): void {
        try {
            $schema=Str::lower( Arr::last( explode("/" , $this->information->offers->availability)));
            ($schema=="instock") ? $this->in_stock = true : $this->in_stock=false;
        } catch (\Exception $e){
            $this->log_error("Stock Availability");
            $this->in_stock = true;
        }

    }
    public function get_no_of_rates(): void{

        //todo check if ebay enabled the rating again
//        try {
//            dd($this->information);
//            $ratings=$this->information->aggregateRating->ratingCount
//                ??  $this->left_column->xpath("//span[@class='ebay-reviews-count']")[0]->__toString();
//            $this->no_of_rates= filter_var($ratings, FILTER_SANITIZE_NUMBER_INT);
//            return;
//        }
//        catch (Exception $e)
//        {
//            dd($e);
//            $this->log_error("No. Of Rates");
//        }
        $this->no_of_rates=0;
    }
    public function get_rate():void {
//        todo check if ebay enabled rating again
//        try {
//            $this->rating=$this->information->aggregateRating->ratingValue
//            ?? get_numbers_only_with_dot($this->left_column->xpath("//div[@id='histogramid']//span[@class='ebay-review-start-rating']")[0]->__toString() )  ;
//        }
//        catch (Exception $e )
//        {
//            $this->log_error($e , "The Rate", $this->product->asin , $this->store->host);
            $this->rating=-1;
//        }
    }

    public function get_seller(): void{
        try {
            $this->seller=$this->right_column->xpath("//div[@class='ux-seller-section__item--seller']//span")[0]->__toString();
        }
        catch (Error | \Exception  )
        {
            $this->log_error("The Seller First Method");
        }

        try {
            $this->seller=$this->right_column->xpath("//div[@class='x-sellercard-atf__info__about-seller']//span")[0]->__toString();
        }
        catch (Error | Exception )
        {
            $this->log_error("The Seller Second Method");
            $this->seller="NA";
        }


    }

    public function get_shipping_price(): void
    {

        try{
            $this->shipping_price= (float) ($this->information->offers->shippingDetails->shippingRate->value);
        }catch (Exception $e){
            $this->log_error("Shipping Price");
        }
    }

    /**
     *  implementation functions for crawler for notification decision
     */

    public static function get_variations() : void {}

    public static function prepare_url($domain, $product, $store = null): string
    {

        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }

    public function  get_condition(): void{
        try{
            $schema=Arr::last( explode("/" , $this->information->offers->itemCondition));
            $this->condition=Str::squish(Str::replace('condition' , '' ,  Str::headline($schema), false));
        }  catch (Exception)
        {
            $this->log_error("The Condition ");
            $this->condition="New";
        }
    }

    function crawler(): void {
        $this->crawl_url();
    }
}
