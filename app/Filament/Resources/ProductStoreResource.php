<?php

namespace App\Filament\Resources;

use App\Enums\StatusEnum;
use App\Filament\Resources\ProductStoreResource\Pages;
use App\Filament\Resources\ProductStoreResource\RelationManagers;
use App\Models\ProductStore;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use function Filament\Support\format_money;

class ProductStoreResource extends Resource
{
    protected static ?string $model = ProductStore::class;
    protected static ?string $label="Single Product Store View";

    protected static ?string $navigationIcon = 'heroicon-m-document';

    protected static ?int $navigationSort=2;
    public static function canCreate() : bool{
     return false;
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Checkbox::make('add_shipping')
                    ->label('Add Shipping Price'),
                TextInput::make('price')
                    ->disabled()
                    ->dehydrated(false)
                    ->label('Current Price')
                    ->prefix(function ($record){
                        return get_currencies($record->store->currency_id);
                    }),

                TextInput::make('notify_price')
                    ->numeric()
                    ->integer()
                    ->label('Notify when cheaper than')
                    ->prefix(function ($record){
                        return get_currencies($record->store->currency_id);
                    }),
            ]);

    }

    public static function table(Table $table): Table
    {

        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.image')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('product.name')
                    ->searchable()
                    ->words(5)
                    ->url( function ($record) {
                        return route('filament.admin.resources.products.edit', $record->product_id);
                    } ,true)
                    ,
                Tables\Columns\TextColumn::make('store.name')
                    ->words(5)
                    ->color("warning")
                    ->url( function ($record) {
                        return route('filament.admin.resources.stores.edit', $record->store_id);
                    } ,true)
                ,
                Tables\Columns\TextColumn::make('price')
                    ->formatStateUsing(function ($record){
                    return prepare_single_prices_in_table($record->price,$record->store->currency_id, true,$record->notify_price );
                }),
                Tables\Columns\TextColumn::make('notify_price')
                    ->formatStateUsing(function ($record){
                    return prepare_single_prices_in_table($record->notify_price,$record->store->currency_id );
                }),
                Tables\Columns\TextColumn::make('shipping_price')
                    ->formatStateUsing(function ($record){
                    return prepare_single_prices_in_table($record->shipping_price,$record->store->currency_id );
                }),
                Tables\Columns\TextColumn::make('rate'),
                Tables\Columns\TextColumn::make('updated_at'),
                Tables\Columns\TextColumn::make('number_of_rates'),
                Tables\Columns\TextColumn::make('seller'),

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


            ])->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->deferLoading(true)
            ->actions([
            ])
            ->bulkActions([
            ])
            ->emptyStateActions([
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
