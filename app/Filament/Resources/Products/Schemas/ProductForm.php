<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->columns(4)
            ->components([
                TextInput::make('name')
                    ->columnSpan(2)
                    ->nullable()
                    ->helperText("if empty, it will be taken crawled url (Amazon has priority)"),

                Select::make('status')
                    ->columnSpan(1)
                    ->options(ProductStatusEnum::class)
                    ->default(ProductStatusEnum::Active->value)
                    ->preload()
                    ->live()
                    ->helperText(fn ($state) => match ($state) {
                        ProductStatusEnum::Active => "Product will be crawled as usual",
                        ProductStatusEnum::Silenced => "Product will be crawled but you won't be notified",
                        ProductStatusEnum::Disabled => "Product will not be crawled",
                    })
                    ->native(false),

                DatePicker::make('notification_settings.snoozed_until')
                    ->columnSpan(1)
                    ->label("Snooze Notification Until"),

                TextInput::make('max_notifications')
                    ->label("Max Notification Sent Daily")
                    ->integer()
                    ->minValue(0)
                    ->numeric()
                    ->placeholder("unlimited")
                    ->hintIcon("heroicon-o-information-circle",
                        "this is for products that fluctuate in price, it won't send any more notification UNLESS the price is less than earlier"),

                Select::make('categories')
                    ->columnSpan(2)
                    ->relationship('categories', 'name')
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        ColorPicker::make('color')->required(),
                    ])
                    ->multiple()
                    ->nullable()
                    ->preload(),

                //                TextInput::make('user_id')
                //                    ->formatStateUsing(fn ($record, $operation) => ($operation == 'create') ?: $record->user->name)
                //                    ->disabledOn([CreateProduct::class])
                //                    ,

                TextInput::make('notifications_sent')
                    ->numeric(),

                Toggle::make('is_favourite')
                    ->onIcon(Heroicon::Star)
                    ->offIcon(Heroicon::Star)
                    ->inline(false)
                    ->label("Add To Favourite"),
            ]);
    }
}
