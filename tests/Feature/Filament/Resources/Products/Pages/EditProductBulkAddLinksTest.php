<?php

namespace Tests\Feature\Filament\Resources\Products\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Products\Actions\BulkAddLinksAction;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Models\Link;
use App\Models\NotificationSetting;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class EditProductBulkAddLinksTest extends TestCase
{
    private Product $defaultProduct;

    private Link $alreadyAddedLink;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::firstWhere('role', RoleEnum::Admin);
        $this->actingAs($user);

        $this->defaultProduct = Product::firstWhere('name', 'Default Product');

        $this->alreadyAddedLink = Link::factory()->create(['store_id' => 1, 'key' => 'already-added']);
        $this->alreadyAddedLink->products()->attach($this->defaultProduct->id, ['user_id' => $user->id]);
    }

    public function test_flags_errors_and_duplicates_then_adds_only_valid_links_with_their_own_settings(): void
    {
        $component = Livewire::test(EditProduct::class, ['record' => $this->defaultProduct->id])
            ->mountAction('bulkAddLinks')
            ->fillForm([
                'price_desired' => 50,
                'urls'          => [
                    ['url' => 'https://test.com/new-item'],
                    ['url' => 'test.com/new-item'],
                    ['url' => 'https://test.com/already-added'],
                    ['url' => 'https://unknown-store.com/item'],
                    ['url' => ''],
                ],
            ])
            ->goToNextWizardStep()
            ->assertHasNoFormErrors();

        $links = $component->instance()->mountedActions[0]['data']['links'];

        $this->assertSame(
            [
                BulkAddLinksAction::STATUS_OK,
                BulkAddLinksAction::STATUS_DUPLICATE,
                BulkAddLinksAction::STATUS_DUPLICATE,
                BulkAddLinksAction::STATUS_ERROR,
            ],
            array_values(array_column($links, 'status')),
        );

        $firstItemKey = array_key_first($links);
        $this->assertEquals(50, $links[$firstItemKey]['price_desired']);
        $links[$firstItemKey]['price_desired'] = 30;

        $component
            ->fillForm(['links' => $links])
            ->callMountedAction()
            ->assertHasNoFormErrors()
            ->assertNotified('1 link(s) added');

        $newLink = Link::withoutGlobalScopes()->firstWhere('key', 'new-item');

        $this->assertTrue($newLink->products()->whereKey($this->defaultProduct->id)->exists());
        $this->assertEquals(30, NotificationSetting::firstWhere('link_id', $newLink->id)->price_desired);
        $this->assertSame(1, NotificationSetting::where('link_id', $newLink->id)->count());
        $this->assertFalse(NotificationSetting::where('link_id', $this->alreadyAddedLink->id)->exists());
    }

    public function test_removing_a_link_clears_the_duplicate_flag_of_its_repeat(): void
    {
        $component = Livewire::test(EditProduct::class, ['record' => $this->defaultProduct->id])
            ->mountAction('bulkAddLinks')
            ->fillForm(['urls' => [['url' => 'https://test.com/new-item'], ['url' => 'test.com/new-item']]])
            ->goToNextWizardStep();

        $firstItemKey = array_key_first($component->instance()->mountedActions[0]['data']['links']);

        $component->callAction(
            TestAction::make('removeLink')->schemaComponent("links.{$firstItemKey}.linkSection"),
        );

        $links = $component->instance()->mountedActions[0]['data']['links'];

        $this->assertArrayNotHasKey($firstItemKey, $links);
        $this->assertSame([BulkAddLinksAction::STATUS_OK], array_values(array_column($links, 'status')));
    }

    public function test_pasting_a_list_into_one_row_splits_it_into_editable_rows(): void
    {
        $component = Livewire::test(EditProduct::class, ['record' => $this->defaultProduct->id])
            ->mountAction('bulkAddLinks');

        $firstRowKey = array_key_first($component->instance()->mountedActions[0]['data']['urls']);

        $component->set("mountedActions.0.data.urls.{$firstRowKey}.url", "https://test.com/a https://test.com/b,https://test.com/c");

        $rows = $component->instance()->mountedActions[0]['data']['urls'];

        $this->assertSame($firstRowKey, array_key_first($rows));
        $this->assertSame(['https://test.com/a', 'https://test.com/b', 'https://test.com/c'], array_column($rows, 'url'));
    }

    public function test_short_urls_are_resolved_together_on_the_review_step(): void
    {
        Store::firstWhere('domain', 'test.com')->update(['short_url_hosts' => ['a.co']]);

        Http::preventStrayRequests();
        Http::fake([
            'a.co/d/first'  => Http::response('', 301, ['Location' => 'https://test.com/first-item?ref=share']),
            'a.co/d/second' => Http::response('', 301, ['Location' => 'https://test.com/second-item']),
            'a.co/d/broken' => Http::response('', 404),
        ]);

        $component = Livewire::test(EditProduct::class, ['record' => $this->defaultProduct->id])
            ->mountAction('bulkAddLinks')
            ->fillForm([
                'urls' => [
                    ['url' => 'https://a.co/d/first'],
                    ['url' => 'a.co/d/second'],
                    ['url' => 'https://a.co/d/broken'],
                    ['url' => 'https://test.com/plain-item'],
                ],
            ]);

        $this->assertSame(
            ['https://a.co/d/first', 'a.co/d/second', 'https://a.co/d/broken', 'https://test.com/plain-item'],
            array_column($component->instance()->mountedActions[0]['data']['urls'], 'url'),
        );
        Http::assertNothingSent();

        $component->goToNextWizardStep()->assertHasNoFormErrors();

        $links = array_values($component->instance()->mountedActions[0]['data']['links']);

        $this->assertSame(
            ['https://test.com/first-item?ref=share', 'https://test.com/second-item', 'https://a.co/d/broken', 'https://test.com/plain-item'],
            array_column($links, 'url'),
        );
        $this->assertSame(
            [BulkAddLinksAction::STATUS_OK, BulkAddLinksAction::STATUS_OK, BulkAddLinksAction::STATUS_ERROR, BulkAddLinksAction::STATUS_OK],
            array_column($links, 'status'),
        );
        $this->assertSame(['first-item', 'second-item', null, 'plain-item'], array_column($links, 'key'));
        $this->assertSame("Couldn't resolve the short URL", $links[2]['message']);
        Http::assertSentCount(3);
    }

    public function test_requires_at_least_one_filled_url(): void
    {
        Livewire::test(EditProduct::class, ['record' => $this->defaultProduct->id])
            ->mountAction('bulkAddLinks')
            ->fillForm(['urls' => [['url' => ''], ['url' => '  ']]])
            ->goToNextWizardStep()
            ->assertHasFormErrors(['urls']);
    }
}
