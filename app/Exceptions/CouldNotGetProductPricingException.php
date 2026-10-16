<?php

namespace App\Exceptions;

use App\Models\Link;
use Exception;
use Throwable;

class CouldNotGetProductPricingException extends Exception
{
    public function __construct(Link $link, ?Throwable $previous = null)
    {
        parent::__construct("could not get product pricing (price, used price, stock, shipping, condition) for link {$link->id} with store {$link->store?->name}", previous: $previous);
    }
}
