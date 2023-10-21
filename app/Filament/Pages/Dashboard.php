<?php

namespace App\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;

class Dashboard extends \Filament\Pages\Dashboard
{
    // ...
    public function getWidgets(): array
    {
        return [
            AccountWidget::class,
            FilamentInfoWidget::class
        ];
    }



}
