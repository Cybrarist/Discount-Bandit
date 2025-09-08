<?php

namespace App\Filament\Resources\NotificationSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class NotificationSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('price_desired')
                    ->sortable()
                    ->numeric(),

                TextColumn::make('percentage_drop')
                    ->sortable()
                    ->numeric()
                    ->suffix('%'),

                TextColumn::make('other_costs_amount')
                    ->sortable()
                    ->numeric(),

                TextColumn::make('other_costs_percentage')
                    ->sortable()
                    ->numeric()
                    ->suffix('%'),

                TextColumn::make('price_lowest_in_x_days')
                    ->sortable()
                    ->numeric()
                    ->suffix('days'),
                ToggleColumn::make('is_official')
                    ->sortable()
                    ->disabled()
                    ->alignCenter(),
                ToggleColumn::make('is_in_stock')
                    ->sortable()
                    ->disabled()
                    ->alignCenter(),
                ToggleColumn::make('any_price_change')
                    ->sortable()
                    ->disabled()
                    ->alignCenter(),
                ToggleColumn::make('is_shipping_included')
                    ->sortable()
                    ->disabled()
                    ->alignCenter(),

            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
