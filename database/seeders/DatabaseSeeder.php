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
        $stores=setup_stores();
        foreach ($stores as $store)
        {
            Store::updateOrCreate(
                ['domain'=>$store['domain']],
                $store
            );
        }


//        setup_main_user();
    }
}
