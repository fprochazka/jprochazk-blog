<?php


namespace App\Exception;

use Exception;
use Throwable;

class CommentPersistenceException extends Exception
{

    public function __construct($message = "persistence_error", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}