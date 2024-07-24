<?php

namespace App\Jobs;

use App\Models\ProductStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class CrawlProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private $product_store_id)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $current_product_store=ProductStore::with("store")->find($this->product_store_id);
        $final_class_name="App\Helpers\StoresAvailable\\" . Str::ucfirst( explode(".",$current_product_store->store->domain)[0]);
        new $final_class_name($current_product_store->id);
    }
}
