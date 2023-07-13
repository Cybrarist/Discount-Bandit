<?php

namespace App\Jobs;

use App\Enums\StatusEnum;
use App\Mail\ProductDiscountMail;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GetProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public int $product;
    public int $service;
    public string $currency;
    public int $notify_price;
    public int $current_price;
    public function __construct($product, $service, $currency , $notify_price, $price)
    {
        $this->product=$product;
        $this->service=$service;
        $this->currency=$currency;
        $this->notify_price=$notify_price;
        $this->current_price=$price ?? 0;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $product=Product::find($this->product);
        $service=Service::find($this->service);

        if (
            ( $product->status == StatusEnum::Published  || $product->statys==StatusEnum::Silenced )
            &&
            $service->status == StatusEnum::Published
        )
            try {
                $response=Http::withUserAgent(user_agents())->get($service->url . "/dp/" . $product->ASIN);
//                $response=Http::withUserAgent("Mozilla/5.0 (Linux; Android 13; Pixel 6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36")->get("https://amazon.pl/dp/B09XQLH785" );
                //prepare the document for the parsing
                $doc=new \DOMDocument();
                $internalErrors = libxml_use_internal_errors(true);
                $doc->loadHTML($response);
                $xml=simplexml_import_dom($doc);

                try {
                    //get the center column to get the related data for it
                    $center_column=$xml->xpath("//div[@id='centerCol']")[0];
                }
                catch (\Throwable | \Exception $e)
                {
                    Log::debug($product);
                    Log::debug( $service);
                    Log::error($e);
                    Log::info("Trying with the next schedule");
                    return;
                }


                //get the right column to get the seller and other data
                $right_column=$xml->xpath("//div[@id='desktop_buybox']")[0];

                //get the name of the product
                $name=get_product_name($center_column);
                //get the current price of the item
                $price=get_original_price($center_column, $this->currency);
                //is the shipment_prime
                if ($center_column->xpath("//div[@id='apex_desktop_qualifiedBuybox']//div[@id='deliveryPriceBadging_feature_div']"))
                    $is_prime=true;

                //check if it's a top deal
                $is_top_deal=check_is_top_deal($center_column);


                //get the ratings
                $ratings=get_number_of_rates($center_column);
                $rating=get_rate($center_column, $service->id);


                //get the shipping price
                $shipping_price=get_shipping_price($right_column);
                //get the seller
                $seller=get_seller($right_column);



                if (!$product->image || !$product->name || $product->name != $name)
                {
                    $product->updateOrCreate(
                        ['id'=>$product->id],
                        [
                        'name'=>$name,
                        'image'=>$doc->getElementById("landingImage")->getAttribute("data-old-hires") ?? "NA",
                    ]);
                }

                $product->services()->updateExistingPivot($service->id,
                [
                    'price'=> (int)((float)$price),
                    'number_of_rates'=>$ratings,
                    'seller'=>$seller,
                    'rate'=>$rating,
                    'is_prime'=>$is_prime ?? false,
                    'top_deal'=>$is_top_deal,
                    'shipping_price'=>$shipping_price,
                ]);
                if ($price <= $this->notify_price && $price != $this->current_price)
                {
                    if ($product->status != StatusEnum::Silenced)
                    {
                        Log::debug("Notifying with Email" . $product->id);
                        $user=User::first();
                        \Mail::to($user->email)->send(new ProductDiscountMail(
                           product: $product,
                           service: $service,
                           current_price:  $price,
                           notify_price:  $this->notify_price,
                           currency: $this->currency
                        ));
                    }
                }

            }
            catch (\Exception $e)
            {
                Log::debug("Couldn't Run the job");
                Log::error($e);
            }
    }
}



