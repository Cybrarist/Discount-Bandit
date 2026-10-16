<?php

namespace App\Exceptions;

use App\Models\Link;
use Exception;
use Throwable;

class CouldNotRunBeforeCrawlException extends Exception
{
    public function __construct(Link $link, ?Throwable $previous = null)
    {
        parent::__construct("could not run the before crawl step for link {$link->id} with store {$link->store?->name}", previous: $previous);
    }
}
