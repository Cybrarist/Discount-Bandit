<?php

namespace App\Exceptions;

use Exception;

class StoreNotFoundException extends Exception
{
    public bool $is_notifiable = true;

    protected $message = "Store is not supported or added to database";
}
