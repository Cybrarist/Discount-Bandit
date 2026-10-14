<?php

namespace App\Exceptions;

use App\Models\Link;
use Exception;
use Throwable;

class CouldNotCrawlProductException extends Exception
{
    public function __construct(Link $link, ?Throwable $previous = null)
    {
        parent::__construct("could not crawl the url for link {$link->id} with store {$link->store?->name}", previous: $previous);
    }
}
