<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use App\Models\Store;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditStore extends EditRecord
{
    protected static string $resource = StoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_as_json')
                ->color('success')
                ->icon(Heroicon::ArrowDownOnSquare)
                ->action(function (Store $record) {
                    $final_data = [
                        'name' => $record->name,
                        'domain' => $record->domain,
                        'slug' => $record->slug,
                        'crawling_method' => $record->custom_settings['crawling_method'] ?? null,
                        'timeout' => $record->custom_settings['timeout'] ?? null,
                        'page_event' => $record->custom_settings['page_event'] ?? null,
                        'currency' => $record->currency?->code,
                        'schema' => [
                            'name_schema_key' => $record->custom_settings['name_schema_key'] ?? null,
                            'image_schema_key' => $record->custom_settings['image_schema_key'] ?? null,
                            'total_reviews_schema_key' => $record->custom_settings['total_reviews_schema_key'] ?? null,
                            'rating_schema_key' => $record->custom_settings['rating_schema_key'] ?? null,
                            'price_schema_key' => $record->custom_settings['price_schema_key'] ?? null,
                            'used_price_schema_key' => $record->custom_settings['used_schema_key'] ?? null,
                            'shipping_schema_key' => $record->custom_settings['shipping_schema_key'] ?? null,
                            'stock_schema_key' => $record->custom_settings['stock_schema_key'] ?? null,
                            'condition_schema_key' => $record->custom_settings['condition_schema_key'] ?? null,
                            'seller_schema_key' => $record->custom_settings['seller_schema_key'] ?? null,
                        ],
                        'css' => [
                            'name_selectors' => $record->custom_settings['name_selectors'] ?? null,
                            'image_selectors' => $record->custom_settings['image_selectors'] ?? null,
                            'total_reviews_selectors' => $record->custom_settings['total_reviews_selectors'] ?? null,
                            'rating_selectors' => $record->custom_settings['rating_selectors'] ?? null,
                            'price_selectors' => $record->custom_settings['price_selectors'] ?? null,
                            'used_price_selectors' => $record->custom_settings['used_price_selectors'] ?? null,
                            'shipping_selectors' => $record->custom_settings['shipping_price_selectors'] ?? null,
                            'stock_selectors' => $record->custom_settings['stock_selectors'] ?? null,
                            'in_or_out_of_stock_selector_type' => $record->custom_settings['in_or_out_of_stock_selector_type'] ?? true,
                            'stock_value_to_search' => $record->custom_settings['stock_value_to_search'] ?? true,
                            'condition_selectors' => $record->custom_settings['condition_selectors'] ?? null,
                            'seller_selectors' => $record->custom_settings['seller_selectors'] ?? null,
                        ],

                    ];

                    $filename = "{$record->domain}.json";

                    return response()->streamDownload(function () use ($final_data) {
                        echo json_encode($final_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                    }, $filename, ['Content-Type' => 'application/json']);
                }),

            ViewAction::make(),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
