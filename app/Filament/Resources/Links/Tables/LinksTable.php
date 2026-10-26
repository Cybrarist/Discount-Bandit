<?php

namespace App\Filament\Resources\Links\Tables;

use App\Filament\Resources\Links\Pages\ListLinks;
use App\Helpers\LinkHelper;
use App\Http\Controllers\Actions\FetchSingleLinkAction;
use App\Http\Controllers\Actions\Links\AttachLinkToProductAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

class LinksTable
{

    // check if i am calling from product card or from links index page
    public static function configure(Table $table, ?Model $product = null): Table
    {
        return $product
            ? self::configure_product_table($table, $product)
            : self::configure_cards($table);
    }

    private static function configure_product_table(Table $table, Model $product): Table
    {
        return $table
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

                        $link = new AttachLinkToProductAction()->__invoke($product, $data);

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
            ])
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
                        if (Auth::user()->currency_id && Auth::user()->currency->rate) {
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
                        if (Auth::user()->currency_id && Auth::user()->currency->rate) {
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
                        if (Auth::user()->currency_id && Auth::user()->currency->rate) {
                            $price = $price * Auth::user()->currency->rate / $record->store->currency->rate;
                            $code = Auth::user()->currency->code;
                        }

                        return Number::currency($price, $code);
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),

                TextColumn::make('lowest_price')
                    ->label('Lowest')
                    ->formatStateUsing(callback: function ($record) {
                        $price = $record->lowest_price;
                        $code = $record->store->currency->code;
                        if (Auth::user()->currency_id && Auth::user()->currency->rate) {
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
                    ->label('Last updated')
                    ->toggleable()
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Added')
                    ->toggleable()
                    ->dateTime()
                    ->formatStateUsing(fn ($record) => $record->pivot?->created_at),

            ])
            ->filters(self::filters())
            ->recordActions([
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

    private static function configure_cards(Table $table): Table
    {
        Auth::user()->loadMissing('currency:id,code,rate');

        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->with(['store:id,name,image,currency_id,domain,referral,are_params_allowed,allowed_params', 'store.currency:id,code,rate'])
            )
            ->extraAttributes(['class' => 'fi-products-cards'])
            ->columns([
                Stack::make([
                    ViewColumn::make('name')
                        ->view('filament.tables.columns.link-card')
                        ->sortable()
                        ->searchable(),
                ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('Fetch')
                        ->label('Refresh link')
                        ->icon(Heroicon::ArrowPath)
                        ->color('success')
                        ->action(function ($record) {
                            new FetchSingleLinkAction()->__invoke($record);
                            Notification::make()
                                ->title('Link sent to crawler')
                                ->success()
                                ->send();
                        }),

                    EditAction::make(),
                ])
                    ->icon(Heroicon::EllipsisHorizontal)
                    ->color('gray'),
            ])
            ->contentGrid(fn ($livewire): array => $livewire instanceof ListLinks
                ? $livewire->content_grid()
                : ListLinks::DEFAULT_CONTENT_GRID)
            ->filters(self::filters());
    }

    /**
     * @return array<int, SelectFilter|TernaryFilter>
     */
    private static function filters(): array
    {
        return [
            SelectFilter::make('store_id')
                ->relationship('store', 'name')
                ->label('Store')
                ->preload()
                ->searchable()
                ->native(false),

            TernaryFilter::make('is_official')->boolean(),
            TernaryFilter::make('is_in_stock')->boolean(),
        ];
    }
}
