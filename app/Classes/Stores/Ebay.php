<?php

namespace App\Classes\Stores;

use App\Classes\MainStore;
use App\Classes\URLHelper;
use App\Models\ProductStore;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Ebay extends MainStore
{
    const MAIN_URL="https://store/itm/product_id" ;

    private $right_column;
    private $left_column;
    private $information;
    public function __construct($product_store_id) {

        parent::get_record($product_store_id);

        //prepare the url template
        $this->product_url= parent::prepare_url(
            domain: $this->current_record->store->domain,
            product: $this->current_record->ebay_id,
            store_url_template: self::MAIN_URL,
        );

        try {
            parent::crawl_url();
            $this->prepare_sections_to_crawl();
        }
        catch (\Exception $exception){
            dd($exception);
            Log::error("Couldn't Crawl the website for the following url $this->product_url \n");
            return;
        }

        //crawl the website to get the important information
        $this->crawling_process();
    }


    /**
     * Helper Function for crawling
     */

    public function prepare_sections_to_crawl(): void{
        try{
            $this->information=json_decode(
                $this->xml
                    ->xpath("//div[contains(@class , 'x-seo-structured-data')]//script")[0]
                    ->__toString());
            $this->information= \Arr::keyBy($this->information , '@type')['Product'];

        }catch (\Exception $e){
            $this->throw_error("Information Crawling");
        }

        try {
//            //get the center column to get the related data for it
//            $this->left_column=$this->xml->xpath("//div[@id='LeftSummaryPanel']");
            //get the right column to get the seller and other data
            $this->right_column=$this->xml->xpath("//div[@id='RightSummaryPanel']")[0];
        }
        catch (\Exception $e)
        {
            $this->throw_error("Crawl The Website");
            return;
        }
    }

    public function crawling_process(){
        //if the product already has a name, no need to crawl it again.
        //also check if the product is availble with amazon, since i want to give amazon the priority
        //due to proper naming the products

        $amazon_stores=ProductStore::where('product_id', $this->current_record->product_id)
            ->whereHas('store' ,function($query){
                $query->where('domain' , 'Like' , '%amazon%');
            })->count();


        if (!$this->current_record->product->name && !$amazon_stores){
            $this->get_name();
            $this->get_image();
            $this->update_product_details($this->current_record->product_id ,[
                'name'=>$this->name,
                'image'=>$this->image
            ]);
        }

        $this->get_price();
        $this->get_stock();
        $this->get_seller();
        $this->get_condition();
        $this->get_shipping_price();


        $this->current_record->update([
            'price' => (float) $this->price,
            'seller' => $this->seller,
            'shipping_price' => $this->shipping_price,
            'condition'=>$this->condition,
            'in_stock'=>$this->in_stock,
            'notifications_sent' => ($this->check_notification()) ? ++$this->current_record->notifications_sent : $this->current_record->notifications_sent ,
        ]);


        parent::record_price_history(
            product_id: $this->current_record->product_id,
            store_id: $this->current_record->store_id,
            price: $this->price
        );

    }

    /**
     * Get the data from the store
     */
    public function get_name(): void {
        try {
            $this->name = $this->information->name;
            return;
        }
        catch ( Exception $e) {
            $this->throw_error("First Method Name");
        }

        try {
            $this->name=$this->right_column->xpath("//h1[@class='x-item-title__mainTitle']//span")[0]->__toString();
        }
        catch ( Exception $e) {
            $this->throw_error("Second Method Name");
            $this->name="NA";
        }

    }
    public function get_image():void {

        try {
            $this->image = $this->information->image ?? "NA";
        }
        catch ( Exception $e) {
            $this->throw_error("Image First Method");
            $this->image = "";
        }
    }
    public function get_price(): void {
        try {
            $this->price=(float) $this->information->offers->price;
        }
        catch (\Exception  $e )
        {
            $this->throw_error("Price First Method");
            $this->price=0;
        }

    }
    public function get_stock(): void {
        try {
            $schema=Str::lower( \Arr::last( explode("/" , $this->information->offers->availability)));
            ($schema=="instock") ? $this->in_stock = true : $this->in_stock=false;
        } catch (\Exception $e){
            $this->throw_error("Stock Availability");
            $this->in_stock = true;
        }

    }
    public function get_seller(): void{
        try {
            $this->seller=$this->right_column->xpath("//div[@class='ux-seller-section__item--seller']//span")[0]->__toString();
        }
        catch (\Error | \Exception $e )
        {
            $this->throw_error("The Seller First Method");
        }

        try {
            $this->seller=$this->right_column->xpath("//div[@class='x-sellercard-atf__info__about-seller']//span")[0]->__toString();
        }
        catch (\Error | \Exception $e )
        {
            $this->throw_error("The Seller Second Method");
            $this->seller="NA";
        }


    }
    public function  get_condition(): void{
        try{
            $schema=\Arr::last( explode("/" , $this->information->offers->itemCondition));
            $this->condition=Str::squish(Str::replace('condition' , '' ,  Str::headline($schema), false));
        }  catch (Exception $e)
        {
            $this->throw_error("The Condition ");
            $this->condition="New";
        }
    }
    public function get_shipping_price(){

        try{
            $this->shipping_price= (float) ($this->information->offers->shippingDetails->shippingRate->value);
        }catch (\Exception $e){
            $this->throw_error("Shipping Price");
            $this->shipping_price=0;
        }
    }
    public function get_variations() : void {}
    public static function validate_ebay_url(URLHelper $url)
    {
        try {
            $url->get_ebay_item_id();
            return true;
        }
        catch (\Exception) {
            Notification::make()
                ->danger()
                ->title("Unrecognized URL scheme")
                ->body("
                    it should be like the following:<br>
                    <span style='color:green'> https://$url->domain/itm/unique_code</span>")
                ->persistent()
                ->send();
            return false;
        }

    }
    public static function is_product_unique(URLHelper $url  ,$record_id=null): bool
    {
        $products_with_the_same_ebay_id=ProductStore::where('ebay_id' , $url->get_ebay_item_id());

        if ($record_id)
            $products_with_the_same_ebay_id->whereNot('product_id' , $record_id);
        $product_store=$products_with_the_same_ebay_id->first();
        if (!$product_store)
            return true;
        else{
            $product_url=route("filament.admin.resources.products.edit" , $product_store->product_id) ;
            Notification::make()
                ->danger()
                ->title("Existing Product")
                ->body("This product already exists in your database. check it from
                <a href='$product_url' target='_blank' style='color: #dc2626'> $product_url</a>"
                )
                ->persistent()
                ->send();
            return false;
        }
    }
    public static function validate($url)
    {
        return self::validate_ebay_url($url) && self::is_product_unique($url) ;
    }

    public function check_notification(): bool
    {
        if ($this->notification_snoozed())
            return false;

        if ($this->stock_available()){
            $this->notify();
            return true;
        }


        if (!$this->price_crawled_and_different_from_database())
            return false;

        //todo check if ebay is selling or everything through 3rd party

        if ($this->current_record->product->lowest_within &&
            parent::is_price_lowest_within(
                product_id:  $this->current_record->product_id ,
                store_id: $this->current_record->store_id,
                days: $this->current_record->product->lowest_within,
                price: $this->price
            )){
            $this->notify();
            return true;
        }

        if ($this->max_notification_reached())
            return false;


        if ($this->price_reached_desired()){
            $this->notify();
            return true;
        }
        return false;
    }
    public function get_no_of_rates(){

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
//            $this->throw_error("No. Of Rates");
//        }
//        $this->no_of_rates=0;
    }

    public function get_rate(){
//        todo check if ebay enabled rating again
//        try {
//            $this->rating=$this->information->aggregateRating->ratingValue
//            ?? get_numbers_only_with_dot($this->left_column->xpath("//div[@id='histogramid']//span[@class='ebay-review-start-rating']")[0]->__toString() )  ;
//        }
//        catch (Exception $e )
//        {
//            $this->throw_error($e , "The Rate", $this->product->asin , $this->store->host);
//            $this->rating=-1;
//        }
    }


}

