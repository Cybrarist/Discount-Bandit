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
    const string MAIN_URL="https://uae.store/api/catalog-browse/browse/products?productIds=product_id" ;
    private  $main_schema;

    public function __construct(int $product_store_id)
    {
        parent::__construct($product_store_id);
    }

    //define crawler
    public function crawler(): void
    {
        parent::crawl_url_chrome([
            "x-context-request"=> '{"applicationId":101,"tenantId":"5DF1363059675161A85F576D"}'
        ]);
    }

    public function prepare_sections_to_crawl(): void
    {
        try {
            //get the center column to get the related data for it
            $this->main_schema=json_decode($this->xml->xpath("//pre")[0]->__toString(), true)['products'][0];

            dump($this->main_schema['options'][3]);
        }catch (Error | Exception $exception) {
            dd($exception);
            $this->log_error("Crawling EMax", $exception->getMessage());
        }

    }

    /**
     * Get the data from the store
     */
    public function get_name(): void
    {
        try {
            $this->name = $this->main_schema['name'];
            return;
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name First Method", $exception->getMessage());
        }
    }

    public function get_image(): void
    {
        try {
            $this->image = $this->main_schema['primaryAsset']["contentUrl"];
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {

            //original price
            $this->price=  (float) $this->main_schema['priceInfo']['price']['amount'];

            //check for sale price
            foreach ($this->main_schema['variants'] as $single_variant)
                if ($single_variant['productId'] === $this->current_record->key)
                    $this->price=  (float) $single_variant['priceInfo']['price']['amount'];

            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }

        //method 2 to return the price of the product
        try {
            $whole=Str::remove([",","\u{A0}"] ,
                $this->center_column
                    ->xpath("//div[@id='corePriceDisplay_desktop_feature_div']//span[@class='a-price-whole']")[0]
                    ->__toString());

            $fraction=Str::remove([",","\u{A0}"] ,
                $this->center_column
                    ->xpath("//div[@id='corePriceDisplay_desktop_feature_div']//span[@class='a-price-fraction']")[0]
                    ->__toString());

            $this->price= (float)"$whole.$fraction";
            return;
        }
        catch (Error | Exception $exception )  {
            $this->log_error( "Price Second Method",$exception->getMessage());
        }

    }

    public function get_used_price(): void
    {

        //method 1 to return the price of the product
        try {
            $prices_in_the_page=json_decode($this->right_column->xpath("//div[contains(@class,'twister-plus-buying-options-price-data')]")[0]->__toString());

            foreach ($prices_in_the_page->{'desktop_buybox_group_1'} as $single_price)
                if ($single_price->{"buyingOptionType"} == "USED")
                    $this->price_used=$single_price->{'priceAmount'};
        }
        catch ( Error | Exception  $exception )
        {
            $this->log_error("First Method Used Price",$exception->getMessage());
        }
    }

    public function get_stock(): void
    {
        try {
            $availability_string=Str::squish($this->document->getElementById("availability")->textContent) ;

            $this->in_stock = Str::contains($availability_string , "in stock" , true) && Str::length($availability_string) <10;

        }catch (Exception $exception){
            $this->log_error( "Stock Availability First Method",$exception->getMessage());
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            $ratings=$this->center_column->xpath("//span[@id='acrCustomerReviewText']")[0]->__toString();
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
            //check if the store is amazon poland or not
            ($this->current_record->domain == "amazon.pl") ? $exploding='z' : $exploding='out';

            $this->rating= explode(" $exploding" ,
                $this->center_column->xpath("//div[@id='averageCustomerReviews']//span[@id='acrPopover']//span[@class='a-icon-alt']")[0]->__toString() ,
                2)[0];
        }
        catch (Error | Exception $exception )
        {
            $this->log_error("The Rate", $exception->getMessage());
        }

    }

    public function get_seller(): void
    {

        try {
            $this->seller=$this
                ->right_column
                ->xpath("//div[@id='merchantInfoFeature_feature_div']//div[@class='offer-display-feature-text']//span")[0]
                ->__toString();

            throw_if(!$this->seller , new Exception());

            return;
        }
        catch (Error | Exception $exception ) {
            $this->log_error("The Seller First Method", $exception->getMessage() );
        }

        try {
            $this->seller=$this
                ->right_column
                ->xpath("//div[@id='merchantInfoFeature_feature_div']//a[@id='sellerProfileTriggerId']")[0]
                ->__toString();

            throw_if(!$this->seller , new Exception());

            return;
        }
        catch (Error | Exception $exception ) {
            $this->log_error("The Seller Second method", $exception->getMessage() );
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

            throw_if(!$this->seller , new Exception());

            return;
        }
        catch (Error | Exception $exception )
        {
            $this->log_error( "The Seller Third Method" ,   $exception->getMessage());
            $this->seller="";
        }
    }

    public function get_shipping_price(): void
    {
        try {
            $shipping_price=$this->right_column->xpath("//div[@id='deliveryBlockMessage']//span[@data-csa-c-delivery-price]")[0]->__toString();
            $shipping_price= Str::replace("," , "." , $shipping_price);
            $this->shipping_price= (float) GeneralHelper::get_numbers_only_with_dot($shipping_price);
        }
        catch (Error  | Exception $exception)
        {
            $this->log_error("Shipping Price", $exception->getMessage());
        }
    }

    // TODO: Implement get_condition() method.
    public function get_condition() {}



    public static function get_variations($url) : array
    {
        $response=parent::get_website($url);

        parent::prepare_dom($response ,$document ,$xml);
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

        } catch (Exception ){
            Notification::make()
                ->danger()
                ->title("Error")
                ->body("couldn't get the variation")
                ->persistent()
                ->send();
        }

        return  [];
    }


    public static function prepare_url( $domain, $product, $store = null): string
    {
        return Str::replace(
            ["store", "product_id"],
            [$domain , $product],
            self::MAIN_URL);
    }
    function is_system_detected_as_robot(): bool { return false;}

}
