<?php

namespace App\Exceptions;

use App\Models\Link;
use Exception;
use Throwable;

class CouldNotGetProductInformationException extends Exception
{
    public function __construct(Link $link, ?Throwable $previous = null)
    {
        parent::__construct("could not get product information (name, image, reviews, rating, seller) for link {$link->id} with store {$link->store?->name}", previous: $previous);
    }
}
