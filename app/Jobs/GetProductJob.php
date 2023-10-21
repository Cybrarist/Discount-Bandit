<?php

namespace App\Jobs;

use App\Classes\MainStore;
use App\Classes\Stores\Amazon;
use App\Classes\Stores\Ebay;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $product_store_id;
    public string $domain;


    public function __construct($product_store_id , $domain)
    {
        $this->product_store_id=$product_store_id;
        $this->domain=$domain;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        if (MainStore::is_amazon($this->domain))
            new Amazon($this->product_store_id);
        elseif (MainStore::is_ebay($this->domain))
            new Ebay($this->product_store_id);

    }
}
