<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\GroupDiscount;
use App\Notifications\ProductDiscount;
use App\Notifications\ProductDiscounted;
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
    public function handle(): void
    {
        User::first()->notify(new ProductDiscounted(
            product_id: 123,
            product_name: "This is a test product",
            store_name: "this is a test store",
            price: 100,
            highest_price: 120,
            lowest_price: 80,
            product_url: "https://cybrarist.com",
            image: "https://raw.githubusercontent.com/Cybrarist/Discount-Bandit/refs/heads/master/storage/app/public/bandit.png",
            currency: "$",
            tags: ",New Test Tags",
        ));
    }
}
