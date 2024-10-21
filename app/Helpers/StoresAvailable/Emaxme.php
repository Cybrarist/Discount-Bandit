<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use App\Models\Currency;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class Emaxme extends StoreTemplate
{
    const string MAIN_URL="https://uae.store/product_id.html" ;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void
    {
        parent::crawl_url_chrome();
    }

    public function prepare_sections_to_crawl(): void {}

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {

        try {
            $this->name = $this->xml->xpath("//h1[contains(@class,'MuiTypography-body1')]")[0]->__toString();
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name Third Method", $exception->getMessage());
        }

        //these will get the base model title, since they don't change the metas when products switch.
        try {
            $this->name = $this->document->getElementsByTagName('title')->item(0)->textContent;
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name First Method", $exception->getMessage());
        }
        try {
            $this->name = $this->xml->xpath("//meta[@name='title']")[0]->attributes()['content']->__toString();
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }

    }

    public function get_image(): void
    {
        try {
            $this->image =Str::before($this->xml->xpath("//div[contains(@class,'slick-active')]//img")[0]->attributes()['src']->__toString() ,"?");

            if (!Str::endsWith($this->image, ".png"))
                $this->image =  $this->image . ".png";

            return;
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

        try {
            $this->image =  $this->xml->xpath("//meta[@property='og:image']")[0]->attributes()['content']->__toString();

            if (!Str::endsWith($this->image, ".png"))
                $this->image =  $this->image . ".png";

        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {
            $this->price = (float) Str::remove(["aed" ,","], $this->document->getElementById('details-price')->textContent, false);
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }

        try {
            $this->price = (float)  $this->xml->xpath("//meta[@property='product:price:amount']")[0]->attributes()['content']->__toString();

            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price Second Method",$exception->getMessage());
        }

    }

    // no used price for Emax
    public function get_used_price(): void {}

    public function get_stock(): void {}

    public function get_no_of_rates(): void {}

    //no product found with rating to check the field.
    public function get_rate(): void {}

    public function get_seller(): void
    {
         $this->seller="Emax";
    }

    public function get_shipping_price(): void {}

    public function get_condition() {}

    //emax doesn't generate variation links, instead one link with variables implemented in links
    //such as color, size, storage etc.
    public static function get_variations($url) : array
    {
        return  [];
    }


    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }


    // didn't see robot message yet, will populate once encountered.
    function is_system_detected_as_robot(): bool { return false;}

}
