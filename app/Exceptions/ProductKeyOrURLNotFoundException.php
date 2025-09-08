<?php

namespace App\Exceptions;

use Exception;

class ProductKeyOrURLNotFoundException extends Exception
{
    public bool $is_notifiable = true;

    protected $message = "The system couldn't find a product with the given key or URL.";
}
