<?php

namespace App\Filament\Resources\ProductLinks\Schemas;

use App\Filament\Resources\NotificationSettings\Schemas\NotificationSettingForm;
use App\Models\Product;
use App\Services\URLParserService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;

class ProductLinkForm
{
    public static function configure(Schema $schema, ?Product $product = null): Schema
    {
        return $schema
            ->components([
                Hidden::make('store_id'),
                Hidden::make('key'),

                Select::make('product_id')
                    ->relationship('product', 'name')
                    ->disabled(fn () => $product?->id)
                    ->default($product?->id)
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('url')
                    ->required(fn ($operation) => $operation == "create")
                    ->hiddenOn([Operation::Edit, Operation::View])
                    ->autofocus()
                    ->url()
                    ->label('URL of product')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $set, URLParserService $service) {
                        if (! $state) {
                            return;
                        }
                        $service->setup($state);
                        if ($service->store?->id) {
                            $set('store_id', $service->store->id);
                            $set('key', $service->product_key);
                        }
                    }),

                Section::make()
                    ->hiddenOn([Operation::Edit])
                    ->label('Notification Settings ( you can add more later on )')
                    ->columnSpanFull()
                    ->columns(3)
                    ->schema([
                        ...NotificationSettingForm::configure($schema)->getComponents(),
                    ]),
            ]);
    }
}
