<?php

namespace Tests\Feature\Filament\Resources\Stores\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Stores\Pages\CreateStore;
use App\Models\Currency;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CreateStoreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('store');

        $this->actingAs(User::firstWhere('role', RoleEnum::Admin));
    }

    /**
     * @return array<string, mixed>
     */
    private function validStoreData(UploadedFile $image): array
    {
        return [
            'image' => $image,
            'name' => 'Polyglot Store',
            'domain' => 'polyglot.test',
            'slug' => 'polyglot_test',
            'currency_id' => Currency::firstWhere('code', 'USD')->id,
            'allowed_params' => [],
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function phpFilesOn(string $disk): Collection
    {
        return collect(Storage::disk($disk)->allFiles())
            ->filter(fn (string $path): bool => str_contains(strtolower($path), '.ph'));
    }

    public function test_rejects_gif_php_polyglot_logo_and_stores_no_php_file(): void
    {
        $polyglot = UploadedFile::fake()
            ->createWithContent('x.php', "GIF89a\x01\x00\x01\x00\x00\x00\x00;<?php echo 'pwned'; ?>")
            ->mimeType('image/gif');

        Livewire::test(CreateStore::class)
            ->fillForm($this->validStoreData($polyglot))
            ->call('create')
            ->assertHasFormErrors(['image']);

        $this->assertDatabaseMissing(Store::class, ['slug' => 'polyglot_test']);
        $this->assertEmpty($this->phpFilesOn('tmp-for-tests'), 'The PHP file reached the Livewire temporary upload directory.');
        $this->assertEmpty($this->phpFilesOn('store'), 'The PHP file reached the public store disk.');
    }

    public function test_png_logo_is_stored_with_png_extension(): void
    {
        Livewire::test(CreateStore::class)
            ->fillForm($this->validStoreData(UploadedFile::fake()->image('logo.png')))
            ->call('create')
            ->assertHasNoFormErrors();

        $storedImage = Store::firstWhere('slug', 'polyglot_test')->image;

        $this->assertStringEndsWith('.png', $storedImage);
        Storage::disk('store')->assertExists($storedImage);
    }
}
