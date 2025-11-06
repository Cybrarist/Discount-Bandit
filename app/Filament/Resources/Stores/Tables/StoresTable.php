<?php

namespace App\Filament\Resources\Stores\Tables;

use App\Enums\StoreStatusEnum;
use App\Models\Store;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('currency:id,code'))
            ->columns([
                ImageColumn::make('image')
                    ->disk('store')
                    ->imageHeight(50),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                SelectColumn::make('status')
                    ->options(StoreStatusEnum::class),

                TextColumn::make('currency.code')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(StoreStatusEnum::class)
                    ->label('Status')
                    ->native(false),

                SelectFilter::make('currency_id')
                    ->relationship('currency', 'code')
                    ->label('Currency')
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('disable')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            Store::whereIn('id', $records->pluck('id')->toArray())
                                ->update([
                                    'status' => StoreStatusEnum::Disabled,
                                ]);

                            Notification::make()
                                ->title('Stores Disabled Successfully')
                                ->body('System will be down for few seconds to update')
                                ->success()
                                ->send();
                            Artisan::call('discount:fill-supervisor-workers');

                        }),

                    BulkAction::make('enable')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            Store::whereIn('id', $records->pluck('id')->toArray())
                                ->update([
                                    'status' => StoreStatusEnum::Active,
                                ]);


                            Notification::make()
                                ->title('Stores Enabled Successfully')
                                ->body('System will be down for few seconds to update')
                                ->success()
                                ->send();
                            Artisan::call('discount:fill-supervisor-workers');

                        }),
                ]),
            ]);
    }
}
