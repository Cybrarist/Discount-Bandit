<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculateGroupListPriceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    public $group_list;
    public function __construct($group_list)
    {
        $this->group_list=$group_list;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $service=$this->group_list->service_id;
            $products_inside=$this->group_list->products()->with(['services'=>function($query) use ($service){
                $query->where('services.id', $service);
            }])->get();

            $total_price=0;

            foreach ($products_inside as $product)
                if ($product->services)
                    $total_price+=$product->services[0]->pivot->price/100 * $product->pivot->qty;



            $this->group_list->update([
               'last_price'=>$total_price * 100,
            ]);
        }
        catch (\Throwable |  \Exception $e)
        {
            Log::error("Couldn't calculate the price for grouplist");
            Log::error($e);
        }

    }
}
