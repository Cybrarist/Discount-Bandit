<?php

namespace App\Filament\Resources\NotificationSettings\Schemas;

use App\Models\Link;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class NotificationSettingForm
{
    public static function configure(Schema $schema, ?Link $link = null): Schema
    {
        return $schema
            ->components([

                Select::make('product_id')
                    ->label('Product')
                    ->hidden(! $link)
                    ->disabled(! $link)
                    ->options(fn () => $link?->products()
                        ->withoutGlobalScopes()
                        ->where('products.user_id', Auth::id())
                        ->pluck('name', 'products.id'))
                    ->required(),

                TextInput::make('price_desired')
                    ->hint('')
                    ->nullable()
                    ->numeric(),

                TextInput::make('percentage_drop')
                    ->nullable()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step(0.01)
                    ->hintIcon("heroicon-o-information-circle", "Get notified when price drops below specified percentage")
                    ->suffix('%')
                    ->numeric(),

                TextInput::make('extra_costs_amount')
                    ->default(0)
                    ->label('Other amount to consider')
                    ->numeric(),

                TextInput::make('extra_costs_percentage')
                    ->label('Extra price percentage to add')
                    ->default(0)
                    ->step(0.1)
                    ->suffix('%')
                    ->numeric(),

                TextInput::make('price_lowest_in_x_days')
                    ->label("Alert if Product lowest within")
                    ->nullable()
                    ->suffix('days')
                    ->maxValue(65535),

                Toggle::make('is_official')
                    ->label("Official Sellers Only")
                    ->inline(false),

                Toggle::make('is_in_stock')
                    ->label("Alert When Stock Available")
                    ->inline(false),

                Toggle::make('any_price_change')
                    ->label("Alert When Stock Available")
                    ->inline(false),

                Toggle::make('is_shipping_included')
                    ->label("Alert When Stock Available")
                    ->inline(false),

                Textarea::make('description')
                    ->columnSpanFull()
                    ->label('Description'),
            ]);
    }
}
