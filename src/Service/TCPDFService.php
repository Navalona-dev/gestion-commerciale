<?php

namespace App\Service;

use TCPDF;

class TCPDFService extends TCPDF
{
    public function __construct()
    {
        parent::__construct();
    }
}
