<?php

namespace App\Classes\Stores;

use App\Classes\MainStore;
use App\Classes\URLHelper;
use App\Interfaces\StoreInterface;
use App\Models\Product;
use App\Models\ProductStore;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function App\Classes\error;

class Argos extends MainStore
{
    const MAIN_URL="https://store/product/product_id?tag=referral_code" ;
    private  $core_product;
    private $right_column;
    private $accordions;

    private $json_data;

    public function __construct($product_store_id) {
        parent::get_record($product_store_id);

        $this->product_url= self::prepare_url($this->total_record->domain, $this->total_record->argos_id);

        //crawl the url and get the data
        try {
            parent::crawl_url();
            self::prepare_sections_to_crawl();
        }
        catch (\Exception){
            Log::error("Couldn't Crawl the website for the following url $this->product_url \n");
            return;
        }

        //crawl the website to get the important information
        $this->crawling_process();

        //check for the notification settings
        $this->check_notification();

    }
    public function prepare_sections_to_crawl(){

        //get the center column to get the related data for it
        $this->core_product=$this->xml->xpath("//section[contains(@class , 'pdp-core')]")[0];
        //get the right column to get the seller and other data
        $this->right_column=$this->xml->xpath("//section[contains(@class , 'pdp-right')]")[0];
        $this->accordions=$this->xml->xpath("//section[contains(@class , 'pdp-accordions')]")[0];

        //json data
        $this->json_data=json_decode( str_replace("undefined" , "false" , explode("=" , $this->xml->xpath("body//script[2]")[0]->__toString() , 2)[1]) , true);
        $this->json_data=Arr::only($this->json_data , "productStore")["productStore"]["data"];
    }

    public function crawling_process(){

        //if the product already has a name, no need to crawl it again.
        if (!$this->total_record?->product_name){
            $this->get_name();
            $this->get_image();
            $this->update_product_details($this->total_record->product_id ,[
                'name'=>$this->name,
                'image'=>$this->image
            ]);
        }

        $this->get_price();
        $this->get_stock();
        $this->get_no_of_rates();
        $this->get_rate();
        $this->get_seller();
        $this->get_shipping_price();
        parent::update_store_product_details(
            $this->total_record->product_store_id,
            [
            'price' => (int)((float)$this->price),
            'number_of_rates' => $this->no_of_rates,
            'seller' => $this->seller,
            'rate' => $this->rating,
            'shipping_price' => $this->shipping_price,
            'condition'=>"new",
            'in_stock'=>$this->in_stock,
            'notifications_sent' => ($this->check_notification()) ? ++$this->total_record->notifications_sent : $this->total_record->notifications_sent ,
            ]
        );

        parent::record_price_history(
            product_id: $this->total_record->product_id,
            store_id: $this->total_record->store_id,
            price: $this->price
        );

    }


    /**
     * Helper Functions
     */


    /**
     * Get the data from the store
     */
    public function get_name(){

        try {
            $this->name = $this->json_data["productName"];
            return;
        }
        catch ( Error | Exception $e) {
            $this->throw_error("Product Name First Method");
        }

        try {
            $remove_buy = explode("Buy" ,$this->document->getElementsByTagName("title")->item(0)->textContent)[1];
            $this->name= trim(explode('|' , $remove_buy)[0]) ;
            return;
        }
        catch (Error | Exception $e){
            $this->throw_error("Product Name Second Method");
        }

        try {
            $this->name = trim($this->core_product->xpath("//span[@data-test='product-title'][1]")[0]
                ->__toString());
        }
        catch ( Error | Exception $e) {
            $this->throw_error("Product Name Third Method");
            $this->name = "NA";
        }


    }
    public function get_image(){

        try {
            $this->image=$this->json_data["media"]["images"][0];
            return;
        }
        catch ( Error | Exception $e) {
            $this->throw_error("The Image First Method");
        }

        try {
            $this->image="https:" . $this->core_product->xpath("//*[@data-test='component-media-gallery']//img[1]")[0]->attributes()->{'src'}->__toString();
            }
        catch ( Error | Exception $e) {
            $this->throw_error("The Image Second Method");
        }

    }
    public function get_price(){
        //method 1 to return the price of the product
        try {
            $this->price= 100 *  $this->json_data["prices"]["attributes"]["now"];
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->throw_error("First Method Price");
        }
//        method 2 to return the price of the product
        try {
            $this->price= 100*  (float) substr($this->right_column->xpath("//li[@itemprop ='price']//h2")[0]->__toString(), 2);
            return;
        }
        catch (Error | \Exception $e ) {
            $this->throw_error("Price Second");
        }

        try {
            $this->price= 100 *  (float) $this->right_column->xpath("//li[@itemprop ='price']")[0]->attributes()->{'content'}->__toString();
            return;
        }
        catch (Error | \Exception $e )
        {
            $this->throw_error( "Price Third");
            $this->price=0;
        }

    }
    public function get_stock(){

        try {
            $this->in_stock= $this->json_data["attributes"]["deliverable"];
        }
        catch (\Exception $e){
            $this->throw_error( "Stock");
            $this->in_stock=true;
        }
    }
    public function get_no_of_rates(){
        try {
            $this->no_of_rates= (int) $this->json_data["ratingSummary"]["attributes"]["reviewCount"];
            return;
        }
        catch (Error | Exception $e)
        {
            $this->throw_error("No. Of Rates First Method");
        }

        try {
            $this->no_of_rates = (int) $this->core_product->xpath("//span[@itemprop='ratingCount']")[0]->__toString();
        }
        catch (Error | Exception $e)
        {
            $this->throw_error("No. Of Rates Second Method");
        }
    }
    public function get_rate(){
        try {
            $this->rating= round((float) $this->json_data["ratingSummary"]["attributes"]["avgRating"],1);
            return;
        }
        catch (Error | Exception $e )
        {
            $this->throw_error("The Rate First Method");
            $this->rating= -1;
        }

    }
    public static function prepare_url($domain, $product, $ref=""){
        return Str::replace(
            ["store", "product_id", "referral_code"],
            [$domain , $product, $ref],
            self::MAIN_URL);
    }

