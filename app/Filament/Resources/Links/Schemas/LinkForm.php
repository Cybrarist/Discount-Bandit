<?php

namespace App\Filament\Resources\Links\Schemas;

use App\Filament\Resources\NotificationSettings\Schemas\NotificationSettingForm;
use App\Services\URLParserService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Model;

class LinkForm
{
    public static function configure(Schema $schema, ?Model $product = null): Schema
    {
        return $schema
            ->components([
                Hidden::make('store_id'),
                Hidden::make('key'),

                Section::make()
                    ->schema([
                        Section::make()
                            ->contained(false)
                            ->columnSpan(6)
                            ->schema([

                                TextInput::make('name')
                                    ->hiddenOn([Operation::Create]),

                            ]),
                        Section::make()
                            ->contained(false)
                            ->columnSpan(6)
                            ->columns()
                            ->schema([
                                TextInput::make('price')
                                    ->numeric(),
                                TextInput::make('used_price')
                                    ->numeric(),
                                TextInput::make('seller'),
                                TextInput::make('shipping_price')
                                    ->numeric(),

                                TextInput::make('rating')
                                    ->numeric(),
                                TextInput::make('total_reviews')
                                    ->numeric(),
                                TextInput::make('highest_price')
                                    ->numeric(),
                                TextInput::make('lowest_price')
                                    ->readOnly()
                                    ->numeric(),

                            ]),

                    ])
                    ->disabled()
                    ->columns(12)
                    ->columnSpanFull()
                    ->hiddenOn([Operation::Create]),

                TextInput::make('url')
                    ->required(fn ($operation) => $operation == Operation::Create->value)
                    ->hiddenOn([Operation::Edit, Operation::View])
                    ->autofocus()
                    ->url()
                    ->label('URL of product')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $set, URLParserService $service) {
                        try {
                            $service->setup($state);
                            if ($service->store?->id) {
                                $set('store_id', $service->store->id);
                                $set('key', $service->product_key);
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title("Error")
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
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
