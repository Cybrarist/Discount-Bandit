<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        $users_links = Cache::flexible('users_links', [5, 10], function () {
            return DB::table('link_product')
                ->groupBy('user_id')
                ->selectRaw('COUNT(DISTINCT link_id) as total, user_id')
                ->get()
                ->keyBy('user_id')
                ->toArray();
        });

        $users_products = Cache::flexible('users_products', [5, 10], function () {
            return DB::table('products')
                ->groupBy('user_id')
                ->selectRaw('COUNT(DISTINCT products.id) as total_products, user_id')
                ->get()
                ->keyBy('user_id')
                ->toArray();
        });

        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('role')
                    ->sortable(),

                TextColumn::make('total_product')
                    ->getStateUsing(function ($record) use ($users_products) {
                        return $users_products[$record->id]->total_products ?? 0;
                    })
                ,

                TextColumn::make('total_links')
                    ->label('Total Links')
                    ->getStateUsing(function ($record) use ($users_links) {
                        return $users_links[$record->id]->total ?? 0;
                    }),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
