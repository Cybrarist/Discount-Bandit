<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupListResource\Pages;
use App\Filament\Resources\GroupListResource\RelationManagers;
use App\Filament\Resources\GroupListResource\RelationManagers\ProductsRelationManager;
use App\Models\Currency;
use App\Models\GroupList;
use App\Models\Service;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GroupListResource extends Resource
{
    protected static ?string $model = GroupList::class;

    protected static ?string $navigationGroup="Products";
    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name'),
                Forms\Components\Select::make('service_id')
                                        ->relationship('service', 'name')
                                        ->preload()
                                        ->required(),
                Forms\Components\TextInput::make('last_price')
                                            ->hiddenOn(['create'])->disabledOn('edit')->formatStateUsing(function ($record){
                        $currency=$record->service->currency->code ?? "";
                        return ($record->last_price ?? 0) /100 . "$currency";
                    })->disabledOn(['create']),
                Forms\Components\TextInput::make('notify_price')->hiddenOn(['create'])->formatStateUsing(function ($record){
                    return  ($record->notify_price ?? 0) /100 ;
                })->disabledOn(['create']),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('service.name'),
                Tables\Columns\TextColumn::make('last_price')->formatStateUsing(function ($record){
                    $currency=$record->service->currency->code;
                    return $record->last_price/100 . " $currency";
                }),
                Tables\Columns\TextColumn::make('notify_price')->formatStateUsing(function ($record){
                    return $record->notify_price/100 ;
                }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {


        return [
            RelationGroup::make('products',
                [
                    ProductsRelationManager::class,

                ])
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGroupLists::route('/'),
            'create' => Pages\CreateGroupList::route('/create'),
            'edit' => Pages\EditGroupList::route('/{record}/edit'),
        ];
    }


}
