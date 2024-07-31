<?php

namespace App\Helpers\StoresAvailable;

use App\Classes\URLHelper;
use App\Helpers\GeneralHelper;
use App\Models\Currency;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Fnac extends StoreTemplate
{

    const MAIN_URL="https://store/product_id" ;
    private  $center_column;
    private $right_column;

    public function __construct($product_store_id) {

        parent::get_record($product_store_id);


        //prepare the url template
        $this->product_url= self::prepare_url(
            domain: $this->current_record->store->domain,
            product: $this->current_record->key,
        );

        //crawl the url and get the data
        try {
            parent::crawl_url_chrome();
            self::prepare_sections_to_crawl();
        }
        catch (\Exception $exception){
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
        $this->center_column=$this->xml->xpath("//div[@id='centerCol']")[0];
        //get the right column to get the seller and other data
        $this->right_column=$this->xml->xpath("//div[@id='desktop_buybox']")[0];
    }

    public function crawling_process(){
        //if the product already has a name, no need to crawl it again.
        if (!$this->current_record->product->name || !$this->current_record->product->image){
            $this->get_name();
            $this->get_image();
            $this->update_product_details($this->current_record->product_id ,[
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

        $this->current_record->update([
            'price' => (float)$this->price,
            'number_of_rates' => $this->no_of_rates,
            'seller' => $this->seller,
            'rate' => $this->rating,
            'shipping_price' => $this->shipping_price,
            'condition'=>"new",
            'in_stock'=>$this->in_stock,
            'notifications_sent' => ($this->check_notification()) ? ++$this->current_record->notifications_sent : $this->current_record->notifications_sent ,
        ]);

        parent::record_price_history(
            product_id: $this->current_record->product_id,
            store_id: $this->current_record->store_id,
            price:  $this->price
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
            $this->name = explode(":" ,$this->document->getElementsByTagName("title")->item(0)->textContent)[0];
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }

        try {
            $this->name = trim($this->center_column->xpath("//span[@id='productname'][1]")[0]
                ->__toString());
        }
        catch ( Error | Exception $e) {
            $this->log_error("Product Name Second Method");
            $this->name = "NA";
        }


    }
    public function get_image(){

        try {
            $this->image = $this->document->getElementById("landingImage")->getAttribute("data-old-hires");
        }
        catch ( Error | Exception $e) {
            $this->log_error("The Image");
            $this->image = "";
        }

    }
    public function get_price(){
        //method 1 to return the price of the product
        try {
            $this->price=  (float) Str::replace(get_currencies($this->current_record->currency_id) , "" ,$this->center_column->xpath("(//span[contains(@class, 'apexPriceToPay')])[1]")[0]->span->__toString());

            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("First Method Price");
        }

        //method 2 to return the price of the product
        try {
            $whole=Str::remove([",","\u{A0}"] ,
                $this->center_column
                    ->xpath("//div[@id='corePriceDisplay_desktop_feature_div']//span[@class='a-price-whole']")[0]
                    ->__toString());

            $fraction=Str::remove([",","\u{A0}"] ,
                $this->center_column
                    ->xpath("//div[@id='corePriceDisplay_desktop_feature_div']//span[@class='a-price-fraction']")[0]
                    ->__toString());

            $this->price=  (float)"$whole.$fraction";
            return;
        }
        catch (Error | \Exception $e )
        {
            $this->log_error( "Price Second");
            $this->price=0;
        }

    }
    public function get_stock(){
        try {
            $availability_string=Str::squish($this->document->getElementById("availability")->textContent) ;
            if (Str::contains($availability_string , "in stock" , true) && Str::length($availability_string) <10){
                $this->in_stock=true;
                return;
            }

            $this->in_stock=false;
        }catch (\Exception $e){
            $this->log_error( "Stock");
            $this->in_stock=true;
        }
    }
    public function get_no_of_rates(){
        try {
            $ratings=$this->center_column->xpath("//span[@id='acrCustomerReviewText']")[0]->__toString();
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
            //check if the store is amazon poland or not
            ($this->current_record->domain == "amazon.pl") ? $exploding='z' : $exploding='out';

            $this->rating= explode(" $exploding" ,
                $this->center_column->xpath("//div[@id='averageCustomerReviews']//span[@id='acrPopover']//span[@class='a-icon-alt']")[0]->__toString() ,
                2)[0];
        }
        catch (Error | Exception $e )
        {
            $this->log_error("The Rate");
            $this->rating= -1;
        }

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
            $this->log_error("The Seller First Method" );
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
            $this->log_error("The Seller Second method" );
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
            $this->log_error("The Seller Third Method" );
            $this->seller="";
        }
    }
    public function get_shipping_price(){
        try {
            $shipping_price=$this->right_column->xpath("//div[@id='deliveryBlockMessage']//span[@data-csa-c-delivery-price]")[0]->__toString();
            $shipping_price= Str::replace("," , "." , $shipping_price);
            $this->shipping_price= (float) get_numbers_only_with_dot($shipping_price);
        }
        catch (Error  | Exception $e)
        {
            $this->log_error("Shipping Price");
            $this->shipping_price= 0;
        }
    }

    /**
     *  implementation functions for crawler for notification decision
     */


    public static function get_variations($url) : array
    {
        $response=self::get_website($url);
        self::prepare_dom($response ,$document ,$xml);
        try {
            $array_script = $document->getElementById("twister_feature_div")->getElementsByTagName("script");
            $array_script=$array_script->item($array_script->count()-1)->nodeValue;
            $array_script=explode('"dimensionValuesDisplayData"' ,$array_script)[1];
            $array_script=explode("\n" ,$array_script)[0];
            $final_string=preg_replace('/\s+[\{\}\:]/', '', $array_script);
            $array_of_keys_values=explode("]," , $final_string);

            foreach ($array_of_keys_values as $single)
            {
                $key_value=explode(":[", Str::replace(['"' , ']},'], " " , $single));
                $options[Str::replace(" ", "" , $key_value[0])]= $key_value[1];
            }
            return $options ?? [];

        } catch (\Exception $e){
            error("couldn't get the variation");

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
                    ['asin'=>$single_variation],
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


    public static function validate_fnac_url(URLHelper $url)
    {
        try {
            $url->get_fnac_key();
            return true;
        }
        catch (\Exception) {
            Notification::make()
                ->danger()
                ->title("Unrecognized URL scheme")
                ->body("
                    it should be like the following:<br>
                    <span style='color:green'> https://$url->domain/product name that doesn't count/unique_code</span>
                    <br>or<br>
                    <span style='color: green'> https://$url->domain/unique_code</span>")
                ->persistent()
                ->send();
            return false;
        }

    }

    public static function is_product_unique(URLHelper $url  ,$record_id=null)
    {

        $products_with_the_same_fnac_id=ProductStore::where('key' , $url->get_fnac_key());

        if ($record_id)
            $products_with_the_same_fnac_id->whereNot('product_id' , $record_id);

        $product_store=$products_with_the_same_fnac_id->first();

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
        return self::validate_fnac_url($url) && self::is_product_unique($url);
    }
    public function get_condition()
    {
        // TODO: Implement get_condition() method.
    }


    public static function prepare_url($domain, $product, ?Store $store = null): string
    {

        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }
}
