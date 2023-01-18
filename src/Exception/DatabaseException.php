<?php

namespace Onetoweb\Multivers\Exception;

use Exception;
use Throwable;

class DatabaseException extends Exception
{
    /**
     * @param string $message = 'this function requires a database to be set, use Client::setDatabse'
     * @param int $code = 0
     * @paramThrowable $previous = null
     */
    public function __construct (string $message = 'this function requires a database to be set, use Client::setDatabase', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}