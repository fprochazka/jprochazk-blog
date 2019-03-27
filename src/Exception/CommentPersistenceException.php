<?php


namespace App\Exception;

use Exception;
use Throwable;

class CommentPersistenceException extends Exception
{

    public function __construct(string $message = "persistence_error", int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}