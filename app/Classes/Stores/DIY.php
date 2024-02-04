<?php

namespace App\Classes\Stores;

use App\Classes\MainStore;
use App\Classes\URLHelper;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function App\Classes\error;

class DIY extends MainStore
{
    const MAIN_URL="https://store/departments/product_id_BQ.prd?referral_code" ;
    private  $left_column;
    private $right_column;
    private $json_data;



    public function __construct($product_store_id) {

        $this->current_record= ProductStore::with([
            "product",
            "store"
        ])->find($product_store_id);
        $this->product_url= parent::prepare_url(
            domain: $this->current_record->store->domain,
            product: $this->current_record->key,
            store_url_template: self::MAIN_URL,
        );

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

    }
    public function prepare_sections_to_crawl(){

        //get the right column to get the seller and other data
        $this->right_column=$this->xml->xpath("//div[@id='product-availability']")[0];
        //get the left column for the images
        $this->left_column=$this->xml->xpath("//div[@class='slick-list']")[0];

        $this->json_data=json_decode($this->xml->xpath("//script[@data-test-id='product-page-structured-data']")[0]->__toString() , true)["mainEntity"];

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



        $this->current_record->update(            [
            'price' => $this->price,
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
            $this->name = $this->json_data["name"];
            return;
        }
        catch (Error | Exception $e){
            $this->throw_error("Product Name First Method");
        }

        try {
            $this->name = Str::squish(explode("|" ,$this->document->getElementsByTagName("title")->item(0)->textContent)[0]);
            return;
        }
        catch (Error | Exception $e){
            $this->throw_error("Product Name Second Method");
        }

        try {
            $this->name = $this->right_column->xpath("//h1[@id='product-title']")[0];
            return;
        }
        catch ( Error | Exception $e) {
            $this->throw_error("Product Name Third Method");
        }

        try {
            $this->name = $this->document->getElementById("product-title")->textContent;
        }
        catch ( Error | Exception $e) {
            $this->throw_error("Product Name Fourth Method");
            $this->name = "NA";
        }


    }
    public function get_image(){
        try {
            $this->image = explode("?" , $this->json_data["image"])[0];
            return;
        }
        catch ( Error | Exception $e) {
            $this->throw_error("The Image First Method");
        }
        try {
            $this->image = explode("?" ,$this->left_column->xpath("//div[@data-test-id='PrimaryImage']//img")[0]->attributes()->src->__toString() )[0];
        }
        catch ( Error | Exception $e) {
            $this->throw_error("The Image Second Method");
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
            $this->throw_error("First Method Price");
        }
        //method 2 to return the price of the product
        try {
            $this->price= (float) $this->right_column->xpath("//div[@data-test-id='product-primary-price']//div")[0]->__toString();
            return;
        }
        catch (Error | \Exception $e )
        {
            $this->throw_error( "Price Second");
            $this->price=0;
        }

    }
    public function get_stock(){
        try {
            $this->in_stock=Str::contains($this->json_data["offers"]["availability"] , "instock", true);
            return;
        }catch (\Exception $e){
            $this->throw_error( "Stock Availability First Method");
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
            $this->throw_error("No. Of Rates");
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
            $this->throw_error("The Rate");
            $this->rating= -1;
        }

    }


    public function get_seller(){

        try {
            $this->seller="B&Q";
            return;
        }
        catch (Error | Exception $e )
        {
            $this->throw_error("The Seller First Method" );
        }

    }
    public function get_shipping_price(){
        try {
            $this->shipping_price=0;
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

    //static functions to be called anywhere
    public static function validate_url(URLHelper $url)
    {
        try {
            $url->get_diy_id();
            return true;
        }
        catch (\Exception) {
            Notification::make()
                ->danger()
                ->title("Unrecognized URL scheme")
                ->body("
                    it should be like the following:<br>
                    <span style='color:green'> https://$url->domain/departments/unique_code</span>")
                ->persistent()
                ->send();
            return false;
        }

    }

    public static function is_product_unique(URLHelper $url  ,$record_id=null)
    {
        $products_with_same_id=ProductStore::where([
            "key" =>  $url->get_diy_id(),
            "store_id" => 28
        ])->first();

        if (!$products_with_same_id)
            return true;
        else{

            $product_url=route("filament.admin.resources.products.edit" , $products_with_same_id->product_id) ;

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
        return self::validate_url($url) && self::is_product_unique($url);
    }
    public function get_condition()
    {
        // TODO: Implement get_condition() method.
    }
}
