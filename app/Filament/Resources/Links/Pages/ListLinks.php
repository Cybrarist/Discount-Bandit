<?php

namespace App\Filament\Resources\Links\Pages;

use App\Filament\Resources\Links\LinkResource;
use Asmit\ResizedColumn\HasResizableColumn;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLinks extends ListRecords
{

    use HasResizableColumn;
    protected static string $resource = LinkResource::class;


    public function setPage($page, $pageName = 'page'): void
    {
        parent::setPage($page, $pageName);

        $this->dispatch('scroll-to-top');
    }

}
