<?php

namespace Asil\Otus\HomeTask_2\Exceptions;

use Throwable;

class SocketException extends \Exception
{
    public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
    {
        $message .= socket_strerror(socket_last_error());
        parent::__construct($message, $code, $previous);
    }

}