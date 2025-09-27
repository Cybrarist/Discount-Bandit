<?php

namespace Database\Seeders;

use App\Models\Link;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory(100)
            ->hasAttached(Link::factory()->count(1), [
                'user_id' => fake()->randomElement([1, 2]),
            ])
            ->create();
    }
}
