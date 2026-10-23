<?php

namespace App\Exceptions;

use App\Models\Link;
use Exception;
use Throwable;

class CouldNotHandleBotDetectionException extends Exception
{
    public function __construct(Link $link, ?Throwable $previous = null)
    {
        parent::__construct("could not handle the bot detection for link {$link->id} with store {$link->store?->name}", previous: $previous);
    }
}
