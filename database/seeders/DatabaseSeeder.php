<?php

namespace Database\Seeders;

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

        User::firstOrCreate([
            'email' => 'test@test.com',
            ], [
            'name' => 'Test User',
            'password'=>'password'
        ]);

        $this->call([
            StoreSeeder::class,
        ]);
    }
}
