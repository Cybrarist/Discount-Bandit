<?php

namespace App\Filament\Resources\GroupResource\Pages;

use App\Filament\Resources\GroupResource;
use App\Helpers\URLHelper;
use App\Models\Product;
use App\Models\ProductStore;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateGroup extends CreateRecord
{
    protected static string $resource = GroupResource::class;

    protected function afterCreate()
    {

    }
}
