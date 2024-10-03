<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use Error;
use Exception;
use Illuminate\Support\Str;

class Target extends StoreTemplate
{

    const string MAIN_URL="https://www.store/p/-/product_id" ;
    private  $center_column;
    private $script_information;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void
    {
        parent::crawl_url();
    }

    public function prepare_sections_to_crawl(): void
    {
        try {

            $temp= substr('[{"price'. explode("secondary_averages", explode("current_retail" , $this->document->textContent)[1])[0] , 0,-3)."}}}}]";
            $this->script_information=json_decode (Str::replace(['\"' , '"ratings_and_reviews"'] , ['"' , '{"ratings_and_reviews"'] ,$temp   ), true);
        }catch (Error | Exception $exception) {
            $this->log_error("Crawling Amazon", $exception->getMessage());
        }

    }
    /**
     * Get the data from the store
     */
    public function get_name(): void
    {

        try {
            $this->name = explode(":" ,$this->document->getElementsByTagName("title")->item(0)->textContent)[0];
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name First Method", $exception->getMessage());
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->xml->xpath("//meta[@property='og:image']")[0]->attributes()["content"]->__toString() ;
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {


        try {
            $this->price=  (float) $this->script_information[0]["price"];
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }
        try {

            $values=explode('{\"__typename\":\"Product\"', $this->document->textContent );

            $wanted="";
            foreach ($values as $value)
                if (Str::startsWith($value , ',\"tcin\":\"' . Str::remove("A-",$this->current_record->key) .'\"'))
                    $wanted=$value;

            $this->price=  (float) explode("," , explode('\"current_retail\":', $wanted)[1])[0];
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price second Method",$exception->getMessage());
        }

    }

    public function get_used_price(): void {}

    public function get_stock(): void {}

    public function get_no_of_rates(): void
    {
        try {
            $ratings=$this->script_information[1]["ratings_and_reviews"]["statistics"]["rating"]["count"];
            $this->no_of_rates= (int) GeneralHelper::get_numbers_only_with_dot($ratings);
        }
        catch (Error | Exception $exception)
        {
            $this->log_error("No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            $this->rating=$this->script_information[1]["ratings_and_reviews"]["statistics"]["rating"]["average"];

        }
        catch (Error | Exception $exception )
        {
            $this->log_error("The Rate", $exception->getMessage());
        }

    }

    public function get_seller(): void { $this->seller="target";}

    public function get_shipping_price(): void {}

    public function get_condition() {}

    public static function get_variations($url) : array { return  []; }

    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }


    function is_system_detected_as_robot(): bool
    {
        return sizeof($this->xml->xpath('//input[@id="captchacharacters"]'));
    }
}
