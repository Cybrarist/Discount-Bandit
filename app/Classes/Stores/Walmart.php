<?php

namespace App\Classes\Stores;

use App\Classes\MainStore;
use App\Classes\URLHelper;
use App\Interfaces\StoreInterface;
use App\Models\Product;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;
use function App\Classes\error;

class Walmart extends MainStore
{
    const MAIN_URL="https://store/ip/product" ;

//    ?tag=referral_code
    private $information;

    public function __construct($product_store_id) {
        parent::get_record($product_store_id);
        $this->product_url= self::prepare_url($this->total_record->domain, $this->total_record->walmart_ip);
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
        $product_info= $this->xml->xpath("//script[@type='application/ld+json']")[0]->__toString();
        $this->information=json_decode($product_info);
    }

    public function crawling_process(){


        //if the product already has a name, no need to crawl it again.
        if (!$this->total_record->product_name){
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
        $this->get_condition();
//        $this->get_shipping_price();

        parent::update_store_product_details(
            $this->total_record->product_store_id,
            [
            'price' => (int) $this->price,
            'number_of_rates' => $this->no_of_rates,
            'seller' => "Walmart",
            'rate' => $this->rating,
            'shipping_price' => 0,
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
     * Get the data from the store
     */

    public function get_name(){

        try {
            $this->name=$this->information->name;
            return;
        }
        catch (Error | Exception $e){
            $this->throw_error("Product Name First Method");
        }

        try {
            $this->name = $this->document->getElementById("main-title")->textContent;
        }
        catch ( Error | Exception $e) {
            $this->throw_error("Product Name Second Method");
            $this->name = "NA";
        }
    }
    public function get_image(){

        try {
            $this->image = $this->information->image;
        }
        catch ( Error | Exception $e) {
            $this->throw_error("The Image");
            $this->image = "";
        }

    }
    public function get_price(){
        //method 1 to return the price of the product
        try {
            $this->price= 100 * (float) $this->information->offers->price;
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->throw_error("First Method Price");
        }
        try {
            $this->price= 100 * (float) self::get_numbers_only_with_dots($this->xml->xpath("//span[@itemprop='price']")[0]->__toString());
        }
        catch ( Error | \Exception  $e )
        {
            $this->throw_error("Second Method Price");
            $this->price=0;
        }
    }
    public function get_stock(){
        try {
            $this->in_stock =  (Str::contains($this->information->offers->availability , "instock" , true));
        }catch (\Exception $e){
            $this->throw_error( "Stock");
            $this->in_stock=true;
        }
    }
    public function get_no_of_rates(){
        try {
            $this->no_of_rates=$this->information->aggregateRating->reviewCount;
            return;
        }
        catch (Error | Exception $e)
        {
            $this->throw_error(" First Method No. Of Rates");
        }
        try {
            $this->no_of_rates=(int) $this->xml->xpath("//a[@itemprop='ratingCount']")[0]->__toString();
        }
        catch (Error | Exception $e)
        {
            $this->throw_error(" First Method No. Of Rates");
            $this->no_of_rates=0;
        }
    }
    public function get_rate(){
        try {
            $this->rating=$this->information->aggregateRating->ratingValue;
            return;
        }
        catch (Error | Exception $e )
        {
            $this->throw_error("The Rate First Method");
        }
        try {
            $this->rating=Str::remove(['(',')'] , $this->xml->xpath("//span[contains(@class , 'rating-number')]")[0]->__toString());
        }
        catch (Error | Exception $e )
        {
            $this->throw_error("The Rate First Method");
            $this->rating="NA";
        }

    }
    public static function prepare_url($domain, $product, $ref=""){
        return Str::replace(
            ["store", "product", "referral_code"],
            [$domain , $product, $ref],
            self::MAIN_URL);
    }

    public function get_seller(){

        try {
            $this->seller=$this
                ->right_column
                ->xpath("//div[@id='merchantInfoFeature_feature_div']//div[@class='offer-display-feature-text']//span")[0]
                ->__toString();
            return;
        }
        catch (Error | Exception $e )
        {
            $this->throw_error("The Seller First Method" );
        }

        try {
            $this->seller=$this
                ->right_column
                ->xpath("//div[@id='merchantInfoFeature_feature_div']//a[@id='sellerProfileTriggerId']")[0]
                ->__toString();
            return;
        }
        catch (Error | Exception $e )
        {
            $this->throw_error("The Seller Second method" );
        }

        //seller method for subscribe and save items
        try {
            $this->seller=$this
                ->right_column
                ->xpath("//div[@id='shipsFromSoldByMessage_feature_div']//span")[0]
                ->__toString();
            //trim the spaces
            $this->seller = trim($this->seller);
            $this->seller=explode('by ' , $this->seller)[1] ?? $this->seller;

            return;
        }
        catch (Error | Exception $e )
        {
            $this->throw_error("The Seller Third Method" );
            $this->seller="";
        }
    }
    public function get_shipping_price(){
        try {
            $shipping_price=$this->right_column->xpath("//div[@id='deliveryBlockMessage']//span[@data-csa-c-delivery-price]")[0]->__toString();
            $this->shipping_price= (int) Str::finish(Str::replace("." , ""  , get_numbers_only_with_dot($shipping_price) ) , "00");
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

        self::prepare_dom($response ,$document ,  $xml);
        try {
            $variations=$xml->xpath("//div[@id='item-page-variant-group-bg-div']//div[@class='dn']/a");
            foreach ($variations as $variation){
                $temp_ip=Str::remove("/" , Str::squish(  \Arr::last(explode("/" , $variation->attributes()->href->__toString()))) );
                $options[$temp_ip]=$variation->__toString();
            }
            return $options ?? [];

        } catch (\Exception $e){
            Log::error("couldn't get variations \n $e");
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
                        ['walmart_ip'=>$single_variation],
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

            Log::error("Something Wrong Happened while getting the variation for product".
                    $settings['url'] . ",  Please share the following details:\n $e"
                );
        }
    }

    //static functions to be called anywhere
    public static function validate_walmart_url(URLHelper $url): bool
    {
        try {
            $url->get_walmart_ip();
            return true;
        }
        catch (\Exception) {
            Notification::make()
                ->danger()
                ->title("Unrecognized URL scheme")
                ->body("
                    it should be like the following:<br>
                    <span style='color:green'> https://$url->domain/ip/some_stuff/unique_code</span>")
                ->persistent()
                ->send();
            return false;
        }

    }

    public static function is_product_unique(URLHelper $url  ,$record_id=null): bool
    {
        $products_with_the_same_walmart_ip=Product::where('walmart_ip' , $url->get_walmart_ip());
        if ($record_id)
            $products_with_the_same_walmart_ip->whereNot('id' , $record_id);

        $product=$products_with_the_same_walmart_ip->first();
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
        return self::validate_walmart_url($url) && self::is_product_unique($url);
    }
    public function get_condition()
    {
        //todo search for the different item conditions in the wbesite
        try {
            $this->condition = (Str::contains($this->information->offers->itemCondition , "NewCondition" , true)) ? "new" : "used" ;
        }catch (\Exception){
            $this->throw_error("First Part Condition");
            $this->condition="new";
        }
    }
}
