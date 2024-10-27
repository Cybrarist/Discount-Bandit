<?php

namespace App\Helpers\StoresAvailable;

use Error;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class Snapdeal extends StoreTemplate
{
    const string MAIN_URL="https://www.store/product/random/product_id" ;

    private  $main_schema;
    private  $metas;

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
            $this->metas = $this->xml->xpath("//meta");


            $this->main_schema = $this->xml->xpath('//*[@itemprop]');

//            dd($this->main_schema);
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
            foreach ($this->metas as $meta)
                foreach ($meta->attributes() as $key => $value)
                    if ($value == "og_title"){
                        $this->name= $meta->attributes()['content']->__toString();
                        return;
                    }
        }
        catch (Error | Exception $exception){
            $this->log_error("Product Name First Method", $exception->getMessage());
        }

        try {
            foreach ($this->main_schema as $meta)
                foreach ($meta->attributes() as $key => $value)
                    if ($value == "name" && $key =='itemprop'){
                        $this->name= $meta->attributes()['title']->__toString();
                        return ;
                    }
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Name Second Method", $exception->getMessage());
        }


    }

    public function get_image(): void
    {
        try {
            foreach ($this->metas as $meta)
                foreach ($meta->attributes() as $key => $value)
                    if ($value == "og_image"){
                        $this->image=Str::of($meta->attributes()['content']->__toString())
                            ->replace('https:/' , 'https://')
                            ->trim()
                            ->toString();
                        return;
                    }
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image First Method", $exception->getMessage());
        }

        try {
            foreach ($this->main_schema as $meta)
                foreach ($meta->attributes() as $key => $value)
                    if ($value == "image" && $key =='itemprop'){
                        $this->image=Str::of($meta->attributes()['src']->__toString())
                            ->replace('https:/' , 'https://')
                            ->trim()
                            ->toString();
                        return ;
                    }
        }
        catch ( Error | Exception $exception) {
            $this->log_error("Product Image Second Method", $exception->getMessage());
        }

    }

    public function get_price(): void
    {
        try {
            foreach ($this->main_schema as $meta)
                foreach ($meta->attributes() as $key => $value)
                    if ($value == "price" && $key =='itemprop'){
                        $this->price = (float) $meta->__toString();
                        return ;
                    }
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Price First Method",$exception->getMessage());
        }
    }

    //didn't see product with used price
    public function get_used_price(): void {}

    //not supported
    public function get_stock(): void {
        try {
            foreach ($this->main_schema as $meta)
                foreach ($meta->attributes() as $key => $value)
                    if ($value == "availability" && $key =='itemprop'){
                        $this->in_stock = Str::contains($meta->attributes()['href']->__toString() , "instock", true);
                        return ;
                    }
            return ;
        }
        catch ( Error | Exception $exception  ) {
            $this->log_error("Stock Method",$exception->getMessage());
        }
    }

    public function get_no_of_rates(): void
    {
        try {
            foreach ($this->main_schema as $meta)
                foreach ($meta->attributes() as $key => $value)
                    if ($value == "ratingCount" && $key =='itemprop'){
                        $this->no_of_rates =(int)  $meta->__toString();
                        return ;
                    }
        }
        catch (Error | Exception $exception)
        {
            $this->log_error("No. Of Rates", $exception->getMessage());
        }
    }

    public function get_rate(): void
    {
        try {
            foreach ($this->main_schema as $meta)
                foreach ($meta->attributes() as $key => $value)
                    if ($value == "ratingValue" && $key =='itemprop'){
                        $this->rating =(float)  $meta->__toString();
                        return ;
                    }

        }
        catch (Error | Exception $exception )
        {
            $this->log_error("The Rate", $exception->getMessage());
        }

    }

    public function get_seller(): void {
       try {
            $this->seller= Str::trim($this->xml->xpath("//span[@itemprop='name']")[0]->__toString());
       }
       catch (Error | Exception $exception )
       {
           $this->log_error("The Seller", $exception->getMessage());
           $this->seller="Snapdeal";
       }

    }

    public function get_shipping_price(): void {
        try {
            $this->shipping_price= (float) $this->document->getElementById('shippingCharges')->getAttribute('value');
        }
        catch (Error | Exception $exception )
        {
            $this->log_error("The Shipping", $exception->getMessage());
        }

    }

    public function get_condition() {}

    //todo needs to be checked with id=is_script
    public static function get_variations($url) : array {

        $response=parent::get_website($url);

        parent::prepare_dom($response ,$document ,$xml);

        try {
            $array_script = json_decode($document->getElementById("attributesJson")->textContent, true);


            foreach ($array_script as $single)
            {
                $options[$single['id']]= $single['value'];
            }


            dd($options);



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
            ["store", "product_id", "random"],
            [$domain , $product, Str::random()],
            self::MAIN_URL);
    }


    function is_system_detected_as_robot(): bool {return false;}

}
