<?php

namespace App\Helpers\StoresAvailable;

use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Wallapop extends StoreTemplate
{
    const MAIN_URL="https://store/country-en/product_id/p/" ;

    private $schema_script;
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


            Context::add("product", $this->current_record->product);
            Log::error("Couldn't Crawl the website for the following url $this->product_url \n");
            return;
        }


        //crawl the website to get the important information
        self::crawling_process();

        //check for the notification settings
        $this->check_notification();

    }
    public function prepare_sections_to_crawl(): void
    {

        //get the center column to get the related data for it
        $scripts=$this->xml->xpath("//script[@type='application/ld+json']");

        foreach ($scripts as $single_script)
            if (Str::contains( $single_script->__toString(), '"@type":"Product"' ,))
                $this->schema_script=json_decode($single_script->__toString());

    }


    /**
     * Get the data from the store
     */
    public function get_name(){

        try {
            $this->name = $this->schema_script->name;
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }

    }
    public function get_image(){
        try {
            $this->image = $this->schema_script->image[0];
        }
        catch ( Error | Exception $e) {
            $this->log_error("The Image");
            $this->image = "";
        }
    }

    public function get_price(){
        //method 1 to return the price of the product
        try {
            $this->price=(float) $this->schema_script->offers[0]->price;
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("Price");
        }
        $this->price=0;
    }

    public function get_used_price(){

        $this->price_used=0;
        //method 1 to return the price of the product
        try {
            $prices_in_the_page=json_decode($this->right_column->xpath("//div[contains(@class,'twister-plus-buying-options-price-data')]")[0]->__toString());

            foreach ($prices_in_the_page->{'desktop_buybox_group_1'} as $single_price)
                if ($single_price->{"buyingOptionType"} == "USED")
                    $this->price_used=$single_price->{'priceAmount'};
        }
        catch ( Error | \Exception  $e )
        {
            //$this->log_error("First Method Used Price");
        }


    }
    public function get_stock(){
        try {
            $this->in_stock=Str::contains( $this->schema_script->offers[0]->availability, "InStock" ,true);

        }catch (\Exception $e){
            Log::error("couldn't get the stock" . $e);
            $this->in_stock=true;
        }
    }
    public function get_no_of_rates(){
        try {
            $this->no_of_rates= (int)$this->schema_script->aggregateRating->reviewCount;
        }
        catch (Error | Exception $e)
        {
            Log::error("couldn't get number of rates \n $e");
            $this->no_of_rates=0;
        }
    }
    public function get_rate(){
        try {
            $this->rating= $this->schema_script->aggregateRating->ratingValue;
        }
        catch (Error | Exception $e )
        {
            Log::error("couldn't get the rate \n $e");
            $this->rating= -1;
        }

    }

    public function get_seller(){

        try {
            $this->seller=$this->schema_script->offers[0]->seller->name;
            return;
        }
        catch (Error | Exception $e ) {
            $this->log_error("The Seller First Method" );
        }

    }
    public function get_shipping_price(){
        $this->shipping_price=0;
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

    public static function prepare_url($domain, $product ,  $store=null ): string
    {


        return Str::replace(
            ["store","country", "product_id"],
            [$domain ,explode(" " , $store->name)[1]  , Str::upper($product)],
            self::MAIN_URL);
    }

    public function get_condition()
    {
        try {
            $this->condition= (Str::contains($this->schema_script->offers[0]->itemCondition , "NewCondition" , true))? "new": "used" ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("Condition");
            $this->condition="new";
        }

    }

    function is_system_detected_as_robot(): bool { return false;}

    #[\Override] function crawler(): void
    {
        // TODO: Implement crawler() method.
    }
}
