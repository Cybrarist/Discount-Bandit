<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Filament\Widgets\PriceHistoryChart;
use App\Jobs\GetProductJob;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Log;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('Fetch')->color('primary')->action(function ($record){
                try {
                    $services=$this->record->services;
                    foreach ($services as $service)
                        GetProductJob::dispatch(
                            $this->record->id,
                            $service->id,
                            $service->currency->code,
                            $service->pivot->notify_price ,
                            $service->pivot->price )->delay(Carbon::now()->addSeconds(10));

                    Notification::make()
                        ->title('Added To Fetching Jobs')
                        ->success()
                        ->send();
                }
                catch ( \Exception $e){
                    Log::error("Couldn't fetch the job with error : $e" );
                    Notification::make()
                        ->title("Couldn't fetch the product, refer to logs")
                        ->danger()
                        ->send();
                }
            }),

        ];
    }


//    protected function getFooterWidgets(): array
//    {
//        if ($this->getRecord()->services()->count() )
//            return [
//                PriceHistoryChart::class
//            ];
//        return [];
//
//    }


}
