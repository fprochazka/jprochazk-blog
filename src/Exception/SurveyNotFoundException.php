<?php


namespace App\Exception;


use Throwable;

class SurveyNotFoundException extends \Exception
{

    public function __construct($message = "Could not find Survey", $code = 404, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}