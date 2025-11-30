<?php

namespace App\Filament\Resources\Products\Tables;

use App\Enums\ProductStatusEnum;
use App\Helpers\LinkHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        Auth::user()->loadMissing('currency:id,code,rate');

        return $table
            ->modifyQueryUsing(function ($query) {
                $query->with([
                    'links',
                    'links.store',
                    'links.store.currency:id,code,rate',
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
                                    ->columnSpan(4)
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
                                Grid::make([
                                    'lg' => 8,
                                    'sm' => 1,
                                ])->schema([
                                    TextColumn::make('links')
                                        ->color('primary')
                                        ->formatStateUsing(function ($state) {
                                            $link = LinkHelper::get_url($state);

                                            return new HtmlString("<a class='underline text-primary-400' href='{$link}' target='_blank'>{$state->store->name}</a>");
                                        })
                                        ->columnSpan([
                                            'md' => 2,
                                            'sm' => 7,
                                        ])
                                        ->html()
                                        ->listWithLineBreaks(),

                                    TextColumn::make('links')
                                        ->formatStateUsing(function ($state) {
                                            $price = $state->highest_price;
                                            if (Auth::user()->currency_id) {
                                                $price = $price * Auth::user()->currency->rate / $state->store->currency->rate;
                                            }

                                            return Number::format($price);
                                        })
                                        ->listWithLineBreaks()
                                        ->columnSpan([
                                            'md' => 2,
                                            'sm' => 7,
                                        ])
                                        ->color('danger'),

                                    TextColumn::make('links')
                                        ->formatStateUsing(function ($state) {
                                            $price = $state->price;
                                            $code = $state->store->currency->code;
                                            if (Auth::user()->currency_id) {
                                                $price = $price * Auth::user()->currency->rate / $state->store->currency->rate;
                                                $code = Auth::user()->currency->code;
                                            }
                                            return Number::currency($price, $code);

                                        })
                                        ->columnSpan([
                                            'md' => 2,
                                            'sm' => 2,
                                        ])
                                        ->listWithLineBreaks(),

                                    TextColumn::make('links')
                                        ->columnSpan([
                                            'md' => 2,
                                            'sm' => 2,
                                        ])
                                        ->listWithLineBreaks()
                                        ->color('success')
                                        ->formatStateUsing(function ($state) {
                                            $price = $state->lowest_price;
                                            if (Auth::user()->currency_id) {
                                                $price = $price * Auth::user()->currency->rate / $state->store->currency->rate;
                                            }
                                            return Number::format($price);
                                        }),

                                ]),
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

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
