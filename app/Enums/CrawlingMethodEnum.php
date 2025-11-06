<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

enum CrawlingMethodEnum: string implements HasColor, HasLabel
{
    case SimpleHttp = 'http';
    case Chromium = 'chromium';

    public function getColor(): string|array|null
    {
        return [
            'admin' => 'primary',
            'user' => 'secondary',
        ];
    }

    public function getLabel(): string|Htmlable|null
    {
        return Str::of($this->name);
    }
}
