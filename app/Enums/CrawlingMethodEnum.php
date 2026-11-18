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

    /**
     * Resolve the enum from either an enum instance or its raw value, ignoring
     * anything that is not a valid crawling method.
     */
    public static function fromValue(mixed $value): ?self
    {
        return match (true) {
            $value instanceof self => $value,
            is_string($value)      => self::tryFrom($value),
            default                => null,
        };
    }

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
