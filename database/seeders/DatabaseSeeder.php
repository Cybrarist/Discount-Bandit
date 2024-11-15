<?php

namespace Database\Seeders;

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

        if (app()->isLocal())
            User::create([
                'email' => 'test@test.com',
                'password' => 'password',
                'name' => 'password',
            ]);

        $this->call([
            StoreSeeder::class,
        ]);



        Store::whereIn('domain',[
            'fnac.com'
        ])->delete();
    }
}
