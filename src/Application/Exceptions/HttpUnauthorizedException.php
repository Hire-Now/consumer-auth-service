<?php

namespace App\Application\Exceptions;

class HttpUnauthorizedException extends \Exception
{
    protected $code = 401;
    protected $message = "Unauthorized access.";
}
