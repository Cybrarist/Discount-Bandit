<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatusEnum;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Http\Controllers\Actions\FetchAllLinksForProductAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        Auth::user()->loadMissing('currency:id,code,rate');

        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with([
                    'links' => fn ($query) => $query->orderByPrice(),
                    'links.store',
                    'links.store.currency:id,code,rate',
                ]);
            })
            ->extraAttributes(['class' => 'fi-products-cards'])
            ->columns([
                Stack::make([
                    ViewColumn::make('name')
                        ->view('filament.tables.columns.product-card')
                        ->sortable()
                        ->searchable(),
                ]),
            ])

            ->recordActions([
                ActionGroup::make([
                    Action::make('refresh')
                        ->label('Refresh all links')
                        ->icon(Heroicon::ArrowPath)
                        ->color('success')
                        ->action(function ($record) {
                            (new FetchAllLinksForProductAction)->__invoke($record);
                            Notification::make()
                                ->title('All links sent to crawler')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisHorizontal)
                    ->color('gray'),
            ])
            ->contentGrid(fn ($livewire): array => $livewire instanceof ListProducts
                ? $livewire->content_grid()
                : ListProducts::DEFAULT_CONTENT_GRID)
            ->defaultSort('is_favourite', 'desc')

            ->filters([
                SelectFilter::make('category')
                    ->relationship('categories', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),

                SelectFilter::make('status')
                    ->options(ProductStatusEnum::class)
                    ->native(false)
                    ->preload()
                    ->multiple(),

                Filter::make('is_favourite')->query(function (Builder $query) {
                    $query->where('is_favourite');
                })
                    ->label('Favourite product')
                    ->toggle(),

                Filter::make('no_price_update')
                    ->label('No price update')
                    ->schema([
                        TextInput::make('days')
                            ->label('No update in the last (days)')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('e.g. 7'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (blank($data['days'])) {
                            return;
                        }

                        $since = now()->subDays((int) $data['days'])->toDateString();

                        $query->whereHas('links', function (Builder $q) use ($since) {
                            $q->whereDoesntHave('link_histories', function (Builder $q) use ($since) {
                                $q->where('date', '>=', $since);
                            });
                        });

                        $query->with([
                            'links' => fn ($query) => $query
                                ->orderByPrice()
                                ->withExists([
                                    'link_histories as has_price_update_since' => fn ($query) => $query->where('date', '>=', $since),
                                ]),
                        ]);

                    })
                    ->indicateUsing(function (array $data) {
                        if (blank($data['days'])) {
                            return null;
                        }

                        return 'No price update in the last '.$data['days'].' days';
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('refresh_all_links')
                        ->label('Refresh All Links')
                        ->icon(Heroicon::ArrowPath)
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn ($product) => (new FetchAllLinksForProductAction)->__invoke($product));
                            Notification::make()
                                ->title('All links sent to crawler')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
