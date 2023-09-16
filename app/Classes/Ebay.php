<?php

namespace App\Classes;

use App\Models\Product;
use App\Models\Store;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function PHPUnit\Framework\throwException;

class Ebay extends StoreCrawl
{
    private  $center_column;
    private $right_column;
    private $left_column;

    private $information;

    private  $ebay_id;
    public function __construct($product=null, $store=null,  $parsed_url=null, $ebay_id=null) {

        if ($parsed_url)
        {
            parent::__construct("https://" . $parsed_url['host'] . "/itm/" . $parsed_url['ebay']);
        }
        else
        {

            $this->ebay_id=$ebay_id;
            $this->product=$product;
            $this->store=$store;
            parent::__construct("https://" . $this->store->host . "/itm/" . $this->ebay_id);

            try {
                $this->information=json_decode($this->xml->xpath("//div[contains(@class , 'x-seo-structured-data')]//script")[0]->__toString());
            }
            catch (\Exception $e) {
                $this->throw_this_error($e , "Couldn't Get the Schema for information", $this->ebay_id , $this->store->host);
            }
            try {
                //get the center column to get the related data for it
                $this->left_column=$this->xml->xpath("//div[@id='LeftSummaryPanel']")[0];
                //get the right column to get the seller and other data
                $this->right_column=$this->xml->xpath("//div[@id='RightSummaryPanel']")[0];

            }
            catch (\Exception $e)
            {
                $this->throw_this_error($e , "Couldn't Get the Columns for ebay", $this->ebay_id , $this->store->host);
            }

            $this->start_processing();
            $this->update_store_product_details();

        }



    }

    public function get_title(){

        try {
            $this->title = $this->information->title
                ?? dd(  $this->information->title  . " \n" . $this->left_column->xpath("//h1[@class='x-item-title__mainTitle']//span")[0]->__toString());
//                ?? trim($this->left_column->xpath("//h1[@class='x-item-title__mainTitle']//span")[0]->__toString());
        }
        catch ( Exception $e) {
            $this->throw_this_error($e , "The Title", $this->product->itm , $this->store->host);
            $this->title = "NA";
        }

    }


    public function get_image(){
        //todo
        try {
            $this->image = $this->information->image ?? "NA";
        }
        catch ( Exception $e) {
            $this->throw_this_error($e , "The Title", $this->product->image , $this->store->host);
            $this->image = "";
        }

    }



    public function get_price(){
        //method 1 to return the price of the product
        try {
            $price_string=$this->information->offers->price
                ??
                $this->left_column->xpath("//div[@class='x-price-approx']")[0] ?? null;
            if (!$price_string)
                $price_string=(float) explode(get_currencies($this->store->currency_id) , $this->left_column->xpath("//div[@class='x-price-primary']//span")[0]->__toString())[1];
            $this->price= 100 * $price_string;
        }
        catch (\Exception  $e )
        {
            Log::debug("Getting price with the first method didn't work");
            $this->throw_this_error($e , "First Method Price");
        }

    }


    public function get_no_of_rates(){
        try {

            $ratings=$this->aggregateRating->ratingCount
                ??  $this->left_column->xpath("//span[@class='ebay-reviews-count']")[0]->__toString();
            $this->no_of_rates= filter_var($ratings, FILTER_SANITIZE_NUMBER_INT);
            return;
        }
        catch (Exception $e)
        {
            $this->throw_this_error($e, "The no. of rates", $this->product->asin , $this->store->host);
        }
        $this->no_of_rates=0;
    }

    public function get_rate(){
        try {
            $this->rating=$this->information->aggregateRating->ratingValue
            ?? get_numbers_only_with_dot($this->left_column->xpath("//div[@id='histogramid']//span[@class='ebay-review-start-rating']")[0]->__toString() )  ;
        }
        catch (Exception $e )
        {
            $this->throw_this_error($e , "The Rate", $this->product->asin , $this->store->host);
            $this->rating=-1;
        }
    }

    public function get_seller(){
        try {
            $this->seller=$this->right_column->xpath("//div[@class='ux-seller-section__item--seller']//span")[0]->__toString();
        }
        catch (Exception $e )
        {
            $this->throw_this_error($e , "The Seller" , $this->product->asin, $this->store->host);
        }
        return "NA";
    }

    public function  get_condition(): void
    {
        try{
              $this->condition=  Str::replaceFirst("/ " , "" ,  Str::headline(parse_url($this->information->offers->itemCondition ,PHP_URL_PATH))) ?? "NA";
        }
        catch (Exception $e)
        {
            $this->throw_this_error($e , "The Condition ");
        }
    }


    public function update_store_product_details()
    {
        \DB::table('product_store')
            ->where('product_id', $this->product->id)
            ->where('store_id' , 23)
            ->where('ebay_id', $this->ebay_id)
            ->update(          [
                'price' => (int)((float)$this->price),
                'number_of_rates' => $this->no_of_rates,
                'seller' => $this->seller,
                'rate' => $this->rating,
                'shipping_price' => $this->shipping_price,
                'condition'=>$this->condition ?? "new"
            ]);
    }


    public function get_variations() : array
    {
        $array_script = $this->document->getElementById("twister_feature_div")->getElementsByTagName("script");
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
    }

    public function get_shipping_price(){

        $this->shipping_price=(int)((float) ($this->information->offers->shippingDetails->shippingRate->value ?? 0) * 100);
    }

}
