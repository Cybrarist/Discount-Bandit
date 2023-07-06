<?php

namespace App\Filament\Resources\GroupListResource\Pages;

use App\Filament\Resources\GroupListResource;
use App\Models\GroupList;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditGroupList extends EditRecord
{
    protected static string $resource = GroupListResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithForm(): bool
    {
        return true;
    }

    public function handleRecordUpdate(Model $record, array $data): Model
    {
        $data['notify_price']=$data['notify_price'] * 100;
        unset($data['last_price']);
        return parent::handleRecordUpdate($record, $data);
    }

}
