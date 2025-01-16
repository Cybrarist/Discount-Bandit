<?php

namespace App\Helpers\StoresAvailable;

use App\Models\Store;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Canadiantire extends StoreTemplate
{
    const MAIN_URL="https://store/en/pdp/pcode-product_id.html" ;
    const API_URL="https://apim.canadiantire.ca/v1/product/api/v1/product/productFamily/";
    private $schema_script;


    public function __construct( int $product_store_id) {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void {
        parent::crawl_url_chrome();
    }

    public function prepare_sections_to_crawl(): void {
        try {

            $scripts=$this->xml->xpath("//script[@type='application/ld+json']");

            foreach ($scripts as $single_script)
                if (Str::contains( $single_script->__toString(), '"@type":"Product"' , true)){
                    $this->schema_script=json_decode($single_script->__toString(), true);
                    break;
                }

        }catch (Exception $e){
            $this->log_error("Preparing the crawl", $e->getMessage());
        }
    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name =$this->schema_script["name"];
        } catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->schema_script["image"];
            return;
        } catch (Error | Exception $e){
            $this->log_error("Product Image First Method");
        }

    }

    public function get_price(): void
    {
        try {
            $this->price=(float) $this->schema_script["offers"]["price"];
            return ;
        } catch ( Error | \Exception  $e )
        {
            $this->log_error("Product Price First Method");
        }
        $this->price=0;
    }

    public function get_used_price(){$this->price_used=0;}

    public function get_stock(): void {
        try {
            $this->in_stock= ($this->schema_script["offers"]["availability"] =="InStock");
            return;
        }catch (\Exception $e){
            $this->log_error("the stock", $e->getMessage());
        }
        $this->in_stock=true;
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates= Str::remove([")","("], $this->xml->xpath("//div[@class='bv_numReviews_text']")[0]->__toString());
            return;
        }catch (\Exception $e){
            $this->log_error("the Number of rates");
        }
        $this->no_of_rates=0;
    }

    public function get_rate(){

        try {
            $this->rating= $this->xml->xpath("//*[contains(@class, 'bv_avgRating_component_container')]")[0]->__toString();
            return;
        }catch (\Exception $e){
            $this->log_error("the Rate");
        }
        $this->rating= -1;
    }

    public function get_seller(): void {$this->seller="Canadian tire";}

    public function get_shipping_price(): void { $this->shipping_price=0;}


    public static function get_variations($url) : array
    {
            Notification::make()
                ->danger()
                ->title("This Store doesn't support variation yet")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();

        return  [];
    }



    public function get_condition(): void{
        try {
            $this->condition= (Str::contains($this->schema_script['offers']['itemCondition'] , "NewCondition", true)) ? "new" : "used";
        }catch (Exception $e) {
            $this->log_error("the Rate");
        }
    }

    public static function prepare_url($domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }
    function is_system_detected_as_robot(): bool {
        return !isset($this->schema_script) && !Arr::exists($this->schema_script, "offers");
    }

}

