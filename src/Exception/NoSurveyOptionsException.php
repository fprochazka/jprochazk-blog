<?php


namespace App\Exception;


use Throwable;

class NoSurveyOptionsException extends \Exception
{
    public function __construct(Throwable $previous = null)
    {
        $message = "Survey has no options";
        $code = 0;
        parent::__construct($message, $code, $previous);
    }
}