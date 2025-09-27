<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Link;
use App\Models\Product;
use App\Models\User;
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



        if (app()->isLocal()) {
            User::create([
                'email' => 'test@test.com',
                'password' => 'password',
                'name' => 'password',
                'role' => RoleEnum::Admin,
            ]);

            User::create([
                'email' => 'test2@test.com',
                'password' => 'password',
                'name' => 'password',
                'role' => RoleEnum::User,
            ]);

//            $this->call([
//                ProductSeeder::class,
//            ]);

        }


    }
}
