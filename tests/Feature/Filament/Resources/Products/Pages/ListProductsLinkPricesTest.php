<?php

namespace Tests\Feature\Filament\Resources\Products\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Currency;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class ListProductsLinkPricesTest extends TestCase
{
    public function test_links_are_ordered_by_rate_normalised_price_when_exchange_rate_api_is_configured(): void
    {
        config(['settings.exchange_rate_api_key' => 'test-key']);

        $this->create_links_for_default_product();

        // 150 SAR is 40 USD, so it sits between the 10 and 50 USD links despite the higher raw price
        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSeeInOrder(['Cheap Store', 'Riyal Store', 'Middle Store', 'Pricey Store', 'Unpriced Store']);
    }

    public function test_links_are_ordered_by_raw_price_without_exchange_rate_api(): void
    {
        config(['settings.exchange_rate_api_key' => null]);

        $this->create_links_for_default_product();

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSeeInOrder(['Cheap Store', 'Middle Store', 'Riyal Store', 'Pricey Store', 'Unpriced Store']);
    }

    public function test_favourite_star_toggles_the_product_favourite(): void
    {
        $this->actingAs(User::firstWhere('role', RoleEnum::Admin));

        $product = Product::firstWhere('name', 'Default Product');
        $product->update(['is_favourite' => false]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->call('toggle_favourite', (string) $product->getKey());

        $this->assertTrue($product->refresh()->is_favourite);
    }

    public function test_favourite_star_cannot_toggle_another_users_product(): void
    {
        $this->actingAs(User::firstWhere('role', RoleEnum::User));

        $otherUsersProduct = Product::withoutGlobalScopes()->firstWhere('name', 'Default Product');
        $otherUsersProduct->update(['is_favourite' => false]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->call('toggle_favourite', (string) $otherUsersProduct->getKey())
            ->assertStatus(404);

        $this->assertFalse($otherUsersProduct->refresh()->is_favourite);
    }

    public function test_card_shows_all_time_low_badge_when_best_price_is_at_its_lowest(): void
    {
        $this->create_links_for_default_product(inStock: true, lowestPrice: 10);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSee('All-time Low')
            ->assertDontSee('Unavailable');
    }

    public function test_card_shows_out_of_stock_badge_when_no_priced_link_is_in_stock(): void
    {
        $this->create_links_for_default_product(inStock: false);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->assertSee('Out of Stock')
            ->assertSee('Unavailable')
            ->assertDontSee('All-time Low');
    }

    public function test_no_price_update_filter_hides_links_updated_within_the_given_days(): void
    {
        $this->create_links_for_default_product();

        $recentlyUpdatedLink = Link::withoutGlobalScopes()->whereRelation('store', 'name', 'Cheap Store')->first();
        LinkHistory::create(['link_id' => $recentlyUpdatedLink->id, 'price' => 10, 'used_price' => 0, 'date' => now()->toDateString()]);

        $staleLink = Link::withoutGlobalScopes()->whereRelation('store', 'name', 'Middle Store')->first();
        LinkHistory::create(['link_id' => $staleLink->id, 'price' => 50, 'used_price' => 0, 'date' => now()->subDays(30)->toDateString()]);

        Livewire::test(ListProducts::class)
            ->call('loadTable')
            ->filterTable('no_price_update', ['days' => 7])
            ->assertSee('Middle Store')
            ->assertDontSee('Cheap Store');
    }

    private function create_links_for_default_product(bool $inStock = true, float $lowestPrice = 0): void
    {
        $user = User::firstWhere('role', RoleEnum::Admin);
        $this->actingAs($user);

        $product = Product::firstWhere('name', 'Default Product');

        $usd = Currency::firstWhere('code', 'USD');
        $sar = Currency::firstWhere('code', 'SAR');

        foreach ([['Pricey Store', 300, $usd], ['Unpriced Store', 0, $usd], ['Riyal Store', 150, $sar], ['Cheap Store', 10, $usd], ['Middle Store', 50, $usd]] as [$storeName, $price, $currency]) {
            $store = Store::factory()->create([
                'name'        => $storeName,
                'domain'      => str($storeName)->slug().'.test',
                'slug'        => str($storeName)->slug('_'),
                'image'       => 'test.png',
                'currency_id' => $currency->id,
            ]);

            Link::factory()
                ->create(['store_id' => $store->id, 'price' => $price, 'is_in_stock' => $inStock, 'lowest_price' => $lowestPrice])
                ->products()
                ->attach($product->id, ['user_id' => $user->id]);
        }
    }
}
