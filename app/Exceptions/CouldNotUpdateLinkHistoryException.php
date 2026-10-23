<?php

namespace App\Exceptions;

use App\Models\Link;
use Exception;
use Throwable;

class CouldNotUpdateLinkHistoryException extends Exception
{
    public function __construct(Link $link, ?Throwable $previous = null)
    {
        parent::__construct("could not update link history for link {$link->id} with store {$link->store?->name}", previous: $previous);
    }
}
