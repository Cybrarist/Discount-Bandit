<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use App\Models\Currency;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class Mediamarkt extends StoreTemplate
{
    const string MAIN_URL="https://www.store/lang/product/-product_id.html" ;
    private  $schema_script;
    private  $rating_response;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void
    {
        parent::crawl_url_chrome();
    }

    public function prepare_sections_to_crawl(): void
    {
        try {
            $scripts=$this->xml->xpath("//script[@type='application/ld+json']");

            foreach ($scripts as $single_script)
                if (Str::contains($single_script->__toString() . '"@type":"BuyAction"' , true) ){
                    $this->schema_script= json_decode($single_script->__toString());
                    break ;
                }
        }catch (Exception){
            $this->log_error("Crawling MediaMarkt Spain");
        }


    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {

        try {
            $this->name = $this->schema_script->object->name ;
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->schema_script->object->image;
        }
        catch ( Error | Exception ) {
            $this->log_error("Product Image First Method");
        }

    }

    public function get_price(): void
    {
        try {
            $this->price=  (float) $this->schema_script->object->offers[0]->price;
            return ;
        }
        catch ( Error | \Exception  $e ) {
            $this->log_error("Price First Method");
        }
    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {

            $this->in_stock = Str::contains($this->schema_script->object->offers[0]->availability , "instock" , true);

        }catch (\Exception $e){
            $this->log_error( "Stock Availability First Method");
        }
    }

    //todo check another request using graphql
    public function get_no_of_rates(): void {}

    public function get_rate(): void{}

    public function get_seller(): void
    {

        try {
            $this->seller=$this->schema_script->object->offers[0]->seller->name;
            throw_if(!$this->seller , new Exception());
            return;
        }
        catch (Error | Exception $e ) {
            $this->log_error("The Seller First Method" );
        }
    }

    public function get_shipping_price(): void {}

    public function get_condition() {}



    public static function get_variations($url) : array {return [];}


    public static function prepare_url( $domain, $product, $store = null): string
    {
        //doing this before making sure other domains for the same store do the same thing.
        $language= match ($domain){
            default=> explode("." , $domain)[1],
        };

        return Str::replace(
            ["store", "product_id" , "lang"],
            [$domain , $product , $language],
            self::MAIN_URL);
    }
    function is_system_detected_as_robot(): bool { return false;}


}
