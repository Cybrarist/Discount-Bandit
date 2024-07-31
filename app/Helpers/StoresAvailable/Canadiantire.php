<?php

namespace App\Helpers\StoresAvailable;

use App\Models\Store;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class Canadiantire extends StoreTemplate
{
    const MAIN_URL="https://store/en/pdp/product_id.html" ;
    const API_URL="https://apim.canadiantire.ca/v1/product/api/v1/product/productFamily/";
    private $schema_script;

    private $body_section;
    private $extra_request;
    public function __construct( int $product_store_id) {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void {
        parent::crawl_url();
    }

    public function prepare_sections_to_crawl(): void {

        try {
            $get_subscription_key=json_decode($this->xml->xpath("//body")[0]->attributes()["data-configs"]->__toString())->{"apim-subscriptionkey"};
            //request to get the new data

            $response=parent::get_website(self::API_URL .
                explode(".", $this->current_record->key)[0]
                ."?baseStoreId=CTR&lang=en_CA&storeId=144"
                ,
                extra_headers: [
                    "ocp-apim-subscription-key"=>$get_subscription_key,
                    "basesiteid"=>"CTR"
                ]
            );

            dd($response->body());

        }catch (Exception $e){
            dd($e);
            $this->log_error("Prepareing the crawl");
        }
    }


    /**
     * Get the data from the store
     */
    public function get_name(){

        try {
            $this->name =$this->xml->xpath("//h1[@class = 'nl-product__title']");

            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }

        try {

            $this->name = explode("|" ,  $this->xml->xpath("//meta[@property='og:title']")[0]->attributes()["content"]->__toString())[0];
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image Second Method");
        }

        try {

            $this->name = explode("|" ,  $this->xml->xpath("//meta[@name='twitter:title']")[0]->attributes()["content"]->__toString())[0];
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image Third Method");
        }

        try {

            $this->name = explode("|" ,trim(Str::remove(["\n", "Buy"] ,$this->document->getElementsByTagName("title")->item(0)->textContent))) [0];
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image Fourth Method");
        }

        try {
            $this->name =Str::trim( $this->xml->xpath("//h1[@class='product-name']")[0]->__toString());
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Name Fifth Method");
        }
        $this->name="NA";
    }

    public function get_image(){


        try {

            $this->image = $this->schema_script->image[0];
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image First Method");
        }

        try {

            $this->image = $this->xml->xpath("//meta[@property='og:image']")[0]->attributes()["content"]->__toString();
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image Second Method");
        }

        try {

            $this->image = $this->xml->xpath("//meta[@name='twitter:image']")[0]->attributes()["content"]->__toString();
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image Third Method");
        }

        try {

            $this->image = $this->xml->xpath("//link[@as='image']")[0]->attributes()["href"]->__toString();
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image Fourth Method");
        }


        $this->image="NA";

    }

    public function get_price(){


        try {
            $this->price=(float) $this->schema_script->offers->price;
            return ;
        } catch ( Error | \Exception  $e )
        {
            $this->log_error("Product Price First Method");
        }

        try {
            $this->price=(float)  Str::remove(["£" , "\n"] , $this->xml->xpath("//div[@class='prices-add-to-cart-actions']//span[@class='value']")[0]->__toString());
            return ;
        } catch ( Error | \Exception  $e )
        {
            $this->log_error("Product Price Second Method");
        }
        $this->price=0;
    }

    public function get_used_price(){$this->price_used=0;}

    public function get_stock(): void {


        try {
            $this->in_stock= Str::contains($this->schema_script->offers->availability,"InStock" , true);
            return;
        }catch (\Exception $e){
            $this->log_error("the stock");

        }

        $this->in_stock=true;
    }

    public function get_no_of_rates(){
        $this->no_of_rates=0;
    }

    public function get_rate(){

        //todo
        // the rating is happening after calling to ttps://api.bazaarvoice.com/data/display/0.2alpha/product/summary?PassKey=bai25xto36hkl5erybga10t99&productid=4000266347&contentType=reviews,questions&reviewDistribution=primaryRating,recommended&rev=0&contentlocale=en_CA,fr_CA,en_US
        // it's initiated by another js file that is not available in the dom, not really important to start a browser instance
        $this->rating= -1;

    }

    public function get_seller(): void {$this->seller="Curry's";}

    public function get_shipping_price(): void { $this->shipping_price=0;}


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



    public function get_condition(): void
    {

        try {
            $this->condition=Str::contains($this->schema_script->offers->itemCondition , 'NewCondition' , true);
        }catch (Exception $e){
            $this->condition="new";
        }

    }

    public static function prepare_url($domain, $product, ?Store $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }
}
