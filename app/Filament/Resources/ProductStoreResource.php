<?php

namespace App\Filament\Resources;

use App\Enums\StatusEnum;
use App\Filament\Resources\ProductStoreResource\Pages;
use App\Helpers\CurrencyHelper;
use App\Helpers\StoreHelper;
use App\Models\ProductStore;
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class ProductStoreResource extends Resource
{
    protected static ?string $model = ProductStore::class;
    protected static ?string $label="Single Product Store View";

    protected static ?string $navigationIcon = 'heroicon-m-document';

    protected static ?int $navigationSort=2;
    public static function canCreate() : bool {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                TextInput::make('price')
                    ->disabled()
                    ->dehydrated(false)
                    ->label('Current Price')
                    ->prefix(function ($record){
                        return  CurrencyHelper::get_currencies($record->store->currency_id);
                    }),

                TextInput::make('notify_price')
                    ->numeric()
                    ->integer()
                    ->label('Notify when cheaper than')
                    ->prefix(function ($record){
                        return  CurrencyHelper::get_currencies($record->store->currency_id);
                    }),
                Forms\Components\Toggle::make('add_shipping')
                    ->label('Add Shipping Price'),
            ])->columns(3);

    }

    public static function table(Table $table): Table
    {

        $stores=StoreHelper::get_stores_with_active_products();
        $currencies=CurrencyHelper::get_currencies();

        return $table
            ->modifyQueryUsing(function ($query){
                $query->with([
                    "product:id,name,image,status,favourite",
                ]);
            })
            ->recordAction(ViewAction::class)
            ->columns([

                ImageColumn::make('product.image')->alignCenter(),

                TextColumn::make('product.name')
                    ->words(5)
                    ->limit(50)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product.status')
                    ->badge()
                    ->color(fn ($state) => StatusEnum::get_badge($state)),

                TextColumn::make('store_id')
                    ->formatStateUsing(function ($state) use($stores){
                        return $stores[$state]["name"];
                    })
                    ->listWithLineBreaks(),

                TextColumn::make('price')
                    ->formatStateUsing(function ($record) use ($currencies, $stores) {
                        $color_string=($record->price <= $record->notify_price) ? "green" :"red";

                        return Str::of("<p  style='color:$color_string'>" .
                            $currencies[$stores[$record->store_id]["currency_id"]].
                            \Illuminate\Support\Number::format($record->price   , maxPrecision: 2) .
                            "</p>")->toHtmlString();
                    })->label('Prices'),

                TextColumn::make('notify_price')
                    ->formatStateUsing(function ($record) use ($currencies, $stores) {
                         return Str::of("<p>" .
                            $currencies[$stores[$record->store_id]["currency_id"]].
                            \Illuminate\Support\Number::format($record->notify_price   , maxPrecision: 2) .
                            "</p>")->toHtmlString();
                    })
                    ->label('Notify Price'),


                TextColumn::make('highest_price')
                    ->label("Highest Price")
                    ->formatStateUsing(function ($record) use ($currencies, $stores) {
                        return $currencies[$stores[$record->store_id]["currency_id"]]. Number::format($record->highest_price, 2);
                    })
                    ->listWithLineBreaks()
                    ->color('danger'),

                TextColumn::make('lowest_price')
                    ->label("Lowest Price")
                    ->formatStateUsing(function ($record) use ($currencies, $stores) {
                        return $currencies[$stores[$record->store_id]["currency_id"]]. Number::format($record->lowest_price, 2);
                    })
                    ->listWithLineBreaks()
                    ->color('success'),

                ToggleIconColumn::make('product.favourite')
                    ->onIcon("heroicon-s-star")
                    ->offIcon("heroicon-o-star"),

                TextColumn::make('updated_at')
                    ->listWithLineBreaks()
                    ->label('Last Update'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('stores')
                    ->relationship('stores_available', 'name')
                    ->preload()
                    ->multiple()
                    ->label('Stores'),

                Filter::make('price_notify_price')->query(function (Builder $query) {
                        return $query->whereRaw('price <= notify_price');
                            })
                    ->label('Price Met Target')
                    ->toggle(),

                Filter::make('favourite')->query(function (Builder $query) {
                    $query->whereHas('product', function ($query2){
                        $query2->where('favourite', 1);
                    });})
                    ->label('Favourite product')
                    ->toggle(),

            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductStores::route('/'),
            'create' => Pages\CreateProductStore::route('/create'),
            'edit' => Pages\EditProductStore::route('/{record}/edit'),
        ];
    }
}
