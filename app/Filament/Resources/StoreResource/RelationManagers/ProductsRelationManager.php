<?php

namespace App\Filament\Resources\StoreResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->recordUrl(
                fn (Model $record): string => route('filament.admin.resources.products.edit', ['record' => $record]),
            )
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')->words(10),
                Tables\Columns\TextColumn::make('updated_at'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
