<?php

namespace App\Filament\Resources\NotificationSettings\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class NotificationSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
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


                TextInput::make('other_costs_amount')
                    ->label('Other amount to consider')
                    ->numeric(),

                TextInput::make('other_costs_percentage')
                    ->label('Extra price percentage to add')
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
