<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

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
                    ->helperText("if empty, it will be taken from first crawled link"),

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

                TextInput::make('remove_link_if_out_of_stock_for_x_days')
                    ->integer()
                    ->minValue(0)
                    ->numeric()
                    ->placeholder("unlimited")
                    ->hintIcon("heroicon-o-information-circle",
                        "this will remove the link from the database if the product is out of stock for x days to free space for other links.  notification will be sent about the link removed"),

                Select::make('categories')
                    ->columnSpan(2)
                    ->relationship('categories', 'name')
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                        ColorPicker::make('color')->required(),
                    ])
                    ->createOptionUsing(function (array $data): int {
                        return Auth::user()->categories()->create($data)->getKey();
                    })
                    ->multiple()
                    ->nullable()
                    ->preload(),

                TextInput::make('notifications_sent')
                    ->hiddenOn([Operation::Create])
                    ->numeric(),

                Toggle::make('is_favourite')
                    ->onIcon(Heroicon::Star)
                    ->offIcon(Heroicon::Star)
                    ->inline(false)
                    ->label("Add To Favourite"),
            ]);
    }
}
