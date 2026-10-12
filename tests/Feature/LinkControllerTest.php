<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LinkControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Store $store;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->store = Store::factory()->create();
    }

    private function attachLinkToUser(Link $link): void
    {
        $product = Product::factory()->create(['user_id' => $this->user->id]);
        $link->products()->attach($product->id, ['user_id' => $this->user->id]);
    }

    public function test_index_returns_paginated_links_for_authenticated_user(): void
    {
        $ownedLinks = Link::factory()->count(3)->create(['store_id' => $this->store->id]);
        $ownedLinks->each(fn ($link) => $this->attachLinkToUser($link));

        $otherLink = Link::factory()->create(['store_id' => $this->store->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/links');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [['id', 'key', 'price', 'is_in_stock', 'is_official', 'created_at']],
                'links',
                'meta',
            ]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/links')->assertUnauthorized();
    }

    public function test_index_filters_by_name(): void
    {
        $apple = Link::factory()->create(['store_id' => $this->store->id, 'name' => 'Apple Watch']);
        $samsung = Link::factory()->create(['store_id' => $this->store->id, 'name' => 'Samsung TV']);
        $this->attachLinkToUser($apple);
        $this->attachLinkToUser($samsung);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/links?filter[name]=apple');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Apple Watch');
    }

    public function test_index_includes_store_when_requested(): void
    {
        $link = Link::factory()->create(['store_id' => $this->store->id]);
        $this->attachLinkToUser($link);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/links?include=store');

        $response->assertOk()
            ->assertJsonStructure(['data' => [['store']]]);
    }

    public function test_index_filters_by_key_using_url(): void
    {
        $amazonStore = Store::factory()->create(['domain' => 'amazon.com']);
        $match = Link::factory()->create(['store_id' => $amazonStore->id, 'key' => 'B08N5WRWNW?']);
        $other = Link::factory()->create(['store_id' => $this->store->id, 'key' => 'some-other-key?']);
        $this->attachLinkToUser($match);
        $this->attachLinkToUser($other);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/links?filter[key]=https://www.amazon.com/dp/B08N5WRWNW');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.key', 'B08N5WRWNW?');
    }

    public function test_index_filter_by_key_returns_empty_for_invalid_url(): void
    {
        $link = Link::factory()->create(['store_id' => $this->store->id]);
        $this->attachLinkToUser($link);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/links?filter[key]=not-a-valid-url');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_index_booleans_are_cast_correctly(): void
    {
        $link = Link::factory()->create([
            'store_id' => $this->store->id,
            'is_in_stock' => true,
            'is_official' => false,
        ]);
        $this->attachLinkToUser($link);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/links');

        $response->assertOk()
            ->assertJsonPath('data.0.is_in_stock', true)
            ->assertJsonPath('data.0.is_official', false);
    }
}
