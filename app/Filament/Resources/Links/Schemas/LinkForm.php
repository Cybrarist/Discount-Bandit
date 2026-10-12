<?php

namespace App\Filament\Resources\Links\Schemas;

use App\Filament\Resources\Links\LinkResource;
use App\Filament\Resources\NotificationSettings\Schemas\NotificationSettingForm;
use App\Models\Link;
use App\Services\URLParserService;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Operation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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
                    ->live(debounce: 300)
                    ->afterStateUpdated(function ($state, $set, URLParserService $service) {
                        try {
                            $service->setup($state);
                            if ($service->store?->id) {
                                $set('store_id', $service->store->id);
                                $set('key', $service->product_key);

                                $existingLink = Link::withoutGlobalScopes()
                                    ->where('store_id', $service->store->id)
                                    ->where('key', $service->product_key)
                                    ->whereExists(fn ($query) => $query->selectRaw(1)
                                        ->from('link_product')
                                        ->whereColumn('link_product.link_id', 'links.id')
                                        ->where('link_product.user_id', Auth::id()))
                                    ->first();

                                if ($existingLink) {
                                    Notification::make()
                                        ->title('Link already exists')
                                        ->body('This link is already being tracked for this store on another product.')
                                        ->warning()
                                        ->actions([
                                            Action::make('openExistingLink')
                                                ->label('Open the current link from here')
                                                ->url(LinkResource::getUrl('edit', ['record' => $existingLink]), shouldOpenInNewTab: true),
                                        ])
                                        ->send();
                                }
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
