<?php

namespace App\Jobs;

use App\Enums\StatusEnum;
use App\Mail\ProductDiscountMail;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Notifications\ItemDesiredPriceReached;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\Valuestore\Valuestore;

class GetProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public Product $product;
    public Service $service;
    public string $currency;
    public function __construct($product, $service, $currency)
    {
        $this->product=$product;
        $this->service=$service;
        $this->currency=$currency;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->service=$this->product->services()->find($this->service);
        try {
            $needs_update=false;

            $response=Http::withUserAgent(
                "Mozilla/5.0 (X11; Linux i686 on x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2909.25 Safari/537.36"
            )->get($this->service->url . "/dp/" . $this->product->ASIN);

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
                Log::debug($this->product);
                Log::debug( $this->service);
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

            //get the shipping price
            $shipping_price=get_shipping_price($right_column);
            //get the seller
            $seller=get_seller($right_column);

            if (!$this->product->image || !$this->product->name || $this->product->name != $name)
            {
                $this->product->updateOrCreate(
                    ['id'=>$this->product->id],
                    [
                    'name'=>$name,
                    'image'=>$doc->getElementById("landingImage")->getAttribute("data-old-hires") ?? "NA",
                ]);
            }


            if ($price <= $this->service->pivot->notify_price && $price != $this->service->pivot->price)
            {
                if ($this->product->status != StatusEnum::Silenced)
                {
                    Log::debug("Notifying with Email" . $this->product->id);
                    $user=User::first();
                    \Mail::to($user->email)->send(new ProductDiscountMail(
                       product: $this->product,
                       service: $this->service,
                       current_price:  $price,
                       notify_price:  $this->service->pivot->notify_price,
                       currency: $this->service->currency->code

                    ));
                }
            }

                $this->product->services()->updateExistingPivot($this->service->id,
            [
                'price'=> (int)((float)$price),
                'number_of_rates'=>$ratings,
                'seller'=>$seller,
                'is_prime'=>$is_prime ?? false,
                'top_deal'=>$is_top_deal,
                'shipping_price'=>$shipping_price,

            ]);


        }
        catch (\Exception $e)
        {
            Log::debug("Couldn't Run the job");
            Log::error($e);
        }
    }
}



