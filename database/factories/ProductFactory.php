<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'asin' => $this->faker->word(),
            'name' => $this->faker->name(),
            'image' => $this->faker->word(),
            'favourite' => $this->faker->boolean(),
            'stock' => $this->faker->boolean(),
            'snoozed_until' => Carbon::now(),
            'max_notifications' => $this->faker->randomNumber(),
            'lowest_within' => $this->faker->randomNumber(),
            'only_official' => $this->faker->boolean(),
            'walmart_ip' => $this->faker->ipv4(),
            'argos_id' => $this->faker->word(),
        ];
    }
}
