<?php

namespace App\Filament\Forms;

use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductLink;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImportForm
{
    public static function configure()
    {
        ini_set('max_execution_time', 6000);

        return Action::make('Import')
            ->steps([
                Step::make('Database')
                    ->description('Select Database Type')
                    ->live()
                    ->schema([
                        Select::make('database')
                            ->options([
                                'MySQL' => 'MySQL',
                                'SQLite' => 'SQLite',
                            ])
                            ->live()
                            ->preload()
                            ->native(false),
                    ]),

                Step::make('MySQL')
                    ->visible(fn ($get) => $get('database') == 'MySQL')
                    ->live()
                    ->schema([
                        TextInput::make('host')
                            ->default('127.0.0.1')
                            ->required(),

                        TextInput::make('database_name')
                            ->default('discount_bandit')
                            ->required(),

                        TextInput::make('port')
                            ->default(3306)
                            ->required(),

                        TextInput::make('username')
                            ->default('root')
                            ->required(),

                        TextInput::make('password')
                            ->default("password")
                            ->required(),
                    ]),

                Step::make('SQLite')
                    ->visible(fn ($get) => $get('database') == 'SQLite')
                    ->description('SQLite Database')
                    ->live()
                    ->schema([
                        FileUpload::make('database_file')
                            ->maxFiles(1)
                            ->required(),
                    ]),

            ])->action(function ($data) {

                if ($data['database'] == 'MySQL') {
                    $database_builder = DB::build([
                        'driver' => 'mysql',
                        'host' => $data['host'],
                        'port' => $data['port'],
                        'database' => $data['database_name'],
                        'username' => $data['username'],
                        'password' => $data['password'],
                    ]);
                } elseif ($data['database'] == 'SQLite') {
                    $database_builder = DB::build([
                        'driver' => 'sqlite',
                        'database' => storage_path('app/private/'.$data['database_file']),
                    ]);
                }

                // get all categories
                self::import_categories($database_builder);

                // get all products
                self::import_products($database_builder);

            });
    }

    public static function import_categories($database_builder): void
    {
        $database_builder->table('categories')
            ->chunkById(100, function ($categories) {
                foreach ($categories as $category) {
                    Category::updateOrCreate([
                        'name' => $category->name,
                        'user_id' => Auth::id(),
                    ]);
                }
            });
    }

    public static function import_products($database_builder): void
    {

        $currentStores = Store::all()->pluck('id', 'name')->toArray();
        $remoteStores = $database_builder->table('stores')->pluck('name', 'id')->toArray();

        $database_builder->table('products')
            ->chunkById(100, function ($products) use ($database_builder, $currentStores, $remoteStores) {
                foreach ($products as $product) {

                    DB::transaction(function () use ($product, $database_builder, $currentStores, $remoteStores) {
                        $status = match ($product->status) {
                            'p' => ProductStatusEnum::Active,
                            'd' => ProductStatusEnum::Disabled,
                            default => ProductStatusEnum::Silenced,
                        };

                        $new_product = Product::updateOrCreate(
                            [
                                'name' => $product->name,
                                'image' => $product->image,
                                'user_id' => Auth::id(),
                            ],
                            [
                                'is_favourite' => $product->favourite,
                                'status' => $status,
                                'snoozed_until' => $product->snoozed_until,
                                'max_notifications_daily' => $product->max_notifications,
                                'notifications_sent' => 0,
                            ]);

                        // move categories to product
                        $categories_for_product = $database_builder
                            ->table('category_product')
                            ->join('categories', 'category_product.category_id', '=', 'categories.id')
                            ->where('product_id', $product->id)
                            ->pluck('categories.name')
                            ->toArray();

                        $categories = Category::whereIn('name', $categories_for_product)
                            ->pluck('id')
                            ->toArray();
                        $new_product->categories()->sync($categories);

                        // get the product stores
                        $product_stores = $database_builder
                            ->table('product_store')
                            ->where('product_id', $product->id)
                            ->get();

                        foreach ($product_stores as $product_store) {

                            $new_link = ProductLink::updateOrCreate(
                                [
                                    'key' => $product_store->key,
                                    'store_id' => $currentStores[$remoteStores[$product_store->store_id]],
                                    'product_id' => $new_product->id,
                                    'user_id' => Auth::id(),

                                ],
                                [
                                    'price' => $product_store->price / 100,
                                    'used_price' => $product_store->used_price / 100,
                                    'highest_price' => $product_store->highest_price / 100,
                                    'lowest_price' => $product_store->lowest_price / 100,
                                    'shipping_price' => $product_store->shipping_price / 100,
                                    'is_in_stock' => $product_store->in_stock,
                                    'rating' => $product_store->rate,
                                    'total_reviews' => $product_store->number_of_rates,
                                    'seller' => $product_store->seller ?? "",
                                    'condition' => $product_store->condition,
                                    'is_official' => true,
                                ]);

                            $new_link->notification_settings()->create([
                                'price_desired' => $product_store->notify_price ?? null,
                                'percentage_drop' => $product_store->notify_percentage,
                                'price_lowest_in_x_days' => $product->lowest_within,
                                'is_in_stock' => $product->stock,
                                'is_official' => $product->only_official,
                                'user_id' => Auth::id(),
                                'is_shipping_included' => $product_store->add_shipping,
                            ]);

                        }
                    });
                }
            });

    }
}
