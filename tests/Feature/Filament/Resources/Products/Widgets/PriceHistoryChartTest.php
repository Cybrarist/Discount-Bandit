<?php

namespace Tests\Feature\Filament\Resources\Products\Widgets;

use App\Enums\RoleEnum;
use App\Filament\Resources\Products\Widgets\PriceHistoryChart;
use App\Models\Product;
use App\Models\User;
use Livewire\Livewire;
use Tests\TestCase;

class PriceHistoryChartTest extends TestCase
{
    public function test_product_page_chart_renders(): void
    {
        $this->actingAs(User::firstWhere('role', RoleEnum::Admin));

        Livewire::test(PriceHistoryChart::class, ['record' => Product::firstWhere('name', 'Default Product')])
            ->call('loadWidget')
            ->assertOk();
    }
}
