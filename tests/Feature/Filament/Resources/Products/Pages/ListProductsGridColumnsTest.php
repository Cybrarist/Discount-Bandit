<?php

namespace Tests\Feature\Filament\Resources\Products\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Links\Pages\ListLinks;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\TestCase;

class ListProductsGridColumnsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::firstWhere('role', RoleEnum::Admin));

        Filament::setCurrentPanel('admin');
        Filament::setServingStatus();
        Filament::bootCurrentPanel();
    }

    public function test_columns_picker_is_only_on_the_products_list(): void
    {
        Livewire::test(ListProducts::class)->assertSeeHtml('id="products-grid-columns"');

        Livewire::test(ListLinks::class)->assertDontSeeHtml('id="products-grid-columns"');
    }

    public function test_default_grid_is_used_until_columns_are_picked(): void
    {
        Livewire::test(ListProducts::class)
            ->assertSet('grid_columns', null)
            ->call('loadTable')
            ->assertSeeHtml('--cols-2xl: repeat(4, minmax(0, 1fr))');
    }

    public function test_picked_columns_apply_from_large_screens_up(): void
    {
        $component = Livewire::test(ListProducts::class)
            ->set('grid_columns', 5)
            ->call('loadTable')
            ->assertSeeHtml('--cols-xl: repeat(5, minmax(0, 1fr))');

        $this->assertSame(['md' => 2, 'lg' => 3, 'xl' => 5], $component->instance()->content_grid());
    }

    public function test_out_of_range_columns_are_clamped(): void
    {
        $component = Livewire::test(ListProducts::class);

        $component->set('grid_columns', 99);
        $this->assertSame(6, $component->instance()->content_grid()['xl']);

        $component->set('grid_columns', 0);
        $this->assertSame(2, $component->instance()->content_grid()['xl']);
    }
}
