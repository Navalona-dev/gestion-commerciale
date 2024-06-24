<?php
namespace App\Exception;

class TestException extends \Exception
{
    public function __construct($prmMessage = "Mon message", $prmCode = 0, \Exception $prmPreviousException = null) {
        parent::__construct($prmMessage, $prmCode, $prmPreviousException);
    }
}