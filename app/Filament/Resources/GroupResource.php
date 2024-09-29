<?php

namespace App\Filament\Resources;

use App\Classes\GroupHelper;
use App\Classes\MainStore;
use App\Enums\StatusEnum;
use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GroupResource extends Resource
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-m-table-cells';
    protected static ?int $navigationSort=3;

    public static function canAccess(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make("name")
                                            ->string()
                                            ->required()
                                            ->minLength(3),


                Forms\Components\TextInput::make("notify_price")
                                            ->numeric()
                                            ->required(),


                Select::make('status')
                    ->options(StatusEnum::to_array())
                    ->default(StatusEnum::Published)
                    ->preload()
                    ->native(false),

                Select::make('currency_id')
                    ->required()
                    ->relationship("currency", "code")
                    ->preload()
                    ->native(false),

                DatePicker::make('snoozed_until')
                    ->label("Snooze Notification Until"),

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
                    ->hintIcon("heroicon-o-information-circle", "this is for products that fluctuate in price, it won't send any more notification UNLESS the TOTAL price is less than earlier"),


                Section::make('Products Available')
                    ->schema([
                        Repeater::make('products')
                            ->schema([
                                Select::make("product_id")
                                    ->label("Products")
                                    ->multiple()
                                    ->options(function ($record){
                                        if ($record)
                                            return Product::whereNotNull("name")
                                                ->whereNotIn("products.id" ,
                                                \DB::table("group_product")
                                                    ->where("group_id", $record->id)
                                                    ->pluck("product_id")->toArray()
                                            )->pluck("name", "id");
                                        else
                                            return Product::whereNotNull("name")->get()->pluck("name", "id");
                                    })
                                    ->preload()
                                    ->native(false)
                          ,
                                TextInput::make('key')
                                    ->string(),

                            ])
                            ->columns(2)
                    ]),


                Section::make('Links For New Products')
                    ->schema([
                        Repeater::make('url_products')
                            ->schema([
                                TextInput::make("url")
                                ->url(),

                                TextInput::make('key')
                                    ->string(),
                            ])
                            ->nullable()
                            ->defaultItems(0)
                            ->columns(2)
                    ]),






            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make("name"),
                Tables\Columns\TextColumn::make("notify_price"),
                Tables\Columns\TextColumn::make("id")
                    ->label("Price")
                    ->formatStateUsing(function ($record){
                        return GroupHelper::get_current_price($record);
                    }),
                Tables\Columns\TextColumn::make("status")

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            RelationManagers\ProductsRelationManager::class

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
