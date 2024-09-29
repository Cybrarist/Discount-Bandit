<?php

namespace App\Helpers\StoresAvailable;

use DOMDocument;
use Error;
use Exception;
use Illuminate\Support\Str;

class Bestbuy extends StoreTemplate
{
    const string MAIN_URL="https://www.bestbuy.com/pricing/v1/price/item?allFinanceOffers=true&catalog=bby&context=offer-list&effectivePlanPaidMemberType=NULL&includeOpenboxPrice=true&paidMemberSkuInCart=false&salesChannel=LargeView&skuId=4901809&useCabo=true&usePriceWithCart=true&visitorId=7e3432cd-6f63-11ef-97ca-12662d3c815b" ;
    const string MAIN_URL_NAME_AND_IMAGE="https://www.store/site/product_id.p?skuId=product_id&intl=nosplash" ;
    const string CANADA_URL="https://www.store/api/offers/v1/products/product_id/offers" ;
    const string CANADA_URL_NAME_AND_IMAGE="https://www.store/en-ca/product/product_id" ;
    private bool $is_canada;
    private $json_schema;
    private $main_body;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void
    {
        $this->is_canada=$this->current_record->store->domain=="bestbuy.ca";;
        ($this->is_canada) ?  parent::crawl_url() : parent::crawl_url_chrome(extra_headers:['X-CLIENT-ID'=>'lib-price-browser']);
    }

    public function prepare_sections_to_crawl(): void
    {
        try {
            $this->json_schema=json_decode(($this->is_canada) ? $this->document->textContent  : $this->xml->xpath("//pre")[0]->__toString());
        }catch (Error | Exception $exception) {
            $this->log_error("Crawling BestBuy", $exception->getMessage());
        }

    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {

        try {
            $this->get_name_and_image();
            $this->name = $this->main_body->xpath("//title")[0]->__toString();
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name First Method", $exception->getMessage());
        }

    }

    public function get_image(): void
    {
        try {
            $this->get_name_and_image();
            $this->image = ($this->is_canada) ? $this->main_body->xpath("//link[@as='image']")[0]->attributes()["href"]->__toString()  :$this->main_body->xpath("//meta[@property='og:image']")[0]->attributes()["content"]->__toString();
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {
            $this->price=  (float)  ($this->is_canada) ? $this->json_schema[0]->salePrice  : $this->json_schema->currentPrice;
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }
    }

    public function get_used_price(): void
    {
        if($this->is_canada) return;
        //method 1 to return the price of the product
        try {
            $this->price_used=(float) $this->json_schema->lowestOpenboxPrice;
        }
        catch ( Error | Exception  $exception )
        {
            $this->log_error("First Method Used Price",$exception->getMessage());
        }
    }

    public function get_stock(): void {}

    public function get_no_of_rates(): void {}

    public function get_rate(): void {}

    public function get_seller(): void
    {
        $this->seller="Best Buy";
    }

    public function get_shipping_price(): void {}

    // TODO: Implement get_condition() method.
    public function get_condition() {}



    public static function get_variations($url) : array {dump("not supported yet");}


    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            ($domain=="bestbuy.com") ? self::MAIN_URL : self::CANADA_URL );
    }

    private function get_name_and_image()
    {
        if (!$this->main_body) {
            $name_and_image_url=Str::replace(
                ["store", "product_id"],
                [$this->current_record->store->domain , $this->current_record->key],
                ($this->current_record->store->domain=="bestbuy.com") ? self::MAIN_URL_NAME_AND_IMAGE : self::CANADA_URL_NAME_AND_IMAGE );


            if ($this->is_canada)
                $response=parent::get_website($name_and_image_url);
            else
                $response= parent::get_website_chrome($name_and_image_url , extra_headers:['X-CLIENT-ID'=>'lib-price-browser']);

            $document=new DOMDocument();
            libxml_use_internal_errors(true);
            $document->loadHTML($response);
            $this->main_body=  simplexml_import_dom($document);


        }

    }
    function is_system_detected_as_robot(): bool { return false;}


}
