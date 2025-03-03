<?php

namespace App\Filament\Resources;

use App\Enums\StatusEnum;
use App\Filament\Resources\StoreResource\Pages;
use App\Filament\Resources\StoreResource\RelationManagers;
use App\Models\Store;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                TextInput::make('name'),

                Select::make('status')
                    ->label('Status')
                    ->hint('This will effect ALL the products that are related to this service')
                    ->options(StatusEnum::class)
                    ->preload(),

                Forms\Components\Section::make('settings')
                    ->columns(4)
                    ->schema([
                        Forms\Components\Toggle::make('tabs')
                            ->inline(false)
                            ->label('Display As Tab on Products'),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->disk('store')
                    ->height(50),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                SelectColumn::make('status')
                    ->options(StatusEnum::class),
                ToggleColumn::make('tabs'),
                TextColumn::make("products_count")
                    ->counts('products')
                    ->label("Total Products"),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(StatusEnum::to_array())
                    ->label('Status')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])->bulkActions([

                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('disable')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            Store::whereIn('id', $records->pluck('id')->toArray())
                                ->update([
                                    'status' => StatusEnum::Disabled,
                                ]);

                            Notification::make()
                                ->title('Stores Disabled Successfully')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\BulkAction::make('enable')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            Store::whereIn('id', $records->pluck('id')->toArray())
                                ->update([
                                    'status' => StatusEnum::Published,
                                ]);

                            Notification::make()
                                ->title('Stores Enabled Successfully')
                                ->success()
                                ->send();
                        }),
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
            'index' => Pages\ListStores::route('/'),
            'create' => Pages\CreateStore::route('/create'),
            'edit' => Pages\EditStore::route('/{record}/edit'),
        ];
    }
}
