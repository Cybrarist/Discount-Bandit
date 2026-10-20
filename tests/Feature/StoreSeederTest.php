<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\Store;
use Database\Seeders\StoreSeeder;
use Tests\TestCase;

class StoreSeederTest extends TestCase
{
    public function test_seeder_merges_short_url_hosts_with_existing_ones(): void
    {
        Store::factory()->create([...$this->store_attributes('store-a.test'), 'short_url_hosts' => ['a.co', 'b.co']]);
        Store::factory()->create([...$this->store_attributes('store-b.test'), 'short_url_hosts' => ['a.co', 'b.co']]);
        Store::factory()->create([...$this->store_attributes('store-c.test'), 'short_url_hosts' => ['x.co']]);
        Store::factory()->create([...$this->store_attributes('store-d.test'), 'short_url_hosts' => null]);

        $this->run_store_seeder([
            [...$this->store_attributes('store-a.test'), 'short_url_hosts' => ['a.co', 'b.co', 'c.co']],
            [...$this->store_attributes('store-b.test'), 'short_url_hosts' => ['a.co', 'c.co', 'd.co']],
            [...$this->store_attributes('store-c.test'), 'short_url_hosts' => ['y.co']],
            [...$this->store_attributes('store-d.test'), 'short_url_hosts' => ['z.co']],
        ]);

        $this->assertSame(['a.co', 'b.co', 'c.co'], Store::firstWhere('domain', 'store-a.test')->short_url_hosts);
        $this->assertSame(['a.co', 'b.co', 'c.co', 'd.co'], Store::firstWhere('domain', 'store-b.test')->short_url_hosts);
        $this->assertSame(['x.co', 'y.co'], Store::firstWhere('domain', 'store-c.test')->short_url_hosts);
        $this->assertSame(['z.co'], Store::firstWhere('domain', 'store-d.test')->short_url_hosts);
    }

    /**
     * @return array{name: string, domain: string, slug: string, image: string, currency_id: int}
     */
    private function store_attributes(string $domain): array
    {
        return [
            'name'        => $domain,
            'domain'      => $domain,
            'slug'        => str_replace(['.', '-'], '_', $domain),
            'image'       => 'store.png',
            'currency_id' => Currency::firstWhere('code', 'USD')->id,
        ];
    }

    /**
     * Run the store seeder with the given stores instead of the predefined ones.
     *
     * @param  list<array<string, mixed>>  $stores
     */
    private function run_store_seeder(array $stores): void
    {
        $seeder = new class($stores) extends StoreSeeder
        {
            /**
             * @param  list<array<string, mixed>>  $stores
             */
            public function __construct(private array $stores) {}

            protected function stores(): array
            {
                return $this->stores;
            }
        };

        $seeder->run();
    }
}
