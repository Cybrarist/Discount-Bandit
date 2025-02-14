<?php

namespace App\Filament\Resources;

use App\Enums\StatusEnum;
use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Helpers\StoreHelper;
use App\Models\Group;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-m-table-cells';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make("name")
                    ->string()
                    ->required(),

                Forms\Components\TextInput::make("notify_price")
                    ->numeric()
                    ->required(),

                TextInput::make('notify_percentage')
                    ->nullable()
                    ->hintIcon("heroicon-o-information-circle",
                        "Get notified when price drops below specified percentage")
                    ->suffix('%')
                    ->numeric(),

                Select::make('status')
                    ->options(StatusEnum::to_array())
                    ->default(StatusEnum::Published)
                    ->preload()
                    ->native(false),

                Select::make('currency_id')
                    ->required()
                    ->relationship("currency", "code")
                    ->preload()
                    ->default(2)
                    ->native(false),

                DatePicker::make('snoozed_until')
                    ->label("Snooze Notification Until"),

                Forms\Components\TextInput::make('lowest_within')
                    ->label("Alert if Group lowest within")
                    ->nullable()
                    ->suffix('days')
                    ->maxValue(65535),

                TextInput::make('max_notifications')
                    ->label("Max Notification Sent Daily")
                    ->integer()
                    ->numeric()
                    ->placeholder("unlimited")
                    ->hintIcon("heroicon-o-information-circle", "this is for products that fluctuate in price, it won't send any more notification UNLESS the TOTAL price is less than earlier"),

                Section::make('Products Available')
                    ->schema([
                        Repeater::make('products_available')
                            ->schema([
                                Select::make("product_id")
                                    ->label("Products")
                                    ->multiple()
                                    ->options(function ($record, $get, $state, $component) {

                                        $stores_for_the_same_group_currency = StoreHelper::get_stores_with_same_currency($get('../../currency_id'));

                                        // remove the current selected products

                                        $current_parent_id = $component->getContainer()->getStatePath(false);

                                        // get the available values across the repeater
                                        $all_products_across_fields = Arr::pluck(
                                            Arr::except($get('../../products_available'),
                                                $current_parent_id), 'product_id'
                                        );

                                        // combine the values into one array
                                        $all_products_across_fields = Arr::collapse($all_products_across_fields);

                                        $available_products = Product::whereNotNull("name")
                                            ->join('product_store', 'product_store.product_id', '=', 'products.id')
                                            ->whereIn('store_id', $stores_for_the_same_group_currency->pluck('id')->toArray())
                                            ->whereNotIn("products.id", $all_products_across_fields);

                                        if ($record) {
                                            $available_products->whereNotIn("products.id",
                                                \DB::table("group_product")
                                                    ->where("group_id", $record->id)
                                                    ->pluck("product_id")
                                                    ->toArray()
                                            );
                                        }

                                        return $available_products->pluck("products.name", "products.id");
                                    })
                                    ->live()
                                    ->native(false),

                                TextInput::make('key')
                                    ->string(),

                            ])
                            ->columns(2),
                    ]),

                Section::make('Links For New Products')
                    ->schema([
                        Repeater::make('url_products')
                            ->schema([
                                TextInput::make("url")
                                    ->url()
                                    ->distinct(),

                                TextInput::make('key')
                                    ->string(),
                            ])
                            ->defaultItems(0)
                            ->columns(2),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name"),
                Tables\Columns\TextColumn::make("current_price"),
                Tables\Columns\TextColumn::make("highest_price"),
                Tables\Columns\TextColumn::make("lowest_price"),
                Tables\Columns\TextColumn::make("notify_price"),
                Tables\Columns\TextColumn::make("status"),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->button(),
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
            RelationManagers\ProductsRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
