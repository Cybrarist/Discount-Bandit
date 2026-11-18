<?php

namespace Tests\Feature\Filament\Resources\Stores;

use App\Enums\RoleEnum;
use App\Filament\Resources\Stores\Pages\EditStore;
use App\Models\Currency;
use App\Models\Store;
use App\Models\User;
use Filament\Forms\Components\Select;
use Livewire\Livewire;
use Tests\TestCase;

class StoreCrawlingMethodFormTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::firstWhere('role', RoleEnum::Admin));
    }

    public function test_store_form_offers_both_crawling_methods(): void
    {
        $store = Store::factory()->create([
            'name'        => 'Amazon Spain',
            'domain'      => 'amazon.es',
            'slug'        => 'amazon_es',
            'image'       => 'store.png',
            'currency_id' => Currency::firstWhere('code', 'USD')->id,
        ]);

        Livewire::test(EditStore::class, ['record' => $store->getRouteKey()])
            ->assertFormFieldExists(
                'custom_settings.crawling_method',
                function (Select $field): bool {
                    return array_keys($field->getOptions()) === ['chromium', 'http'];
                },
            );
    }
}
