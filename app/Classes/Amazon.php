<?php

namespace App\Classes;

use App\Models\Product;
use App\Models\Store;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Amazon extends StoreCrawl
{
    private  $center_column;
    private $right_column;
    public function __construct($product=null, $store=null,  $parsed_url=null) {

        if ($parsed_url)
        {
            parent::__construct("https://" . $parsed_url['host'] . "/en/dp/" . $parsed_url['asin']);
        }
        else
        {
            $this->product=$product;
            $this->store=$store;
            parent::__construct("https://" . $this->store->host . "/en/dp/" . $this->product->asin);

            try {
                //get the center column to get the related data for it
                $this->center_column=$this->xml->xpath("//div[@id='centerCol']")[0];
                //get the right column to get the seller and other data
                $this->right_column=$this->xml->xpath("//div[@id='desktop_buybox']")[0];
                $this->start_processing();
                $this->update_store_product_details();
            }
            catch (\Exception $e) {
                $this->throw_this_error($e , "The Center Column", $this->product->asin , $this->store->host);
            }
        }



    }

    public function get_title(){

        try {
            $this->title = trim($this->center_column->xpath("//span[@id='productTitle'][1]")[0]
                ->__toString());
        }
        catch ( Exception $e) {
            $this->throw_this_error($e , "The Title", $this->product->asin , $this->store->host);
            $this->title = "NA";
        }

    }

    public function get_image(){

        try {
            $this->image = $this->document->getElementById("landingImage")->getAttribute("data-old-hires") ?? "NA";
        }
        catch ( Exception $e) {
            $this->throw_this_error($e , "The Title", $this->product->image , $this->store->host);
            $this->image = "";
        }

    }


    public function get_price(){
        //method 1 to return the price of the product
        try {
            $this->price= 100 * (float) Str::replace(get_currencies($this->store->currency_id) , "" ,$this->center_column->xpath("(//span[contains(@class, 'apexPriceToPay')])[1]")[0]->span->__toString());
        }
        catch (\Exception  $e )
        {
            Log::debug("Getting price with the first method didn't work");
            $this->throw_this_error($e , "First Method Price");
            $this->price=0;
        }

        //method 2 to return the price of the product
        try {
            $whole=Str::remove([",","\u{A0}"] , $this->center_column->xpath("//div[@id='corePriceDisplay_desktop_feature_div']//span[@class='a-price-whole']")[0]->__toString());
            $fraction=Str::remove([",","\u{A0}"] , $this->center_column->xpath("//div[@id='corePriceDisplay_desktop_feature_div']//span[@class='a-price-fraction']")[0]->__toString());
            $this->price=  100 * (float)"$whole.$fraction";
        }
        catch (\Exception $e )
        {
            Log::debug("Getting price with the second method didn't work");
            $this->throw_this_error($e , "Second Method Price");
            $this->price=0;
        }

    }


    public function get_no_of_rates(){
        try {

            $ratings=$this->center_column->xpath("//span[@id='acrCustomerReviewText']")[0]->__toString();
            $this->no_of_rates= (int) get_numbers_only_with_dot($ratings);
        }
        catch (Exception $e)
        {
            $this->throw_this_error($e, "The no. of rates", $this->product->asin , $this->store->host);
            $this->no_of_rates=0;
        }
    }

    public function get_rate(){
        try {
            if ($this->store->host =="amazon.pl")
                //Checking Poland
                $exploding='z';
            else
                $exploding='out';

            $this->rating= explode(" $exploding" ,
                $this->center_column->xpath("//div[@id='averageCustomerReviews']//span[@id='acrPopover']//span[@class='a-icon-alt']")[0]->__toString() ,
                2)[0];
        }
        catch (Exception $e )
        {
            $this->throw_this_error($e , "The Rate", $this->product->asin , $this->store->host);
            $this->rating= -1;
        }

    }

    public function get_shipping_price(){
        try {
            $shipping_price=$this->right_column->xpath("//div[@id='deliveryBlockMessage']//span[@data-csa-c-delivery-price]")[0]->__toString();
            $this->shipping_price= (int) Str::finish(Str::replace("." , ""  , get_numbers_only_with_dot($shipping_price) ) , "00");
        }
        catch (Throwable | Exception $e)
        {
            Log::debug("Something Wrong Happened while getting the shipping price");
            Log::error($e);
            $this->shipping_price= 0;
        }
    }


    public function get_seller(){
        try {
            $this->seller= $this->right_column->xpath("//div[@id='tabular_feature_div']//div[@class='tabular-buybox-text'][last()]//span")[0]->__toString();
            if (!$this->seller)
                $this->seller=$this->right_column->xpath("//div[@id='tabular_feature_div']//div[@class='tabular-buybox-text'][last()]//span//a")[0]->__toString();
        }
        catch (Exception $e )
        {
            $this->throw_this_error($e , "The Seller" , $this->product->asin, $this->store->host);
            $this->seller="";
        }
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


    public function update_store_product_details()
    {
        $this->product->stores()->updateExistingPivot($this->store->id,
            [
                'price' => (int)((float)$this->price),
                'number_of_rates' => $this->no_of_rates,
                'seller' => $this->seller,
                'rate' => $this->rating,
                'shipping_price' => $this->shipping_price,
                'condition'=>$this->condition ?? "new"
            ]);
    }
}
