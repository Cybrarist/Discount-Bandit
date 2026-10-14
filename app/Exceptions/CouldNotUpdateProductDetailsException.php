<?php

namespace App\Exceptions;

use App\Models\Link;
use Exception;
use Throwable;

class CouldNotUpdateProductDetailsException extends Exception
{
    public function __construct(Link $link, ?Throwable $previous = null)
    {
        parent::__construct("could not update product details (name, image) for link {$link->id} with store {$link->store?->name}", previous: $previous);
    }
}
