<?php

namespace Database\Seeders;

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
    }
}
