<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\RoleEnum;
use App\Filament\Resources\Users\UserResource;
use App\Models\Product;
use App\Models\Link;
use App\Notifications\ProductDiscounted;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()->role === RoleEnum::Admin ||
            Auth::id() == $parameters['record']->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_notification')
                ->action(function ($record) {
                    $product = Product::withoutGlobalScopes()
                        ->where('user_id', $record->id)
                        ->first();


                    if (! $product) {
                        $product = Product::create([
                            'user_id' => $record->id,
                            'name' => "Test Product",
                        ]);
                    }


                    $link = new Link([
                        'price' => 100,
                        'used_price' => 50,
                        'is_in_stock' => true,
                        'shipping_price' => 0,
                        'condition' => "New",
                        'total_reviews' => 100,
                        'rating' => 4.5,
                        'seller' => "Cybrarist",
                        'is_official' => true,
                    ]);

                    $record->notify(new ProductDiscounted(
                        product_id: $product->id,
                        product_name: $product->name,
                        product_image: "https://raw.githubusercontent.com/Cybrarist/Discount-Bandit/refs/heads/master/storage/app/public/bandit.png",
                        store_name: "Discount Bandit",
                        new_link: $link,
                        highest_price: 150,
                        lowest_price: 100,
                        currency_code: "$",
                        notification_reasons: ["test"],
                        product_url: "https://discount-bandit.cybrarist.com",
                    ));

                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
