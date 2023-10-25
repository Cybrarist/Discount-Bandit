<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\ProductDiscount;
use Illuminate\Console\Command;

class DiscountTestNotificationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discount:test-notify';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Notification and make sure it works fine';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users=User::all();
        foreach ($users as $user)
            $user->notify(new ProductDiscount(
                product_name: 'This is a test product',
                store_name: "this is a test store"  ,
                price: '100' ,
                product_url: "https://cybrarist.com" ,
                image: "",
                currency: "$"));
    }
}
