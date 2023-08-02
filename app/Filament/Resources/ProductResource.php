<?php

namespace App\Filament\Resources;

use AlperenErsoy\FilamentExport\Actions\FilamentExportBulkAction;
use AlperenErsoy\FilamentExport\FilamentExport;
use App\Enums\StatusEnum;
use App\Filament\Resources\GroupListResource\RelationManagers\ProductsRelationManager;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Resources\ProductResource\Widgets\ProductOverview;
use App\Filament\Widgets\PriceHistoryChart;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use App\Rules\AllowedDomainsRule;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Pages\Actions\CreateAction;
use Filament\Pages\Actions\DeleteAction;
use Filament\Pages\Actions\EditAction;
use Filament\Pages\Page;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Sabberworm\CSS\Rule\Rule;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $label="Product";
    protected static ?string $navigationGroup="Products";
    protected static ?string $navigationIcon = 'heroicon-o-collection';

    protected static ?string $recordTitleAttribute="name";



    public static function form(Form $form): Form
    {


        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->nullable()
                    ->hiddenOn(Pages\CreateProduct::class)
                    ->disabledOn(['create']),

                Forms\Components\TextInput::make('url')
                    ->nullable()
                    ->url()
                    ->unique(table: Product::class , ignorable: fn($record)=>$record)
                    ->rule(new AllowedDomainsRule())
                    ->label('URL of product'),


                Forms\Components\Select::make('status')
                    ->options(StatusEnum::to_array())
                    ->default(StatusEnum::Published)
                    ->preload(),


                Forms\Components\Select::make('categories')
                    ->relationship('categories','name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')->required(),])
                    ->multiple()
                    ->nullable()
                    ->exists('categories','id')
                    ->preload(),

                Forms\Components\Select::make('tags')
                    ->relationship('tags','name')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('name')
                            ->required(),])
                    ->multiple()
                    ->nullable()
                    ->exists('tags','id')
                    ->preload(),


            ]);

    }

    public static function table(Table $table): Table
    {


        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->alignCenter(),
                Tables\Columns\TextColumn::make('name')->limit(50)->searchable()->sortable(),
                Tables\Columns\SelectColumn::make('status')->options(StatusEnum::to_array()),
                Tables\Columns\TextColumn ::make('services.name')->formatStateUsing(function ($state){
                    return Str::of(Str::replace("," , "<br>" , $state))->toHtmlString() ;
                }),
                Tables\Columns\TextColumn::make('services')->formatStateUsing(function ($state){
                     return prepare_multiple_prices_in_table($state);
                })->label('Prices'),
                Tables\Columns\TextColumn::make('Services')->formatStateUsing(function ($state){
                    return prepare_multiple_notify_prices_in_table($state);
                }  )->label('Notify at'),
                TextColumn::make('SErvices')->formatStateUsing(function ($state){
                    return prepare_multiple_update_in_table($state);
                })->label('Last Update'),
                ])
            ->filters([
                Tables\Filters\SelectFilter::make('services')
                                            ->relationship('services', 'name', function ($query)
                                            {
                                                $query->whereIn('status', [StatusEnum::Published , StatusEnum::Silenced]);
                                            })
                                            ->multiple()->label('Services')->indicator('Services'),
                Tables\Filters\SelectFilter::make('categories')->relationship('categories', 'name')->multiple()->label('Categories')->indicator('Categories'),
                Tables\Filters\SelectFilter::make('tags')->relationship('tags', 'name')->multiple()->label('Tags')->indicator('Tags'),
                Tables\Filters\SelectFilter::make('status')->options(StatusEnum::to_array())->label('Status'),
                Tables\Filters\Filter::make('price_notify_price')->query(function ($query){

                    $query->whereHas(
                            'services',function($service_query){
                             $service_query->whereRaw('product_service.price  <= product_service.notify_price');
                        }
                    );

                })->label('Price Met Target')->toggle(),

            ])->filtersLayout(Tables\Filters\Layout::AboveContent)->deferLoading(true)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                FilamentExportBulkAction::make('export')
                    ->fileName('Products')
                    ->csvDelimiter(',')
                    ->withColumns([TextColumn::make('ASIN')])
                ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ServicesRelationManager::class,
                ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }


    protected static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }


    public static function getWidgets(): array
    {
        return [
            PriceHistoryChart::class,
        ];
    }


}
