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
            "AC63200542",
            "2020/04/13",
            "10904",
            "5431"
        );

        echo json_encode($invoice);

        echo "\n";
    }
}

$x = new EInvoiceTest();
$x->setUp();
$x->test();