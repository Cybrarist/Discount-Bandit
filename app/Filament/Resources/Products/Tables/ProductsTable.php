<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatusEnum;
use App\Http\Controllers\Actions\FetchAllLinksForProductAction;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
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
                    'links.link_histories:id,link_id,date',
                ]);
            })
            ->recordUrl(function ($record) {
                return (! $record->name) ? route('filament.admin.resources.products.edit', ['record' => $record]) : null;
            })
            ->columns([
                Grid::make([
                    'lg' => 10,
                ])
                    ->schema([
                        ImageColumn::make('image')
                            ->verticallyAlignCenter()
                            ->alignCenter()
                            ->imageSize('100%')
                            ->extraImgAttributes(['style' => 'max-height:200px; '])
                            ->columnSpan(3)
                            ->url(fn ($record): string => route('filament.admin.resources.products.edit',
                                ['record' => $record])
                            ),

                        Grid::make([
                            'lg' => 8,
                            'md' => 4,
                        ])
                            ->schema([

                                TextColumn::make('name')
                                    ->default("Fetching....")
                                    ->columnSpan(3)
                                    ->alignCenter()
                                    ->searchable()
                                    ->words(10)
                                    ->url(fn ($record): string => route('filament.admin.resources.products.edit',
                                        ['record' => $record])
                                    )
                                    ->sortable(),

                                TextColumn::make('status')
                                    ->columnSpan(2)
                                    ->badge()
                                    ->verticallyAlignCenter()
                                    ->alignEnd()
                                    ->badge(),

                                IconColumn::make('refresh')
                                    ->getStateUsing(fn () => true)
                                    ->columnSpan(1)
                                    ->alignEnd()
                                    ->tooltip('Refresh All Links')
                                    ->icon(Heroicon::ArrowPath)
                                    ->color('success')
                                    ->action(function ($record) {
                                        (new FetchAllLinksForProductAction)->__invoke($record);
                                        Notification::make()
                                            ->title('All links sent to crawler')
                                            ->success()
                                            ->send();
                                    }),

                                IconColumn::make('delete')
                                    ->getStateUsing(fn () => true)
                                    ->columnSpan(1)
                                    ->alignEnd()
                                    ->icon(Heroicon::Trash)
                                    ->color('danger')
                                    ->action(DeleteAction::make()),

                                IconColumn::make('is_favourite')
                                    ->columnSpan(1)
                                    ->alignEnd()
                                    ->icon(fn ($state) => ($state) ? Heroicon::Star : Heroicon::OutlinedStar)
                                    ->color('primary')
                                    ->action(fn ($record) => $record->update(['is_favourite' => ! $record->is_favourite])),

                            ])
                            ->columnSpan(7),

                        Stack::make([

                            Panel::make([
                                ViewColumn::make('links')
                                    ->view('filament.tables.columns.link-prices')
                                    ->columnSpanFull(),
                            ])
                                ->columnSpanFull(),

                        ])
                            ->space(3)
                            ->columnSpanFull(),

                    ]),
            ])
            ->contentGrid([
                'md' => 2,
            ])
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