    public function get_seller(){
        $this->seller="argos";
    }
    public function get_shipping_price(){

        try {
            if ($this->json_data["attributes"]["freeDelivery"])
                $this->shipping_price=0;
            else
                $this->shipping_price=  100 * $this->json_data["attributes"]["deliveryPrice"];

        }
        catch (Error  | Exception $e)
        {
            $this->throw_error("Shipping Price");
            $this->shipping_price= 0;
        }
    }

    /**
     *  implementation functions for crawler for notification decision
     */
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

        //if the seller is not amazon, then don't notify the user
        if ($this->total_record->only_official && ! self::is_amazon($this->seller))
            return false;

        if ($this->total_record->lowest_within &&
            parent::is_price_lowest_within(
                product_id:  $this->total_record->product_id ,
                store_id: $this->total_record->store_id,
                days: $this->total_record->lowest_within,
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
//
//

    return  [];
    }

    public static function insert_variation($variations , $store , $settings){

        try {
            foreach ($variations as $single_variation)
                {
                    $store->products()->withPivot('notify_price')->updateOrCreate(
                        ['argos_id'=>$single_variation],
                        [
                            'favourite' => $settings['favourite'],
                            'lowest_within'=>$settings['lowest_within'],
                            'max_notifications'=>$settings['max_notifications'],
                            'snoozed_until'=>$settings['snoozed_until'],
                            'status'=>$settings['status'],
                            'only_official'=>$settings['only_official'],
                            'stock'=>$settings['stock'],
                        ],
                        [
                            'product_store.notify_price'=>$settings['notify_price'] * 100,
                        ]
                    );
                }

        }
        catch (\Exception $e){
            Notification::make()
                ->warning()
                ->title("Something Wrong Happened")
                ->body("can you please check your logs and share it with the developer"
                )
                ->persistent()
                ->send();

            error("Something Wrong Happened while getting the variation for product".
                    $settings['url'] . ",  Please share the following details:\n $e"
                );
        }
    }

    //static functions to be called anywhere
    public static function validate_argos_url(URLHelper $url)
    {
        try {
            $url->get_argos_product_id();
            if (sizeof(explode("/" ,$url->path )) !=3)
                throw new Exception();
            return  true;
        }
        catch (\Exception) {
            Notification::make()
                ->danger()
                ->title("Unrecognized URL scheme")
                ->body("
                    it should be like the following:<br>
                    <span style='color:green'> https://$url->domain/product/unique_code</span>")
                ->persistent()
                ->send();
            return false;
        }

    }

    public static function is_product_unique(URLHelper $url  ,$record_id=null)
    {
        $products_with_the_same_argos_id=Product::where('argos_id' , $url->get_argos_product_id());

        if ($record_id)
            $products_with_the_same_argos_id->whereNot('product_id' , $record_id);

        $product=$products_with_the_same_argos_id->first();
        if (!$product)
            return true;
        else{
            $product_url=route("filament.admin.resources.products.edit" , $product->id) ;
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
        return self::validate_argos_url($url) && self::is_product_unique($url);
    }
    public function get_condition()
    {
        // TODO: Implement get_condition() method.
    }
}
