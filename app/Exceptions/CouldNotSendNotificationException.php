<?php

namespace App\Exceptions;

use App\Models\Link;
use Exception;
use Throwable;

class CouldNotSendNotificationException extends Exception
{
    public function __construct(Link $link, ?Throwable $previous = null)
    {
        parent::__construct("could not send notifications or update the link for link {$link->id} with store {$link->store?->name}", previous: $previous);
    }
}
