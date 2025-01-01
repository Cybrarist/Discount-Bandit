<?php

namespace App\Helpers\StoresAvailable;

use App\Helpers\GeneralHelper;
use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class Walmart extends StoreTemplate
{
    const string MAIN_URL="https://www.store/ip/product_id";

    const string API_URL="https://www.store/orchestra/pdp/graphql/ItemByIdBtf/5ee7e3830d84d432276cdec384e8a3c65f926886f1667a4ff49364e453c9b200/ip/product_id";

    private $information;

    public function __construct(private int $product_store_id) {
        parent::__construct($this->product_store_id);
    }

    public function crawler(): void
    {
        parent::crawl_url_chrome(
//            extra_headers: [
//                "Content-Type"=> "application/json",
//                "Accept"=> "application/json",
//                "Accept-Language"=> "en-US",
//                "Accept-Encoding"=> "gzip, deflate, br, zstd",
//                "x-o-segment"=> "oaoh",
//                "x-o-platform-version"=> "us-web-1.171.4-22e8dcfe70ac4c2a97ebec1ee1cf1bdff5c8ff5d-112018",
//                "x-o-gql-query"=> "query ItemByIdBtf",
//                "X-APOLLO-OPERATION-NAME"=> "ItemByIdBtf",
//                "baggage"=> "trafficType=customer,deviceType=desktop,renderScope=SSR,webRequestSource=Browser,pageName=itemPage,isomorphicSessionId=-NbuNCXPPg5FO_So7UqMY",
//                "x-o-platform"=> "rweb"
//            ]
        );
    }


    public function prepare_sections_to_crawl(): void
    {
        try {
            $product_info= $this->xml->xpath("//script[@type='application/ld+json']")[0];
            $this->information=json_decode($product_info);
        }catch (Exception $exception){
            $this->log_error("Crawling Walmart" );
        }

    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name=$this->information->name;
            return;
        }
        catch (Error | Exception $e){
            $this->log_error("Product Name First Method");
        }

        try {
            $this->name = $this->document->getElementById("main-title")->textContent;
        }
        catch ( Error | Exception $e) {
            $this->log_error("Product Name Second Method");
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->information->image;
        }
        catch ( Error | Exception ) {
            $this->log_error("Product Image First Method");
        }

    }

    public function get_price(): void
    {
        try {
            $this->price=  $this->information->offers->price;
            return ;
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("Price First Method");
        }
        try {
            $this->price = GeneralHelper::get_numbers_only_with_dot($this->xml->xpath("//span[@itemprop='price']")[0]->__toString());
        }
        catch ( Error | \Exception  $e )
        {
            $this->log_error("Price Second Method");
        }
    }

    public function get_used_price(): void {}

    public function get_stock(): void
    {
        try {
            $this->in_stock =  (Str::contains($this->information->offers->availability , "instock" , true));
        }catch (\Exception ){
            $this->log_error( "Stock Availability First Method");
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $this->no_of_rates=$this->information->aggregateRating->reviewCount;
            return;
        }
        catch (Error | Exception )
        {
            $this->log_error(" First Method No. Of Rates");
        }
        try {
            $this->no_of_rates=(int) $this->xml->xpath("//a[@itemprop='ratingCount']")[0]->__toString();
        }
        catch (Error | Exception )
        {
            $this->log_error(" Second Method No. Of Rates");
        }
    }

    public function get_rate(): void
    {
        try {
            $this->rating=$this->information->aggregateRating->ratingValue;
            return;
        }
        catch (Error | Exception $e )
        {
            $this->log_error("The Rate First Method");
        }
        try {
            $this->rating=Str::remove(['(',')'] , $this->xml->xpath("//span[contains(@class , 'rating-number')]")[0]->__toString());
        }
        catch (Error | Exception $e )
        {
            $this->log_error("The Rate Second Method");
        }

    }

    public function get_seller(): void
    {

        try {
            $this->seller=$this
                ->right_column
                ->xpath("//div[@id='merchantInfoFeature_feature_div']//div[@class='offer-display-feature-text']//span")[0]
                ->__toString();
            return;
        }
        catch (Error | Exception $e )
        {
            $this->log_error("The Seller First Method" );
        }

        try {
            $this->seller=$this
                ->right_column
                ->xpath("//div[@id='merchantInfoFeature_feature_div']//a[@id='sellerProfileTriggerId']")[0]
                ->__toString();
            return;
        }
        catch (Error | Exception $e )
        {
            $this->log_error("The Seller Second method" );
        }

        //seller method for subscribe and save items
        try {
            $this->seller=$this
                ->right_column
                ->xpath("//div[@id='shipsFromSoldByMessage_feature_div']//span")[0]
                ->__toString();
            //trim the spaces
            $this->seller = trim($this->seller);
            $this->seller=explode('by ' , $this->seller)[1] ?? $this->seller;

            return;
        }
        catch (Error | Exception $e )
        {
            $this->log_error("The Seller Third Method" );
        }
    }

    public function get_shipping_price(): void
    {
        try {
            $shipping_price=$this->right_column->xpath("//div[@id='deliveryBlockMessage']//span[@data-csa-c-delivery-price]")[0]->__toString();
            $this->shipping_price= (int) Str::finish(Str::replace("." , ""  , get_numbers_only_with_dot($shipping_price) ) , "00");
        }
        catch (Error  | Exception $e)
        {
            $this->log_error("Shipping Price");
            $this->shipping_price= 0;
        }
    }

    public function get_condition(): void
    {
        //todo search for the different item conditions in the wbesite
        try {
            $this->condition = (Str::contains($this->information->offers->itemCondition , "NewCondition" , true)) ? "new" : "used" ;
        }catch (\Exception){
            $this->log_error("First Part Condition");
        }
    }


    public static function get_variations($url) : array
    {
        $response=parent::get_website($url);
        parent::prepare_dom($response ,$document ,  $xml);

        try {
            $variations=$xml->xpath("//div[@id='item-page-variant-group-bg-div']//div[@class='dn']/a");
            foreach ($variations as $variation){
                $temp_ip=Str::remove("/" , Str::squish(  \Arr::last(explode("/" , $variation->attributes()->href->__toString()))) );
                $options[$temp_ip]=$variation->__toString();
            }
            return $options ?? [];

        } catch (Exception $e){
            Notification::make()
                ->danger()
                ->title("Error")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();
        }


        return  [];
    }


    public static function prepare_url($domain, $product, $store=null ): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL );
    }


    function is_system_detected_as_robot(): bool { return false;}
}
