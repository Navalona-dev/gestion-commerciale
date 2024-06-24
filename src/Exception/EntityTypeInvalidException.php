<?php
namespace App\Exception;

class EntityTypeInvalidException extends \Exception
{
    public function __construct($prmMessage, $prmCode = 0, \Exception $prmPreviousException = null) {
        parent::__construct($prmMessage, $prmCode, $prmPreviousException);
    }
}