<?php

namespace App\Console\Commands;

use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateOldDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate your old database to new one';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        echo "Disabling 2FA from old database";

        DB::table('breezy_sessions')->update([
            "two_factor_secret"=>null
        ]);

        echo "Migrating  old database to new one\n";

        if (env('DB_CONNECTION') =='mysql'){

            //amazon products
            $products=Product::whereNotNull('asin')->get();
            //update amazon products
            foreach ($products as $product){
                ProductStore::where('product_id' , $product)
                    ->whereIn('store_id' , Store::where('name' , 'Like' , '%Amazon')->pluck('id')->toArray())
                    ->update([
                        'key'=>$product->asin
                    ]);
                echo  " Migrating Product \033[0;32m $product->name \033[0m \n";
            }

            //Argos products
            $products=Product::whereNotNull('argos_id')->get();
            //update amazon products
            foreach ($products as $product){
                ProductStore::where('product_id' , $product)
                    ->whereIn('store_id' , Store::where('name' , 'Like' , '%Argos')->pluck('id')->toArray())
                    ->update([
                        'key'=>$product->argos_id
                    ]);
                echo  " Migrating Product \033[0;32m $product->name \033[0m \n";
            }
            //Walmart products
            $products=Product::whereNotNull('argos_id')->get();
            //update amazon products
            foreach ($products as $product){
                ProductStore::where('product_id' , $product)
                    ->whereIn('store_id' , Store::where('name' , 'Like' , '%Walmart')->pluck('id')->toArray())
                    ->update([
                        'key'=>$product->walmart_ip
                    ]);
                echo  " Migrating Product \033[0;32m $product->name \033[0m \n";
            }





        }else if (env('DB_CONNECTION') =='sqlite'){
            foreach (Product::on('mysql')->cursor() as $record){

                //insert the new product.
                try {
                    $new_product= Product::on('sqlite')
                        ->create([
                        "name" => $record->name,
                        "image" => $record->image,
                        "status" => $record->status,
                        "favourite" => $record->favourite,
                        "stock" => $record->stock,
                        "snoozed_until" => $record->snoozed_until,
                        "max_notifications" => $record->max_notifications,
                        "lowest_within" => $record->lowest_within,
                        "only_official" => $record->only_official,
                    ]);

                }catch (\Exception $exception){
                    dd($exception->getMessage());
                }

                //migrate old history.

                $old_histories=PriceHistory::on('mysql')
                    ->where('product_id',$record->id)
                    ->get();

                foreach ($old_histories as $old_history)
                    PriceHistory::create([
                        "product_id" => $new_product->id,
                        "date"=>$old_history->date,
                        "price"=>$old_history->price,
                        "store_id"=>$old_history->store_id,
                        "used_price"=>0
                    ]);

                //migrate product store.

                $product_stores=ProductStore::on('mysql')
                    ->where('product_id',$record->id)
                    ->get();

                foreach ($product_stores as $product_store){

                    $current_store=Store::find($product_store->store_id);

                    $product_key= match (true){
                        Str::contains($current_store->name, "amazon", true) => $record->asin,
                        Str::contains($current_store->name, "ebay", true) => $product_store->ebay_id,
                        Str::contains($current_store->name, "walmart", true) => $record->walmart_ip,
                        Str::contains($current_store->name, "argos", true) => $record->argos_id,
                        default=>null
                    };

                    try{
                        ProductStore::create([
                            "product_id" => $new_product->id,
                            "store_id" => $product_store->store_id,
                            "price" =>$product_store->price,
                            "used_price"=>0,
                            "notify_price"=>$product_store->notify_price,
                            "rate"=>$product_store->rate,
                            "number_of_rates"=>$product_store->number_of_rates,
                            "seller"=>$product_store->seller,
                            "offers"=>$product_store->offers,
                            "shipping_price"=>$product_store->shipping_price,
                            "condition"=>$product_store->condition,
                            "notifications_sent"=>$product_store->notifications_sent,
                            "lowest_30"=>$product_store->lowest_30,
                            "add_shipping"=>$product_store->add_shipping,
                            "in_stock"=>$product_store->in_stock,
                            "key"=>$product_key,
                        ]);
                    }catch (\Exception $e){
                        dd($product_store);
                    }

                }



                echo  " Migrating Product \033[0;32m $record->name \033[0m \n";

            }




        }
    }
}
