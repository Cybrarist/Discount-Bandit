<?php

namespace Tests\Feature\Filament\Resources\Links\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Links\Pages\ListLinks;
use App\Models\Link;
use App\Models\LinkHistory;
use App\Models\Product;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ListLinksCardsTest extends TestCase
{
    private Link $cardLink;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstWhere('role', RoleEnum::Admin);
        $this->actingAs($user);

        $this->cardLink = Link::factory()->create(['store_id' => 1, 'key' => 'card-item', 'name' => 'Card Item']);
        $this->cardLink->products()->attach(Product::firstWhere('name', 'Default Product')->id, ['user_id' => $user->id]);

        LinkHistory::create(['link_id' => $this->cardLink->id, 'price' => 95, 'used_price' => 0, 'date' => now()->subDays(3)->toDateString()]);
        LinkHistory::create(['link_id' => $this->cardLink->id, 'price' => 90, 'used_price' => 0, 'date' => now()->subYear()->toDateString()]);
    }

    public function test_columns_picker_sets_the_links_grid(): void
    {
        Filament::setCurrentPanel('admin');
        Filament::setServingStatus();
        Filament::bootCurrentPanel();

        Livewire::test(ListLinks::class)
            ->assertSeeHtml('id="links-grid-columns"')
            ->assertDontSeeHtml('id="products-grid-columns"')
            ->set('grid_columns', 5)
            ->call('loadTable')
            ->assertSeeHtml('--cols-xl: repeat(5, minmax(0, 1fr))');
    }

    public function test_card_shows_all_time_low_badge_when_price_is_at_its_lowest(): void
    {
        $this->cardLink->update(['price' => 10, 'lowest_price' => 10, 'is_in_stock' => true]);

        Livewire::test(ListLinks::class)
            ->call('loadTable')
            ->searchTable('Card Item')
            ->assertSee('Card Item')
            ->assertSee('All-time Low')
            ->assertDontSee('Unavailable');
    }

    public function test_card_shows_out_of_stock_badge_when_link_is_not_in_stock(): void
    {
        $this->cardLink->update(['price' => 10, 'is_in_stock' => false]);

        Livewire::test(ListLinks::class)
            ->call('loadTable')
            ->searchTable('Card Item')
            ->assertSee('Out of Stock')
            ->assertSee('Unavailable')
            ->assertDontSee('All-time Low');
    }

    public function test_listing_links_does_not_query_price_history(): void
    {
        DB::enableQueryLog();

        Livewire::test(ListLinks::class)
            ->call('loadTable')
            ->assertSee('Price History');

        $this->assertEmpty(collect(DB::getQueryLog())->filter(fn (array $query): bool => str_contains($query['query'], 'link_histories')));
    }

    public function test_price_history_series_returns_the_last_six_months(): void
    {
        Livewire::test(ListLinks::class)
            ->call('price_history_series', $this->cardLink->id)
            ->assertReturned([[
                'name' => 'Price ($)',
                'data' => [['x' => now()->subDays(3)->toDateString(), 'y' => 95]],
            ]]);
    }

    public function test_price_history_series_cannot_load_another_users_link(): void
    {
        $this->actingAs(User::firstWhere('role', RoleEnum::User));

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(ListLinks::class)
            ->call('price_history_series', $this->cardLink->id);
    }
}
