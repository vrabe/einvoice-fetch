<?php
require "vendor/autoload.php"; 

use Tests\TestCase;
use App\EInvoiceClient\EInvoiceClient;

class EInvoiceTest extends TestCase
{
    function setUp() : void
    {
        parent::setUp();
    }

    function test()
    {
        $eInvoiceClient = new EInvoiceClient();
        $invoice = $eInvoiceClient->getEInvoice(
            "WX59201746",
            "2019/12/29",
            "10812",
            "1825"
        );

        echo json_encode($invoice);

        echo "\n";
    }
}

$x = new EInvoiceTest();
$x->setUp();
$x->test();