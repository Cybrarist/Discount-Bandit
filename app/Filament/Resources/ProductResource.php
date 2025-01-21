<?php

namespace App\Filament\Resources;

use App\Enums\StatusEnum;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Resources\ProductResource\Pages\ViewProduct;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Helpers\CurrencyHelper;
use App\Helpers\ProductHelper;
use App\Helpers\StoreHelper;
use App\Helpers\StoresAvailable\Noon;
use App\Helpers\URLHelper;
use App\Models\Product;
use App\Models\Store;
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?int $navigationSort=1;
    protected static ?string $recordTitleAttribute="name";
    protected static bool $isGloballySearchable=true;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->nullable()
                    ->hiddenOn(CreateProduct::class),

                TextInput::make('url')
                    ->required(fn($operation)=> $operation=="create")
                    ->autofocus(fn($operation)=> $operation=="create")
                    ->url()
                    ->label('URL of product')
                    ->live(onBlur: true )
                    ->afterStateUpdated(function ($state){
                        if($state){
                            $url=new URLHelper($state);
                            if ($url->store )
                                StoreHelper::is_unique($url);
                        }
                    }),

                Select::make('status')
                    ->options(StatusEnum::class)
                    ->default(StatusEnum::Published)
                    ->preload()
                    ->native(false),

                TextInput::make('notify_price')
                    ->nullable()
                    ->numeric(),

                TextInput::make('notify_percentage')
                    ->nullable()
                    ->hintIcon("heroicon-o-information-circle", "Get notified when price drops below specified percentage")
                    ->suffix('%')
                    ->numeric(),

                Select::make('categories')
                    ->relationship('categories','name')
                    ->createOptionForm([Forms\Components\TextInput::make('name')->required()])
                    ->multiple()
                    ->nullable()
                    ->preload(),

                DatePicker::make('snoozed_until')
                    ->label("Snooze Notification Until"),


                Section::make("Cross Stores Notification Settings")
                    ->columns(4)
                    ->schema([
                        Forms\Components\Toggle::make('only_official')
                            ->label("Official Sellers Only")
                            ->inline(false),

                        Forms\Components\Toggle::make('favourite')
                            ->label("Add To Favourite")
                            ->inline(false),

                        Forms\Components\Toggle::make('stock')
                            ->label("Alert When Stock Available")
                            ->inline(false),

                        Forms\Components\TextInput::make('lowest_within')
                            ->label("Alert if Product lowest within")
                            ->nullable()
                            ->suffix('days')
                            ->maxValue(65535),

                        TextInput::make('max_notifications')
                            ->label("Max Notification Sent Daily")
                            ->integer()
                            ->minValue(0)
                            ->numeric()
                            ->placeholder("unlimited")
                            ->hintIcon("heroicon-o-information-circle",
                                "this is for products that fluctuate in price, it won't send any more notification UNLESS the price is less than earlier"),
                    ])
                    ->collapsible(),


                Section::make("Variants")
                    ->hiddenOn(["view", "edit"])
                    ->columns(4)
                    ->schema([

                        Forms\Components\Toggle::make('variations')
                            ->label("choose other variations to include")
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(function ($component, $get , $state){
                                if (!$get('url')){
                                    Notification::make()
                                        ->danger()
                                        ->title("URL Field is empty")
                                        ->send();
                                    return ;
                                }

                                if ($state  ){
                                    $url=new URLHelper($get('url'));
                                    $final_class_name="App\Helpers\StoresAvailable\\" . Str::ucfirst( explode(".",$url->store->domain)[0]);
                                    $variations = call_user_func($final_class_name . '::get_variations' , $url->final_url  );
                                    $component->getContainer()
                                        ->getComponent('variation_options')
                                        ->options($variations)
                                        ->disabled(false)
                                        ->multiple()
                                        ->native(false);
                                }

                            }),

                        Select::make('variation_options')
                            ->key('variation_options')
                            ->preload()
                            ->disabled()
                            ->placeholder("choose variation")
                 ]),
            ]);

    }

    public static function table(Table $table): Table
    {

        $stores=StoreHelper::get_stores_with_active_products();
        $currencies=CurrencyHelper::get_currencies();


        return $table
            ->modifyQueryUsing(function ($query){
                $query->with([
                    "product_stores:id,product_id,store_id,price,notify_price,updated_at,highest_price,lowest_price,key,updated_at",
                ]);
            })
            ->recordUrl( function ($record){
                return (!$record->name) ?  route('filament.admin.resources.products.edit', ['record' => $record]) : null;
            })
            ->columns([
                Grid::make([
                    'lg' => 10,
                    ])
                    ->schema([
                        ImageColumn::make('image')
                            ->verticallyAlignCenter()
                            ->alignCenter()
                            ->height('100%')
                            ->width('100%')
                            ->extraImgAttributes(['style'=>'max-height:200px; '])
                            ->columnSpan(3)
                            ->url(fn ($record): string =>
                                route('filament.admin.resources.products.edit',
                                ['record' => $record])
                            ),

                        Grid::make([
                            'lg' => 8,
                            'md'=>4
                        ])
                            ->schema([

                                TextColumn::make('name')
                                    ->default("Fetching....")
                                    ->columnSpan(4)
                                    ->searchable()
                                    ->words(10)
                                    ->url(fn ($record): string =>
                                    route('filament.admin.resources.products.edit',
                                        ['record' => $record])
                                    )
                                    ->sortable(),

                                TextColumn::make('status')
                                    ->columnSpan(2)
                                    ->badge()
                                    ->verticallyAlignCenter()
                                    ->alignEnd()
                                    ->color(fn ($state) => StatusEnum::get_badge($state)),

                                ToggleIconColumn::make('favourite')
                                    ->columnSpan(1)
                                    ->alignEnd()
                                    ->onIcon("heroicon-s-star")
                                    ->offIcon("heroicon-o-star"),



                                IconColumn::make('delete')
                                    ->getStateUsing(fn() => true)
                                    ->columnSpan(1)

                                    ->alignEnd()
                                    ->icon(fn(bool $state): string => 'heroicon-m-trash')
                                    ->color('danger')
                                    ->action(Tables\Actions\DeleteAction::make()),


                            ])->columnSpan(7),

                        Stack::make([

                            Tables\Columns\Layout\Panel::make([
                                Grid::make([
                                    'lg'=> 7,
                                    'sm'=>1
                                ])->schema([
                                    TextColumn::make('product_stores')
                                        ->formatStateUsing(function ($state, $record) use($stores){

                                            $store = new Store();
                                            $store->fill($stores[$state->store_id]);

                                            $final_class_name="App\Helpers\StoresAvailable\\" . Str::ucfirst( explode(".", $store->domain)[0]);
                                            $url= call_user_func($final_class_name . '::prepare_url' , $store->domain, $state->key , $store );

                                            return new HtmlString("<a class='underline text-primary-400' href='$url' target='_blank'>{$stores[$state->store_id]["name"]}</a>");
                                        })
                                        ->columnSpan([
                                            'md'=> 2,
                                            'sm'=> 7
                                        ])
                                        ->html()
                                        ->listWithLineBreaks(),

                                    TextColumn::make('product_stores.highest_price')
                                        ->listWithLineBreaks()
                                        ->columnSpan([
                                            'md'=> 1,
                                            'sm'=> 7
                                        ])
                                        ->color('danger'),

                                    TextColumn::make('product_stores.price')
                                        ->columnSpan([
                                            'md'=> 1,
                                            'sm'=> 2
                                        ])
                                        ->formatStateUsing(function ($record) use ($currencies, $stores) {
                                            return ProductHelper::prepare_multiple_prices_in_table($record, $currencies, $stores);
                                        })
                                        ->label('Prices'),

                                    TextColumn::make('product_stores.lowest_price')
                                        ->columnSpan([
                                            'md'=> 1,
                                            'sm'=> 2
                                        ])
                                        ->listWithLineBreaks()
                                        ->color('success'),

                                    TextColumn::make('product_stores.updated_at')
                                        ->columnSpan([
                                            'md'=> 2,
                                            'sm'=> 3
                                        ])
                                        ->listWithLineBreaks(),
                                ])
                            ])
                                ->columnSpanFull(),


                        ])
                            ->space(3)
                            ->columnSpanFull(),

                  ])
            ])
            ->contentGrid([
                'md' => 2,
            ])
            ->defaultSort('favourite' , 'desc')
            ->filters([

                SelectFilter::make('status')
                    ->options(StatusEnum::class)
                    ->preload()
                    ->multiple(),

                Filter::make('notify_price')->query(function ($query){
                    $query->whereHas(
                        'product_stores',function($query){
                            $query->whereRaw('product_store.price  <= product_store.notify_price');
                        }
                    );})
                    ->label('Price Met Target')
                    ->toggle(),

                Filter::make('favourite')->query(function (Builder $query) {
                    $query->where('favourite', 1);
                    })
                    ->label('Favourite product')
                    ->toggle(),

                Filter::make('highest_price')->query(function (Builder $query) {
                        return $query->whereHas('product_stores', function ($query){
                            $query->whereColumn('price' , '<=' , 'highest_price');
                        });
                    })
                    ->label('Lower Than Highest Price')
                    ->toggle(),

                Filter::make('lowest_within')
                    ->form([
                        TextInput::make('lowest_within_x')
                            ->label('Price is lowest in X Days'),

                    ])
                    ->query(function (Builder $query, $data) {

                        if (!$data["lowest_within_x"])
                            return ;

                        $products_with_lowest_price_within_x=\DB::select("
                            SELECT p.product_id
                            FROM product_store p
                            JOIN (
                                SELECT price_histories.product_id, price_histories.store_id , MIN(price_histories.price) AS min_price
                                FROM price_histories
                                WHERE price_histories.date >= NOW() - INTERVAL {$data['lowest_within_x']} DAY and
                                      price_histories.price >0
                                GROUP BY product_id , store_id )
                                ph ON p.product_id = ph.product_id and p.store_id = ph.store_id
                            WHERE p.price <= ph.min_price and
                                  p.price >0
                            ;
                            "
                        );

                        $product_ids= Arr::pluck($products_with_lowest_price_within_x , 'product_id');
                      $query->wherein('id', $product_ids);
                })->indicateUsing(function (array $data): ?string {
                        if (! $data['lowest_within_x']) {
                            return null;
                        }

                        return "Lowest in {$data['lowest_within_x']} Days" ;
                    }),


                SelectFilter::make('category')
                    ->relationship('categories','name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }



    public static function getRelations(): array
    {
        return [
            RelationManagers\StoresRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
            'view' =>ViewProduct::route('/{record}'),
        ];
    }
}
