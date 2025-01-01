<?php

namespace App\Helpers\StoresAvailable;

use App\Models\Store;
use Error;
use Exception;
use Illuminate\Support\Str;

class Costco extends StoreTemplate
{
    const string MAIN_URL="https://store/.product.product_id.html" ;
    const string SECONDARY_URL="https://store/p/product_id" ;

    private $product_raw_data;
    private $schema_script;
    private $extra_request;

    public function __construct(int $product_store_id) {
        parent::__construct($product_store_id);
    }

    public function crawler(): void { $this->crawl_url();}

    public function prepare_sections_to_crawl(): void
    {
        try {
            //get list of scripts to extract the data needed.
            $scripts=$this->xml->xpath("//script[@type='text/javascript']");

            foreach ($scripts as $single_script)

                if (Str::contains( $single_script->__toString(), "adobeProductData" , true)){
                    //get the strings that we want and close the bracket
                    $temp_wanted_strings=Str::replace(["\n" , "\t" ,"\"" ,"initialize("] ,"" ,Str::between($single_script ,"[" ,"product:" )  ). "}";


                    //clean up the strings
                    $temp_cleaned= Str::replace([")," ,",}","{" ,",",":"],[",","\"}","{\"","\",\"","\":\""] ,$temp_wanted_strings);

                    $this->product_raw_data = $temp_cleaned;

                    //replace and adds double quotes
                    $this->schema_script=json_decode(Str::remove(["'","\\"],  $temp_cleaned));
                }
                elseif ( Str::contains( $single_script->__toString(), "AjaxGetInventoryStatusUpdate" )){
                    $temp_wanted_strings=Str::replace(["\n" , "\t" ] ,"" ,$single_script->__toString() );
                    $this->extra_request = json_decode( "{\"productId\"" . Str::between($temp_wanted_strings, "{\"productId\"" , ",\"defaultShipCode\"") ."}");

                }


            if (!$this->schema_script){

                $scripts=$this->xml->xpath("//script[@type='application/ld+json']");

                foreach ($scripts as $single_script)
                    if ( $single_script->attributes()['id'] == "schemaorg_product" ){
                        $this->schema_script= json_decode($this->document->getElementById("schemaorg_product")->textContent);
                    }
            }

        }catch (Error | Exception ){
            $this->log_error("Crawling Costco");
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void {
        try {
            $this->name =Str::trim( $this->schema_script->name);
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }
        try {
            $this->name =$this->document->getElementsByTagName("title")->item(0)->textContent;
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name Second Method");
        }
        try {
            $this->name = explode("\n" , $this->xml->xpath("//meta[@name='description']")[0]->attributes()["content"]->__toString())[0];
            return;

        }catch (Error | Exception $e){
            $this->log_error("Product Name Third Method");
        }
        try {
            $this->name = explode("\n" , $this->xml->xpath("//meta[@property='og:title']")[0]->attributes()["content"]->__toString())[0];
            return;
        }catch (Error | Exception $e){
            $this->log_error("Product Name Fourth Method");
        }
        try {
            $this->name = explode("\n" , $this->xml->xpath("//meta[@property='og:description']")[0]->attributes()["content"]->__toString())[0];
            return;
        }catch (Error | Exception $e){
            $this->log_error("Product Name Fifth Method");
        }
        try {
            $this->name = $this->schema_script->name;
            return;
        }catch (Error | Exception $e){
            $this->log_error("Product Name Sixth Method");
        }
    }

    public function get_image(): void {
        try {

            $this->image = $this->document->getElementById("initialProductImage")->getAttribute("src");
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }

        try {
            $this->image = explode("\n" , $this->xml->xpath("//meta[@property='og:image']")[0]->attributes()["content"]->__toString())[0];
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name Second Method");
        }

        try {
            $this->image = explode("\n" , $this->xml->xpath("//meta[@name='twitter:image']")[0]->attributes()["content"]->__toString())[0];
            return;

        }catch (Error | Exception $e){
            $this->log_error("Product Name Third Method");
        }

        try {
            $this->image = $this->schema_script->image;
            return;
        }catch (Error | Exception $e){
            $this->log_error("Product Name Fourth Method");
        }

    }

    public function get_price(): void {

        try {
            $this->price=(float) $this->schema_script->priceTotal;
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("Price First Method");
        }

        try {
            $this->price=(float) $this->schema_script->offers->price;
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("Price Second Method");
        }
        try {
            $this->price= Str::remove('"}' ,  explode('priceTotal":"' , $this->product_raw_data)[1]);

        }catch (Exception ){
            $this->log_error("Price Third Method");
        }
    }

    public function get_used_price(): void { $this->price_used=0; }

    public function get_stock(): void {

        try {
            $this->in_stock= Str::contains($this->schema_script->offers->availability,"InStock" , true);
            return;
        } catch (Exception){
            $this->log_error("Stock Availability First Method");

        }

        try {
            $response=self::get_website("https://www." . $this->current_record->store->domain . "/AjaxGetInventoryStatusUpdate", [
                "productId"=>$this->extra_request->productId,
                "storeId"=>$this->extra_request->storeId,
                "warehouse"=>"1-wh",
                "inWarehouse"=>"true",
            ])->json();

            $this->in_stock= $response["inventoryStatusForNearestWarehouse"] =="IN_STOCK";
            return;
        }catch (Exception ){
            $this->log_error("Stock Availability Second Method");

        }
    }

    public function get_no_of_rates(): void { $this->no_of_rates=0;}

    public function get_rate(): void {
        //todo
        // the rating is happening after calling to ttps://api.bazaarvoice.com/data/display/0.2alpha/product/summary?PassKey=bai25xto36hkl5erybga10t99&productid=4000266347&contentType=reviews,questions&reviewDistribution=primaryRating,recommended&rev=0&contentlocale=en_CA,fr_CA,en_US
        // it's initiated by another js file that is not available in the dom, not really important to start a browser instance
    }

    public function get_seller(): void {$this->seller="costco";}

    public function get_shipping_price(): void {
        try {
            $this->shipping_price=$this->schema_script->offers->shippingDetails->shippingRate->value;

        }catch (Exception){
            $this->log_error("Shipping Price First Method");
        }
    }

    public function get_condition(): void {
        try {
            $this->condition=Str::contains($this->schema_script->offers->itemCondition , 'NewCondition' , true);
        }catch (Exception){
            $this->log_error("Condition First Method");
        }
    }

    /**
     *  implementation functions for crawler for notification decision
     */

    public static function get_variations($url) : array { return []; }


    public static function prepare_url($domain, $product , $store =null ): string
    {
        return match($domain){
            "costco.com","costco.ca"=>Str::replace(["store", "product_id"], [$domain , Str::upper($product)], self::MAIN_URL),
            "costco.com.mx", "costco.co.uk" , "costco.co.kr" , "costco.com.tw","costco.co.jp","costco.com.au","costco.is"=>Str::replace(["store", "product_id"], [$domain , Str::upper($product)], self::SECONDARY_URL),
        };

    }

    function is_system_detected_as_robot(): bool { return false;}


}
