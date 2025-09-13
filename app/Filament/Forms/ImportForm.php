<?php

namespace App\Filament\Forms;

use App\Enums\Icons\Devicons;
use App\Enums\ProductStatusEnum;
use App\Models\Category;
use App\Models\NotificationSetting;
use App\Models\Product;
use App\Models\ProductLink;
use App\Models\Store;
use Awcodes\Shout\Components\Shout;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ImportForm
{
    public static function configure()
    {
        ini_set('max_execution_time', 6000);

        return Action::make('Import')
            ->icon(Heroicon::ArrowUpCircle)
            ->schema([
                Shout::make('so-important')
                    ->heading('Important Notice')
                    ->content('This will reset all data in the application except users.')
                    ->type('danger'),

                Tabs::make('Tabs')
                    ->activeTab(1)
                    ->tabs([
                        Tab::make('MySQL')
                            ->icon(Devicons::Mysql)
                            ->schema([
                                TextInput::make('host')
                                    ->placeholder('127.0.0.1'),

                                TextInput::make('database_name')
                                    ->placeholder('discountbandit'),

                                TextInput::make('port')
                                    ->placeholder(3306),

                                TextInput::make('username')
                                    ->placeholder('root'),

                                TextInput::make('password')
                                    ->placeholder('***********'),
                            ]),
                        Tab::make('SQLite')
                            ->icon(Devicons::Sqlite)
                            ->schema([
                                FileUpload::make('database_file')
                                    ->maxFiles(1),
                            ]),
                    ]),
            ])
            ->action(function ($data) {

                if ($data['host'] &&
                    $data['port'] &&
                    $data['database_name'] &&
                    $data['username'] &&
                    $data['password']
                ) {
                    $database_builder = DB::build([
                        'driver' => 'mysql',
                        'host' => $data['host'],
                        'port' => $data['port'],
                        'database' => $data['database_name'],
                        'username' => $data['username'],
                        'password' => $data['password'],
                    ]);
                } elseif ($data['database_file']) {
                    $database_builder = DB::build([
                        'driver' => 'sqlite',
                        'database' => storage_path('app/private/'.$data['database_file']),
                    ]);
                } else {
                    Notification::make()
                        ->title('Please Fill all fields for your selected type')
                        ->danger()
                        ->send();

                    return;
                }

                self::reset_all();

                // get all categories
                self::import_categories($database_builder);

                // get all products
                self::import_products($database_builder);

            });
    }

    public static function reset_all()
    {
        NotificationSetting::truncate();
        ProductLink::truncate();
        DB::table('category_product')->truncate();
        Product::truncate();
        Category::truncate();
    }

    public static function import_categories($database_builder): void
    {
        $database_builder->table('categories')
            ->chunkById(100, function ($categories) {
                $new_categories = [];
                foreach ($categories as $category) {
                    $new_categories[] = [
                        'name' => $category->name,
                        'user_id' => Auth::id(),
                    ];
                }

                Category::insert($new_categories);
            });
    }

    public static function import_products($database_builder): void
    {

        $current_stores = Store::all()->pluck('id', 'name')->toArray();
        $remote_stores = $database_builder->table('stores')->pluck('name', 'id')->toArray();

        $old_categories = $database_builder
            ->table('categories')
            ->pluck('name', 'id')
            ->toArray();

        $new_categories = Category::all()
            ->pluck('id', 'name')
            ->toArray();

        $start_product_id = 1;
        $start_product_link_id = 1;

        $database_builder->table('products')
            ->chunkById(5, function ($products) use ($database_builder, $old_categories,
                $current_stores, $remote_stores, $new_categories, &$start_product_id,
                &$start_product_link_id) {

                $new_products = [];
                $product_links = [];
                $product_categories = [];
                $notification_settings = [];
                $product_store_histories = [];

                $categories_for_current_products_batch = $database_builder
                    ->table('category_product')
                    ->whereIn('product_id', $products->pluck('id')->toArray())
                    ->select([
                        'category_id',
                        'product_id',
                    ])
                    ->get();

                $old_products_stores = $database_builder
                    ->table('product_store')
                    ->whereIn('product_id', $products->pluck('id')->toArray())
                    ->get();

                foreach ($products as $old_product) {
                    $status = match ($old_product->status) {
                        'p' => ProductStatusEnum::Active,
                        'd' => ProductStatusEnum::Disabled,
                        default => ProductStatusEnum::Silenced,
                    };

                    $new_products[$old_product->id] = [
                        'new_id' => $start_product_id,
                        'name' => $old_product->name,
                        'image' => $old_product->image,
                        'is_favourite' => $old_product->favourite,
                        'status' => $status,
                        'snoozed_until' => $old_product->snoozed_until,
                        'max_notifications_daily' => $old_product->max_notifications,
                        'notifications_sent' => 0,
                        'user_id' => Auth::id(),
                    ];

                    $start_product_id++;
                }

                Product::insert(Arr::select($new_products, [
                    'name',
                    'image',
                    'is_favourite',
                    'status',
                    'snoozed_until',
                    'max_notifications_daily',
                    'notifications_sent',
                    'user_id',
                ]));

                foreach ($categories_for_current_products_batch as $category_product) {
                    $product_categories[] = [
                        'category_id' => $new_categories[$old_categories[$category_product->category_id]],
                        'product_id' => $new_products[$category_product->product_id]['new_id'],
                    ];
                }

                DB::table('category_product')
                    ->insert($product_categories);

                foreach ($old_products_stores as $old_product_store) {
                    $product_links[$old_product_store->id] = [
                        'key' => $old_product_store->key,
                        'store_id' => $current_stores[$remote_stores[$old_product_store->store_id]],
                        'product_id' => $new_products[$old_product_store->product_id]['new_id'],
                        'price' => $old_product_store->price * 10,
                        'used_price' => $old_product_store->used_price * 10,
                        'highest_price' => $old_product_store->highest_price * 10,
                        'lowest_price' => $old_product_store->lowest_price * 10,
                        'shipping_price' => $old_product_store->shipping_price * 10,
                        'is_in_stock' => $old_product_store->in_stock,
                        'rating' => $old_product_store->rate,
                        'total_reviews' => $old_product_store->number_of_rates,
                        'seller' => $old_product_store->seller ?? "",
                        'condition' => $old_product_store->condition,
                        'is_official' => true,
                        'user_id' => Auth::id(),
                    ];

                    $notification_settings[$old_product_store->id] = [
                        'product_link_id' => $start_product_link_id,
                        'price_desired' => $old_product_store->notify_price ?? null,
                        'percentage_drop' => $old_product_store->notify_percentage,
                        'price_lowest_in_x_days' => $old_product->lowest_within,
                        'is_in_stock' => $old_product->stock,
                        'is_official' => $old_product->only_official,
                        'is_shipping_included' => $old_product_store->add_shipping,
                        'user_id' => Auth::id(),
                    ];

                    $start_product_link_id++;
                }

                ProductLink::insert($product_links);
                NotificationSetting::insert($notification_settings);

                Notification::make()
                    ->title('Imported '.count($products).' products')
                    ->success()
                    ->send();
            });

    }

}
