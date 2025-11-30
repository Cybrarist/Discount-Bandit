<?php

namespace App\Filament\Resources\Links\Tables;

use App\Helpers\LinkHelper;
use App\Http\Controllers\Actions\FetchSingleLinkAction;
use App\Models\Link;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class LinksTable
{
    public static function configure(Table $table, ?Model $product = null): Table
    {
        if ($product) {
            $table
                ->recordUrl(fn ($record) => route('filament.admin.resources.links.edit', ['record' => $record->id]))
                ->headerActions([
                    CreateAction::make('create')
                        ->label('Add link')
                        ->using(function ($data, $action) use ($product) {

                            if (blank($data['store_id'])) {
                                Notification::make()
                                    ->title("Store doesn't exist")
                                    ->body("Please create the store first or check the url")
                                    ->danger()
                                    ->send();

                                $action->halt();
                            }

                            $link = Link::updateOrCreate(Arr::only($data, [
                                'key',
                                'store_id',
                            ]));

                            $link->products()
                                ->syncWithoutDetaching([$product->id => [
                                    'user_id' => Auth::id(),
                                ]]);

                            if ($link->image && ! $product->image)
                                $product->image = $link->image;

                            if ($link->name && ! $product->name)
                                $product->name = $link->name;

                            if ($product->isDirty())
                                $product->save();

                            $link->notification_settings()
                                ->create([
                                    'price_desired' => $data['price_desired'],
                                    'percentage_drop' => $data['percentage_drop'],
                                    'price_lowest_in_x_days' => $data['price_lowest_in_x_days'],
                                    'is_in_stock' => $data['is_in_stock'],
                                    'is_shipping_included' => $data['is_shipping_included'],
                                    'any_price_change' => $data['any_price_change'],
                                    'is_official' => $data['is_official'],
                                    'extra_costs_amount' => $data['extra_costs_amount'],
                                    'extra_costs_percentage' => $data['extra_costs_percentage'],
                                    'user_id' => Auth::id(),
                                    'product_id' => $product->id,
                                ]);

                            $total_notification_settings = $link->notification_settings()->count();
                            if ($total_notification_settings > 1)
                                Notification::make()
                                    ->title("You have multiple notification settings for this link ({$total_notification_settings})")
                                    ->actions([
                                        Action::make('View')
                                            ->url(route('filament.admin.resources.links.edit', ['record' => $link->id])),
                                    ])
                                    ->duration(1000)
                                    ->warning()
                                    ->send();
                        }),
                ]);
        }

        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with(['store:id,name,currency_id,domain,referral,are_params_allowed,allowed_params', 'store.currency:id,code,rate'])
            )
            ->columns([
                ImageColumn::make('image')
                    ->toggleable()
                    ->imageHeight(100),

                TextColumn::make('name')
                    ->sortable()
                    ->wrap()
                    ->width('200px')
                    ->toggleable()
                    ->url(fn ($record) => LinkHelper::get_url($record), true)
                    ->words(10)
                    ->tooltip(fn ($record) => $record->name)
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('store.name')
                    ->url(fn ($record) => route('filament.admin.resources.stores.edit', ['record' => $record->store->id]), true)
                    ->toggleable()
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
                    ->toggleable()
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
                    ->toggleable()
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
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

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
                    })
                    ->toggleable()
                    ->sortable()
                    ->color('success'),

                IconColumn::make('is_in_stock')
                    ->toggleable()
                    ->sortable()
                    ->boolean(),

                IconColumn::make('is_official')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable()
                    ->boolean(),

                TextColumn::make('seller')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rating')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_reviews')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Reviews')
                    ->numeric(),

                TextColumn::make('updated_at')
                    ->toggleable()
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
                    ->visible(! $product?->id)
                    ->iconButton(),

                DetachAction::make()
                    ->iconButton()
                    ->icon(Heroicon::Trash)
                    ->after(fn ($record) => $record->notification_settings()->delete()),

                Action::make('Fetch')
                    ->button()
                    ->icon(Heroicon::ArrowPath)
                    ->iconButton()
                    ->color('success')
                    ->action(fn ($record) => new FetchSingleLinkAction()->__invoke($record)),

            ]);
    }
}
