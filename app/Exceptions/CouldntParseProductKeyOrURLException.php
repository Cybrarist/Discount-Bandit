<?php

namespace App\Exceptions;

use Exception;

class CouldntParseProductKeyOrURLException extends Exception
{
    public bool $is_notifiable = true;

    protected $message = "The system wasn't able to parse the product key or URL.";
}
