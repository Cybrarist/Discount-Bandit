<?php

namespace App\Filament\Resources;

use App\Classes\Amazon;
use App\Enums\StatusEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Archilex\ToggleIconColumn\Columns\ToggleIconColumn;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;


    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';



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
                    ->label('URL of product'),


                Select::make('status')
                    ->options(StatusEnum::to_array())
                    ->default(StatusEnum::Published)
                    ->preload()
                    ->native(false),

                Select::make('categories')
                    ->relationship('categories','name')
                    ->createOptionForm([Forms\Components\TextInput::make('name')->required()])
                    ->multiple()
                    ->nullable()
                    ->exists('categories','id')
                    ->preload(),


                TextInput::make('notify_price')
                        ->nullable()
                        ->numeric(),

                Forms\Components\DatePicker::make('snoozed_until')
                    ->label("Snooze Notification Until"),

                Section::make("Extra Settings")
                    ->columns(4)
                    ->schema([
                        Forms\Components\Toggle::make('favourite')
                            ->label("Add To Favourite")
                            ->inline(false),

                        Forms\Components\Toggle::make('stock')
                            ->label("Alert When Stock Available")
                            ->inline(false),


                        Forms\Components\Toggle::make('only_amazon_seller')
                            ->label("only sold by amazon")
                            ->inline(false),


                        Forms\Components\Toggle::make('variations')
                            ->label("choose other variations to include")
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(function ($component, $get , $state){

                                $variations=[];
                                $parsed_url=parse_url($get('url'));

                                if($state)
                                {
                                    if (is_amazon($parsed_url['host'] ?? "")){
                                        $asin_data=validate_amazon_product($parsed_url ,$parsed_url);
                                        $amazon_product=new Amazon(null , null , $parsed_url );
                                        $variations=$amazon_product->get_variations();
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




                        Select::make('variation_options')
                                ->key('variation_options')
                                ->options([])
                                ->disabled()
                                ->placeholder("choose variation"),


                    ])
                    ->collapsible(),


            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')->alignCenter(),
                Tables\Columns\TextColumn::make('name')->limit(50)->searchable()->sortable(),
                Tables\Columns\SelectColumn::make('status')->options(StatusEnum::to_array()),
                Tables\Columns\TextColumn ::make('stores.name')->formatStateUsing(function ($state){
                    return Str::of(Str::replace("," , "<br>" , $state))->toHtmlString() ;
                }),
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


            ])->defaultSort('favourite' , 'desc')
            ->filters([
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->color(Color::Amber)
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
