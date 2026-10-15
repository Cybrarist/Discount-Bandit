<?php

namespace Tests\Feature\Filament\Resources\Products\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ListProductsPriceHistoryTest extends TestCase
{
    private Product $defaultProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstWhere('role', RoleEnum::Admin);
        $this->actingAs($user);

        $this->defaultProduct = Product::firstWhere('name', 'Default Product');

        $link = Link::factory()->create(['store_id' => 1, 'key' => 'chart-item', 'name' => 'Chart Item']);
        $link->products()->attach($this->defaultProduct->id, ['user_id' => $user->id]);

        LinkHistory::create(['link_id' => $link->id, 'price' => 95, 'used_price' => 0, 'date' => now()->subDays(3)->toDateString()]);
        LinkHistory::create(['link_id' => $link->id, 'price' => 90, 'used_price' => 0, 'date' => now()->subYear()->toDateString()]);
    }

    public function test_listing_products_does_not_query_price_history(): void
    {
        DB::enableQueryLog();

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSee('Price History');

        $this->assertEmpty(collect(DB::getQueryLog())->filter(fn (array $query): bool => str_contains($query['query'], 'link_histories')));
    }

    public function test_price_history_series_returns_the_last_six_months(): void
    {
        Livewire::test(ListProducts::class)
            ->call('price_history_series', $this->defaultProduct->id)
            ->assertReturned([[
                'name' => 'Chart Item ($)',
                'data' => [['x' => now()->subDays(3)->toDateString(), 'y' => 95]],
            ]]);
    }

    public function test_price_history_series_cannot_load_another_users_product(): void
    {
        $this->actingAs(User::firstWhere('role', RoleEnum::User));

        // findOrFail: a 404 response in the app, the exception itself in Livewire tests
        $this->expectException(ModelNotFoundException::class);

        Livewire::test(ListProducts::class)
            ->call('price_history_series', $this->defaultProduct->id);
    }
}
