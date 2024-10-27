<?php

namespace App\Helpers\StoresAvailable;

use App\Models\Store;
use Error;
use Exception;
use Illuminate\Support\Str;

class Tatacliq extends StoreTemplate
{
    const string MAIN_URL="https://store/p-mpproduct_id" ;
    const string API_URL="https://www.store/marketplacewebservices/v2/mpl/products/productDetails/mpproduct_id?isPwa=true&isMDE=true&isDynamicVar=true" ;

    private $schema_script;

    public function __construct(int $product_store_id) {
        parent::__construct($product_store_id);
    }

    public function crawler(): void { $this->crawl_url_chrome();}

    public function prepare_sections_to_crawl(): void
    {
        try {
            $this->schema_script=json_decode($this->document->textContent, true);
        }catch (Error | Exception ){
            $this->log_error("Crawling");
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void {
        try {
            $this->name = $this->schema_script["productName"];
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }
        try {
            $this->name = $this->schema_script["productTitle"];
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name Second Method");
        }
    }

    public function get_image(): void {
        try {
            $this->image ="https:" . $this->schema_script["galleryImagesList"][0]["galleryImages"][0]["value"];
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }

    }

    public function get_price(): void {
        try {
            $this->price=(float) $this->schema_script["winningSellerPrice"]['value'];
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("Price First Method");
        }
    }

    public function get_used_price(): void { $this->price_used=0; }

    public function get_stock(): void {}

    public function get_no_of_rates(): void {

        try {
            $this->no_of_rates= (int) $this->schema_script["ratingCount"];
        }
        catch (Error | Exception $exception)
        {
            $this->log_error("No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void {
        try {
            $this->rating= $this->schema_script["averageRating"];
        }
        catch (Error | Exception $exception )
        {
            $this->log_error("The Rate", $exception->getMessage());
        }
    }

    public function get_seller(): void {
        try {
            $this->seller=$this->schema_script["winningSellerName"];
            return;
        }
        catch (Error | Exception $exception ) {
            $this->log_error("The Seller First Method", $exception->getMessage() );
        }
    }

    public function get_shipping_price(): void {}

    public function get_condition(): void {
        try {
            $this->condition=$this->schema_script['isProductNew'] == 'N';
        }catch (Exception){
            $this->log_error("Condition First Method");
        }
    }

    /**
     *  implementation functions for crawler for notification decision
     */

    public static function get_variations($url) : array { return []; }


    public static function prepare_url($domain, $product , ?Store $store =null ): string
    {
        /*
         * check the trace, and if it's called from the store relation manager
         * then show the url where the user can access
        */
        $which_class_called_the_function=debug_backtrace()[1]['function'];

        if (Str::contains($which_class_called_the_function, ["notify","call_user_fun"]))
            return Str::replace(
                ["store", "product_id"],
                [$domain , $product],
                self::MAIN_URL );
        else
            return Str::replace(
                ["store", "product_id"],
                [$domain , $product],
                self::API_URL );

    }

    function is_system_detected_as_robot(): bool { return false;}


}
