<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Currency;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

//
//         \App\Models\User::factory()->create([
//             'name' => 'Test User',
//             'email' => 'test@example.com',
//             'password' => 'password'
//         ]);

        setup_stores();
        setup_main_user();

        $stores=Store::all();

//        $product=Product::factory()->hasAttached($stores->random(2),[
//            'price'=>10000,
//            'notify_price'=>30000,
//
//        ])->count(100)->create();
    }
}
