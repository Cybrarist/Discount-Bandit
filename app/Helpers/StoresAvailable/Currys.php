<?php

namespace App\Helpers\StoresAvailable;

use App\Models\Store;
use Error;
use Exception;
use Illuminate\Support\Str;

class Currys extends StoreTemplate
{
    const MAIN_URL="https://store/products/product_id.html" ;
    private $schema_script;

    private $body_section;
    private $extra_request;
    public function __construct(private int $product_store_id) {
        parent::__construct($this->product_store_id);
    }

    //define crawler
    public function crawler(): void {
        parent::crawl_url_chrome();
    }

    public function prepare_sections_to_crawl():void {

        try {
            $scripts=$this->xml->xpath("//script[@type='application/ld+json']");

            foreach ($scripts as $single_script)
                if (Str::contains($single_script->__toString() . '"@type":"Product"' , true) ){
                    $this->schema_script= json_decode($single_script->__toString());
                    break ;
                }
        }catch (Exception){
            $this->log_error("Crawling Curry's");

        }

    }


    public function get_name():void {

        try {
            $this->name =Str::trim( $this->schema_script->name);
            return;
        } catch (Error | Exception ){
            $this->log_error("Product Name First Method");
        }

        try {

            $this->name = explode("|" ,  $this->xml->xpath("//meta[@property='og:title']")[0]->attributes()["content"]->__toString())[0];
            return;
        } catch (Error | Exception ){
            $this->log_error("Product Name Second Method");
        }

        try {

            $this->name = explode("|" ,  $this->xml->xpath("//meta[@name='twitter:title']")[0]->attributes()["content"]->__toString())[0];
            return;
        } catch (Error | Exception ){
            $this->log_error("Product Name Third Method");
        }

        try {
            $this->name = explode("|" ,trim(Str::remove(["\n", "Buy"] ,$this->document->getElementsByTagName("title")->item(0)->textContent))) [0];
            return;
        } catch (Error | Exception ){
            $this->log_error("Product Name Fourth Method");
        }

        try {
            $this->name =Str::trim( $this->xml->xpath("//h1[@class='product-name']")[0]->__toString());
            return;
        } catch (Error | Exception ){
            $this->log_error("Product Name Fifth Method");
        }
    }

    public function get_image():void {
        try {
            $this->image = $this->schema_script->image[0];
            return;
        } catch (Error | Exception){
            $this->log_error("Product Image First Method");
        }

        try {
            $this->image = $this->xml->xpath("//meta[@property='og:image']")[0]->attributes()["content"]->__toString();
            return;
        } catch (Error | Exception){
            $this->log_error("Product Image Second Method");
        }

        try {
            $this->image = $this->xml->xpath("//meta[@name='twitter:image']")[0]->attributes()["content"]->__toString();
            return;
        } catch (Error | Exception){
            $this->log_error("Product Image Third Method");
        }

        try {
            $this->image = $this->xml->xpath("//link[@as='image']")[0]->attributes()["href"]->__toString();
            return;
        } catch (Error | Exception){
            $this->log_error("Product Image Fourth Method");
        }
    }

    public function get_price():void {
        try {
            $this->price=(float) $this->schema_script->offers->price;
            return ;
        } catch ( Error | \Exception)
        {
            $this->log_error("Product Price First Method");
        }

        try {
            $this->price=(float)  Str::remove(["£" , "\n"] , $this->xml->xpath("//div[@class='prices-add-to-cart-actions']//span[@class='value']")[0]->__toString());
            return ;
        } catch ( Error | \Exception)
        {
            $this->log_error("Product Price Second Method");
        }
    }

    public function get_used_price():void {$this->price_used=0;}

    public function get_stock(): void {
        try {
            $this->in_stock= Str::contains($this->schema_script->offers->availability,"InStock" , true);
            return;
        }catch (Exception){
            $this->log_error("the stock");
        }
    }

    public function get_no_of_rates():void {$this->no_of_rates=0;}

    public function get_rate():void {
        //todo
        // the rating is happening after calling to ttps://api.bazaarvoice.com/data/display/0.2alpha/product/summary?PassKey=bai25xto36hkl5erybga10t99&productid=4000266347&contentType=reviews,questions&reviewDistribution=primaryRating,recommended&rev=0&contentlocale=en_CA,fr_CA,en_US
        // it's initiated by another js file that is not available in the dom, not really important to start a browser instance
        $this->rating= -1;

    }

    public function get_seller(): void {$this->seller="Curry's";}

    public function get_shipping_price(): void { $this->shipping_price=0;}

    public function get_condition(): void {
        try {
            $this->condition=Str::contains($this->schema_script->offers->itemCondition , 'NewCondition' , true);
        }catch (Exception $e){
            $this->log_error("Condition");
        }
    }

    /*
     * Variants with curry's are annoying, it doesn't provide the ID of variants
     * instead, it gives a tracking link that redirects to the other variant.
     *
     * this won't work, because the software will need to do x number of requests for the selected
     * variants, then get the id
     */

    public static function get_variations($url) : array
    {
        return  [];
    }


    public static function prepare_url($domain, $product , ?Store $store=null ): string
    {

        return Str::replace(["store", "product_id"], [$domain , Str::upper($product)], self::MAIN_URL);


    }

    function is_system_detected_as_robot(): bool { return false;}

}
