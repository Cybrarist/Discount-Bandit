<?php

namespace App\Jobs;

use App\Classes\Amazon;
use App\Classes\Ebay;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class GetProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $product;
    public int $store;
    public  $product_store_id;
    public $ebay_id;
    public function __construct($product, $store, $product_store_id=null, $ebay_id =null)
    {
        $this->product=$product;
        $this->store=$store;
        $this->product_store_id=$product_store_id;
        $this->ebay_id=$ebay_id;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $product_to_get=Product::where('id' ,(int) $this->product)
            ->with([
                'stores'=>function ($query){
                        $query->where('stores.id', (int) $this->store);
                }] )->first();


        if (is_amazon($product_to_get->stores[0]->host))
            $result= new Amazon($product_to_get , $product_to_get->stores[0]);
        elseif (is_ebay($product_to_get->stores[0]->host))
            $result= new Ebay($product_to_get , $product_to_get->stores[0] , null, $this->ebay_id);

        }
}
