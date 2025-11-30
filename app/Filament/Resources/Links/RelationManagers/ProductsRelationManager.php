<?php

namespace App\Filament\Resources\Links\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('products.user_id', Auth::id()))
            ->recordTitleAttribute('name')
            ->recordUrl(function ($record) {
                return  route('filament.admin.resources.products.edit', ['record' => $record]);
            })
            ->columns([
                ImageColumn::make('image')
                    ->verticallyAlignCenter()
                    ->alignCenter()
                    ->imageSize('100%')
                    ->extraImgAttributes(['style' => 'max-height:200px; max-width: 200px;'])
                    ->columnSpan(3),

                TextColumn::make('name')
                    ->words(20)
                    ->searchable(),

                TextColumn::make('highest_price')
                    ->color('danger'),

                TextColumn::make('lowest_price')
                    ->color('danger'),


            ])
            ->filters([
                //
            ]);
    }
}
