<?php

declare(strict_types = 1);

namespace Tests\Unit\Repositories;

use App\EInvoiceClient\EInvoice;
use App\EInvoiceClient\EInvoiceClient;
use App\Repositories\EInvoiceRepository;
use Tests\TestCase;
use Mockery;

class EInvoiceRepositoryTest extends TestCase
{
    public function tearDown() : void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testGetEInvoices() : void
    {
        $requestedData = array(
            array(
                "invNum" => "AA00000001",
                "invDate" => "2020/01/01",
                "invTerm" => "10902",
                "randomNumber" => "0001",
                "amount" => "1"
            ),
            array(
                "invNum" => "AA00000002",
                "invDate" => "2020/01/01",
                "invTerm" => "10902",
                "randomNumber" => "0010",
                "amount" => "2"
            ),
            array(
                "invNum" => "AA00000003",
                "invDate" => "2020/01/01",
                "invTerm" => "10902",
                "randomNumber" => "0100"
            )
        );

        $mockedEInvoiceClient = Mockery::mock(EInvoiceClient::class);
        $mockedEInvoiceClient->expects()->getEInvoice(
            "AA00000001",
            "2020/01/01",
            "10902",
            "0001",
            "1"
        )->andReturn(new EInvoice());
        $mockedEInvoiceClient->expects()->getEInvoice(
            "AA00000002",
            "2020/01/01",
            "10902",
            "0010",
            "2"
        )->andReturn(new EInvoice());
        $mockedEInvoiceClient->expects()->getEInvoice(
            "AA00000003",
            "2020/01/01",
            "10902",
            "0100",
            null
        )->andReturn(new EInvoice());

        $repository = new EInvoiceRepository($mockedEInvoiceClient);

        $invoices = $repository->getEInvoices($requestedData);

        $this->assertIsArray($invoices);

        foreach($invoices as $invoice) {
            $this->assertInstanceOf(EInvoice::class, $invoice);
        }
    }

    public function testGetCarriedEInvoices() : void
    {
        $result1 = array(
            array(
                "invNum" => "ER02338051",
                "invDate" => "2012/07/09",
                "cardType" => "3J0002",
                "cardNo"=> "/XCCYDHQ"
            )
        );

        $result2 = array(
            array(
                "invNum" => "ER02338052",
                "invDate" => "2012/08/09",
                "cardType" => "3J0002",
                "cardNo"=> "/XCCYDHQ"
            )
        );

        $mockedEInvoiceClient = Mockery::mock(EInvoiceClient::class);
        $mockedEInvoiceClient->expects()->getCarriedEInvoiceList(
            "2012/07/01",
            "2012/07/31",
            "/XCCYDHQ",
            "0000"
        )->andReturn($result1);
        $mockedEInvoiceClient->expects()->getCarriedEInvoiceList(
            "2012/08/01",
            "2012/08/15",
            "/XCCYDHQ",
            "0000"
        )->andReturn($result2);
        $mockedEInvoiceClient->expects()->getCarriedEInvoice(
            "ER02338051",
            "2012/07/09",
            "/XCCYDHQ",
            "0000"
        )->andReturn(new EInvoice());
        $mockedEInvoiceClient->expects()->getCarriedEInvoice(
            "ER02338052",
            "2012/08/09",
            "/XCCYDHQ",
            "0000"
        )->andReturn(new EInvoice());

        $repository = new EInvoiceRepository($mockedEInvoiceClient);
        $invoices = $repository->getCarriedEInvoices(
            "2012/07/01",
            "2012/08/15",
            "/XCCYDHQ",
            "0000"
        );

        $this->assertIsArray($invoices);

        foreach($invoices as $invoice) {
            $this->assertInstanceOf(EInvoice::class, $invoice);
            $this->assertEquals(array(
                "cardType" => "3J0002",
                "cardNo" => "/XCCYDHQ"
            ), $invoice->note);
        }
    }
}
