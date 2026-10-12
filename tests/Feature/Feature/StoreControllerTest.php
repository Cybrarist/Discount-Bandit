<?php

namespace Tests\Feature\Feature;

use App\Enums\RoleEnum;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => RoleEnum::Admin]);
    }

    private function validStorePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Amazon',
            'domain' => 'amazon.com',
            'image' => 'amazon.png',
            'slug' => 'amazon',
            'status' => 'active',
        ], $overrides);
    }

    public function test_store_creates_store_without_custom_settings(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/stores', $this->validStorePayload());

        $response->assertCreated();
        $this->assertDatabaseHas(Store::class, ['slug' => 'amazon']);
    }

    public function test_store_accepts_valid_custom_settings(): void
    {
        $payload = $this->validStorePayload([
            'custom_settings' => [
                'crawling_method' => 'chromium',
                'timeout' => 5000,
                'page_event' => 'networkIdle',
                'name_selectors' => 'h1.product-title',
                'price_selectors' => 'span.price',
                'price_schema_key' => 'offers.price',
                'in_or_out_of_stock_selector_type' => true,
                'stock_value_to_search' => 'In Stock',
            ],
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/stores', $payload);

        $response->assertCreated();

        $store = Store::where('slug', 'amazon')->first();
        $this->assertEquals('chromium', $store->custom_settings['crawling_method']);
        $this->assertEquals(5000, $store->custom_settings['timeout']);
        $this->assertEquals('networkIdle', $store->custom_settings['page_event']);
    }

    public function test_store_rejects_invalid_crawling_method(): void
    {
        $payload = $this->validStorePayload([
            'custom_settings' => ['crawling_method' => 'invalid-method'],
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/stores', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['custom_settings.crawling_method']);
    }

    public function test_store_rejects_invalid_page_event(): void
    {
        $payload = $this->validStorePayload([
            'custom_settings' => ['page_event' => 'notAnEvent'],
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/stores', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['custom_settings.page_event']);
    }

    public function test_store_rejects_non_integer_timeout(): void
    {
        $payload = $this->validStorePayload([
            'custom_settings' => ['timeout' => 'fast'],
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/stores', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['custom_settings.timeout']);
    }

    public function test_store_rejects_negative_timeout(): void
    {
        $payload = $this->validStorePayload([
            'custom_settings' => ['timeout' => -1],
        ]);

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/stores', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['custom_settings.timeout']);
    }
}
