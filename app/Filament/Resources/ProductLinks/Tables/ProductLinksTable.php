<?php

namespace App\Filament\Resources\ProductLinks\Tables;

use App\Helpers\ProductHelper;
use App\Http\Controllers\Actions\FetchSingleLinkAction;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class ProductLinksTable
{
    public static function configure(Table $table, $relation = false): Table
    {
        if ($relation) {
            $table
                ->recordUrl(fn ($record) => route('filament.admin.resources.product-links.edit', ['record' => $record->id]))
                ->headerActions([
                    CreateAction::make()
                        ->label('Add Link')
                        ->mutateDataUsing(function (array $data): array {
                            $data['user_id'] = Auth::id();

                            return $data;
                        })
                        ->after(function ($record, $data) {
                            $record->notification_settings()
                                ->create([
                                    'price_desired' => $data['price_desired'],
                                    'percentage_drop' => $data['percentage_drop'],
                                    'price_lowest_in_x_days' => $data['price_lowest_in_x_days'],
                                    'is_in_stock' => $data['is_in_stock'],
                                    'is_shipping_included' => $data['is_shipping_included'],
                                    'any_price_change' => $data['any_price_change'],
                                    'is_official' => $data['is_official'],
                                    'product_link_id' => $record->id,
                                    'user_id' => Auth::id(),
                                ]);
                        }),
                ]);
        }

        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['product:id,name', 'store:id,name,currency_id,domain,referral', 'store.currency:id,code,rate']))
            ->columns([
                ImageColumn::make('image')
                    ->imageHeight(100),

                TextColumn::make('name')
                    ->url(fn ($record) => ProductHelper::get_url($record), true)
                    ->words(5)
                    ->tooltip(fn ($record) => $record->name)
                    ->description(fn ($record) => $record->product->name)
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('store.name')
                    ->url(fn ($record) => route('filament.admin.resources.stores.edit', ['record' => $record->product->id]), true)
                    ->color('primary'),

                TextColumn::make('highest_price')
                    ->label('Highest')
                    ->formatStateUsing(function ($record) {
                        $price = $record->highest_price;
                        $code = $record->store->currency->code;
                        if (Auth::user()->currency_id) {
                            $price = $price * Auth::user()->currency->rate / $record->store->currency->rate;
                            $code = Auth::user()->currency->code;
                        }

                        return Number::currency($price, $code);
                    })
                    ->color('danger')
                    ->sortable(),

                TextColumn::make('price')
                    ->formatStateUsing(function ($record) {
                        $price = $record->price;
                        $code = $record->store->currency->code;
                        if (Auth::user()->currency_id) {
                            $price = $price * Auth::user()->currency->rate / $record->store->currency->rate;
                            $code = Auth::user()->currency->code;
                        }

                        return Number::currency($price, $code);
                    })
                    ->sortable(),

                TextColumn::make('used_price')
                    ->label('Used')
                    ->formatStateUsing(function ($record) {
                        $price = $record->used_price;
                        $code = $record->store->currency->code;
                        if (Auth::user()->currency_id) {
                            $price = $price * Auth::user()->currency->rate / $record->store->currency->rate;
                            $code = Auth::user()->currency->code;
                        }

                        return Number::currency($price, $code);
                    })->sortable(),

                TextColumn::make('lowest_price')
                    ->label('Lowest')
                    ->formatStateUsing(function ($record) {
                        $price = $record->lowest_price;
                        $code = $record->store->currency->code;
                        if (Auth::user()->currency_id) {
                            $price = $price * Auth::user()->currency->rate / $record->store->currency->rate;
                            $code = Auth::user()->currency->code;
                        }

                        return Number::currency($price, $code);
                    })->sortable()
                    ->color('success'),

                IconColumn::make('is_in_stock')
                    ->sortable()
                    ->boolean(),

                IconColumn::make('is_official')
                    ->sortable()
                    ->boolean(),

                TextColumn::make('rating'),
                TextColumn::make('total_reviews')
                    ->label('Reviews')
                    ->numeric(),

                TextColumn::make('updated_at')
                ->dateTime()
                ->sortable(),

            ])
            ->filters([
                SelectFilter::make('store_id')
                    ->relationship('store', 'name')
                    ->label('Store')
                    ->preload()
                    ->searchable()
                    ->native(false),

                TernaryFilter::make('is_official')->boolean(),
                TernaryFilter::make('is_in_stock')->boolean(),

            ])
            ->recordActions([
                EditAction::make()
                    ->hidden($relation)
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton()
                    ->before(fn ($record) => $record->notification_settings()->delete()),

                Action::make('Fetch')
                    ->button()
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton()
                    ->color('success')
                    ->action(fn ($record) => new FetchSingleLinkAction()->__invoke($record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
