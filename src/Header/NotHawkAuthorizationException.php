<?php

namespace Dflydev\Hawk\Header;

use Exception;

class NotHawkAuthorizationException extends Exception
{
    public function __construct()
    {
        parent::__construct("Field value does not start with Hawk");
    }
}
