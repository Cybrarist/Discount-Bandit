<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Product;
use App\Models\ProductStore;
use App\Models\Store;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            StoreSeeder::class,
        ]);
    }
}
