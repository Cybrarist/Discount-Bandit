<?php

namespace Tests\Feature\Filament\Resources\Products\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Currency;
use App\Models\Link;
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

    private function create_links_for_default_product(): void
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
                ->create(['store_id' => $store->id, 'price' => $price])
                ->products()
                ->attach($product->id, ['user_id' => $user->id]);
        }
    }
}
