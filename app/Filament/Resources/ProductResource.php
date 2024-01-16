<?php

namespace App\Filament\Resources;

use App\Classes\MainStore;
use App\Classes\Stores\Amazon;
use App\Classes\Stores\Argos;
use App\Classes\Stores\Ebay;
use App\Classes\Stores\Walmart;
use App\Classes\URLHelper;
use App\Enums\StatusEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Konnco\FilamentImport\Actions\ImportAction;
use Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort=1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->nullable()
                    ->hiddenOn(Pages\CreateProduct::class)
                    ->disabledOn(['create']),

                TextInput::make('url')
                    ->nullable()
                    ->url()
                    ->label('URL of product')
                    ->live(onBlur: true )
                    ->afterStateUpdated(function ($state, string $operation){
                        if ($operation !="edit" && $state !=null){
                            $url=new URLHelper($state);
                            MainStore::validate_url($url);
                        }

                    }),

                Select::make('status')
                    ->options(StatusEnum::to_array())
                    ->default(StatusEnum::Published)
                    ->preload()
                    ->native(false),

                TextInput::make('notify_price')
                    ->nullable()
                    ->numeric(),

                Select::make('categories')
                    ->relationship('categories','name')
                    ->createOptionForm([Forms\Components\TextInput::make('name')->required()])
                    ->multiple()
                    ->nullable()
                    ->exists('categories','id')
                    ->preload(),

                DatePicker::make('snoozed_until')
                    ->label("Snooze Notification Until"),


                Section::make("Extra Settings")
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
                            ->numeric()
                            ->placeholder("unlimited")
                            ->hintIcon("heroicon-o-information-circle", "this is for products that fluctuate in price, it won't send any more notification UNLESS the price is less than earlier"),

                    ])
                    ->collapsible(),



//                Amazon Settings
                Section::make('Amazon Settings')
                        ->columns(4)
                        ->schema([
                            Forms\Components\Toggle::make('variations')
                                ->label("choose other variations to include")
                                ->inline(false)
                                ->reactive()
                                ->afterStateUpdated(function ($component, $get , $state){
                                    $variations=[];
                                    $url=new URLHelper($get('url'));
                                    if($state)
                                    {
                                        if (MainStore::is_amazon($url->domain)){
                                           $variations= Amazon::get_variations($url->final_url);
                                        }

                                        $component->getContainer()
                                            ->getComponent('variation_options')
                                            ->options($variations)
                                            ->disabled(false)
                                            ->multiple()
                                            ->native(false);
                                    }
                                }
                                ),

                            Select::make('variation_options')
                                ->key('variation_options')
                                ->preload()
                                ->disabled()
                                ->placeholder("choose variation")

                        ])
                        ->visible(function (Forms\Get $get, $record){
                                return MainStore::is_amazon($get('url')) ||
                                    ($record && $record->stores()->where('domain' , 'like' , "%amazon%")->count());
                        }),

//                Amazon Settings
                Section::make('Argos Settings')
                        ->columns(4)
                        ->schema([
                            Forms\Components\Toggle::make('variations')
                                ->label("choose other variations to include")
                                ->inline(false)
                                ->reactive()
                                ->afterStateUpdated(function ($component, $get , $state){
                                    $variations=[];
                                    $url=new URLHelper($get('url'));
                                    if($state)
                                    {
                                        if (MainStore::is_argos($url->domain)){
                                           $variations= Argos::get_variations($url->final_url);
                                        }

                                        $component->getContainer()
                                            ->getComponent('variation_options')
                                            ->options($variations)
                                            ->disabled(false)
                                            ->multiple()
                                            ->native(false);
                                    }
                                }
                                ),

                            Select::make('variation_options')
                                ->key('variation_options')
                                ->preload()
                                ->disabled()
                                ->placeholder("choose variation")

                        ])
                        ->visible(function (Forms\Get $get, $record){
                                return MainStore::is_argos($get('url')) ||
                                    ($record && $record->stores()->where('domain' , 'like' , "%argos%")->count());
                        }),


//                Walmart Settings
                Section::make('Walmart Settings')
                        ->columns(4)
                        ->schema([
                            Forms\Components\Toggle::make('variations')
                                ->label("choose other variations to include")
                                ->inline(false)
                                ->reactive()
                                ->afterStateUpdated(function ($component, $get , $state){
                                    $variations=[];
                                    $url=new URLHelper($get('url'));
                                    if($state)
                                    {
                                        if (MainStore::is_walmart($url->domain)){
                                           $variations= Walmart::get_variations($url->final_url);
                                        }

                                        $component->getContainer()
                                            ->getComponent('variation_options')
                                            ->options($variations)
                                            ->disabled(false)
                                            ->multiple()
                                            ->native(false);
                                    }
                                }
                                ),

                            Select::make('variation_options')
                                ->key('variation_options')
                                ->preload()
                                ->disabled()
                                ->placeholder("choose variation")

                        ])
                        ->visible(function (Forms\Get $get, $record){
                                return MainStore::is_walmart($get('url')) ||
                                    ($record && $record->stores()->where('domain' , 'like' , "%walmart%")->count());
                        }),

                Section::make('Ebay Settings')
                        ->columns(4)
                        ->schema([
                            Forms\Components\Toggle::make('remove_if_sold')
                                ->label("Remove Product If Sold")
                                ->inline(false),
                        ])
                        ->visible(function (Forms\Get $get, $record){
                            return MainStore::is_ebay($get('url')) ||
                                ($record && $record->stores()->where('domain' , 'like' , "%ebay%")->count());
                        }),




            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('name')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')->options(StatusEnum::to_array()),
//                Tables\Columns\TextColumn ::make('stores.name')->formatStateUsing(function ($state){
//                    return Str::of(Str::replace("," , "<br>" , $state))->toHtmlString() ;
//                }),
                Tables\Columns\TextColumn::make('stores.pivot.price')->formatStateUsing(function ($record){
                    return prepare_multiple_prices_in_table($record);
                })->label('Prices'),
                Tables\Columns\TextColumn::make('stores.pivot.notify_price')->formatStateUsing(function ($record){
                    return prepare_multiple_notify_prices_in_table($record);
                }  )->label('Notify at'),

                ToggleIconColumn::make('favourite')
                    ->onIcon("heroicon-s-star")
                    ->offIcon("heroicon-o-star"),
                TextColumn::make('stores.updated_at')->formatStateUsing(function ($record){
                    return prepare_multiple_update_in_table($record);
                })->label('Last Update'),


            ])
            ->defaultSort('favourite' , 'desc')
            ->filters([

                SelectFilter::make('stores')
                    ->relationship('stores_available', 'name')
                    ->preload()
                    ->multiple()
                    ->label('Stores'),

                SelectFilter::make('status')
                    ->options(StatusEnum::to_array())
                    ->preload()
                    ->multiple()
                    ->label('Status'),

                Filter::make('price_notify_price')->query(function ($query){
                    $query->whereHas(
                        'product_store',function($query){
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

            ])->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)->deferLoading(true)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\StoresRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

}
