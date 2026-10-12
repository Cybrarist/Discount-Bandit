<?php

namespace Tests\Feature;

use App\Enums\ProductStatusEnum;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_index_returns_paginated_products_for_authenticated_user(): void
    {
        Product::factory()->count(3)->create(['user_id' => $this->user->id]);
        Product::factory()->create(['user_id' => User::factory()->create()->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'name', 'status', 'is_favourite', 'lowest_price', 'highest_price', 'created_at']],
                'links',
                'meta',
            ]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/products')->assertUnauthorized();
    }

    public function test_index_filters_by_name(): void
    {
        Product::factory()->create(['user_id' => $this->user->id, 'name' => 'Apple Watch']);
        Product::factory()->create(['user_id' => $this->user->id, 'name' => 'Samsung TV']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?filter[name]=apple');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Apple Watch');
    }

    public function test_index_filters_by_status(): void
    {
        Product::factory()->create(['user_id' => $this->user->id, 'status' => ProductStatusEnum::Active]);
        Product::factory()->create(['user_id' => $this->user->id, 'status' => ProductStatusEnum::Disabled]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?filter[status]=active');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.status', ProductStatusEnum::Active);
    }

    public function test_index_filters_by_is_favourite(): void
    {
        Product::factory()->create(['user_id' => $this->user->id, 'is_favourite' => true]);
        Product::factory()->create(['user_id' => $this->user->id, 'is_favourite' => false]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?filter[is_favourite]=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.is_favourite', true);
    }

    public function test_index_sorts_by_name(): void
    {
        Product::factory()->create(['user_id' => $this->user->id, 'name' => 'Zebra']);
        Product::factory()->create(['user_id' => $this->user->id, 'name' => 'Apple']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?sort=name');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Apple')
            ->assertJsonPath('data.1.name', 'Zebra');
    }

    public function test_index_includes_links_when_requested(): void
    {
        Product::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?include=links');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['links']]]);
    }

    public function test_index_includes_categories_when_requested(): void
    {
        Product::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/products?include=categories');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['categories']]]);
    }
}
