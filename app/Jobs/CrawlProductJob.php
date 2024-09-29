<?php

namespace App\Jobs;

use App\Models\ProductStore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class CrawlProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;




    public $tries = 1;

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
        try {
            $current_product_store=ProductStore::with("store")->find($this->product_store_id);
            $final_class_name="App\Helpers\StoresAvailable\\" . Str::ucfirst( explode(".",$current_product_store->store->domain)[0]);
            new $final_class_name($current_product_store->id);
        }catch (\Exception | \Error $e){
            Log::error("Job Stopped And Failed");
        }

    }
    public function failed(?Throwable $exception): void
    {
        Log::error($exception);
        $this->fail($exception);
        $this->delete();
    }
}
