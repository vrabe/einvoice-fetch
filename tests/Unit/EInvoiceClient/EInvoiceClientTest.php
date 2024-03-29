<?php

declare(strict_types = 1);

namespace Tests\Unit\EInvoiceClient;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use App\EInvoiceClient\EInvoiceClient;
use App\Exceptions\EInvoiceResponseException;
use Tests\TestCase;

class EInvoiceClientTest extends TestCase
{
    public function testGetEInvoice(): void
    {
        $handle = fopen(__dir__ . "/EInvoiceTestInput01.json", "r");
        $sucessResult = fread($handle, filesize(__dir__ . "/EInvoiceTestInput01.json"));
        fclose($handle);

        $failResult = '{"v":"0.5","code":904,"msg":"錯誤的查詢種類"}';

        $mock = new MockHandler([
            new Response(200, [], $sucessResult),
            new Response(200, [], $sucessResult),
            new Response(200, [], $failResult),
            new Response(200, [], "<html></html>"),
            new Response(503)
        ]);
        $mockHttpClient = new Client(["handler" => HandlerStack::create($mock)]);
        $eInvoiceClient = new EInvoiceClient($mockHttpClient);

        // Normal case
        $invoice = $eInvoiceClient->getEInvoice(
            "MH25570631",
            "2019/01/19",
            "10802",
            "2355"
        );
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/EInvoiceTestOutput01.json",
            json_encode($invoice)
        );

        // Normal case with additional amount
        $invoice = $eInvoiceClient->getEInvoice(
            "MH25570631",
            "2019/01/19",
            "10802",
            "2355",
            "50"
        );
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/EInvoiceTestOutput01.json",
            json_encode($invoice)
        );

        // E-Invoice system error 1
        $this->expectException(EInvoiceResponseException::class);
        $invoice = $eInvoiceClient->getEInvoice(
            "MH25570631",
            "2019/01/19",
            "10802",
            "2355"
        );

        // E-Invoice system error 2
        $this->expectException(EInvoiceResponseException::class);
        $invoice = $eInvoiceClient->getEInvoice(
            "MH25570631",
            "2019/01/19",
            "10802",
            "2355"
        );

        // Server error
        $this->expectException(RequestException::class);
        $invoice = $eInvoiceClient->getEInvoice(
            "MH25570631",
            "2019/01/19",
            "10802",
            "2355"
        );
    }

    public function testGetEInvoiceByQRCode(): void
    {
        $handle = fopen(__dir__ . "/EInvoiceTestInput01.json", "r");
        $sucessResult = fread($handle, filesize(__dir__ . "/EInvoiceTestInput01.json"));
        fclose($handle);

        $failResult = '{"v":"0.5","code":904,"msg":"錯誤的查詢種類"}';

        $mock = new MockHandler([
            new Response(200, [], $sucessResult),
            new Response(200, [], $sucessResult),
            new Response(200, [], $failResult),
            new Response(200, [], "<html></html>"),
            new Response(503)
        ]);
        $mockHttpClient = new Client(["handler" => HandlerStack::create($mock)]);
        $eInvoiceClient = new EInvoiceClient($mockHttpClient);

        // Normal case
        $invoice = $eInvoiceClient->getEInvoiceByQRCode(
            "MH25570631",
            "2019/01/19",
            "72452657",
            "g++HDeJS3GgXiHu8ACvkIg==",
            "2355"
        );
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/EInvoiceTestOutput01.json",
            json_encode($invoice)
        );

        // Normal case with additional amount
        $invoice = $eInvoiceClient->getEInvoiceByQRCode(
            "MH25570631",
            "2019/01/19",
            "72452657",
            "g++HDeJS3GgXiHu8ACvkIg==",
            "2355",
            "50"
        );
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/EInvoiceTestOutput01.json",
            json_encode($invoice)
        );

        // E-Invoice system error 1
        $this->expectException(EInvoiceResponseException::class);
        $invoice = $eInvoiceClient->getEInvoiceByQRCode(
            "MH25570631",
            "2019/01/19",
            "72452657",
            "g++HDeJS3GgXiHu8ACvkIg==",
            "2355"
        );

        // E-Invoice system error 2
        $this->expectException(EInvoiceResponseException::class);
        $invoice = $eInvoiceClient->getEInvoiceByQRCode(
            "MH25570631",
            "2019/01/19",
            "72452657",
            "g++HDeJS3GgXiHu8ACvkIg==",
            "2355"
        );

        // Server error
        $this->expectException(RequestException::class);
        $invoice = $eInvoiceClient->getEInvoiceByQRCode(
            "MH25570631",
            "2019/01/19",
            "72452657",
            "g++HDeJS3GgXiHu8ACvkIg==",
            "2355"
        );
    }

    public function testCarriedEInvoice(): void
    {
        $handle = fopen(__dir__ . "/EInvoiceTestInput02.json", "r");
        $sucessResult = fread($handle, filesize(__dir__ . "/EInvoiceTestInput02.json"));
        fclose($handle);

        $failResult = '{"v":"0.5","code":951,"msg":"連線逾時"}';

        $mock = new MockHandler([
            new Response(200, [], $sucessResult),
            new Response(200, [], $failResult),
            new Response(200, [], "<html></html>"),
            new Response(503)
        ]);
        $mockHttpClient = new Client(["handler" => HandlerStack::create($mock)]);
        $eInvoiceClient = new EInvoiceClient($mockHttpClient);

        // Normal case
        $invoice = $eInvoiceClient->getCarriedEInvoice(
            "MH25570631",
            "2019/01/19",
            "/TESTEST",
            "0000"
        );
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/EInvoiceTestOutput02.json",
            json_encode($invoice)
        );

        // E-Invoice system error 1
        $this->expectException(EInvoiceResponseException::class);
        $invoice = $eInvoiceClient->getCarriedEInvoice(
            "MH25570631",
            "2019/01/19",
            "/TESTEST",
            "0000"
        );

        // E-Invoice system error 2
        $this->expectException(EInvoiceResponseException::class);
        $invoice = $eInvoiceClient->getCarriedEInvoice(
            "MH25570631",
            "2019/01/19",
            "/TESTEST",
            "0000"
        );

        // Server error
        $this->expectException(RequestException::class);
        $invoice = $eInvoiceClient->getCarriedEInvoice(
            "MH25570631",
            "2019/01/19",
            "/TESTEST",
            "0000"
        );
    }

    public function testGetCarriedEInvoiceList(): void
    {
        $handle = fopen(__dir__ . "/EInvoiceTestInput03.json", "r");
        $sucessResult = fread($handle, filesize(__dir__ . "/EInvoiceTestInput03.json"));
        fclose($handle);

        $failResult = '{"v":"0.5","code":951,"msg":"連線逾時"}';

        $mock = new MockHandler([
            new Response(200, [], $sucessResult),
            new Response(200, [], $failResult),
            new Response(200, [], "<html></html>"),
            new Response(503)
        ]);
        $mockHttpClient = new Client(["handler" => HandlerStack::create($mock)]);
        $eInvoiceClient = new EInvoiceClient($mockHttpClient);

        // Normal case
        $invoiceList = $eInvoiceClient->getCarriedEInvoiceList(
            "2012/07/01",
            "2012/07/31",
            "/XCCYDHQ",
            "0000"
        );

        $expectResult = array(
            array(
                "invNum" => "ER02338051",
                "invDate" => "2012/07/09",
                "cardType" => "3J0002",
                "cardNo" => "/XCCYDHQ"
            ),
            array(
                "invNum" => "ER02338052",
                "invDate" => "2012/07/09",
                "cardType" => "3J0002",
                "cardNo" => "/XCCYDHQ"
            )
        );

        $this->assertEquals($invoiceList, $expectResult);

        // E-Invoice system error 1
        $this->expectException(EInvoiceResponseException::class);
        $invoice = $eInvoiceClient->getCarriedEInvoiceList(
            "2012/07/01",
            "2012/07/31",
            "/XCCYDHQ",
            "0000"
        );

        // E-Invoice system error 2
        $this->expectException(EInvoiceResponseException::class);
        $invoice = $eInvoiceClient->getCarriedEInvoiceList(
            "2012/07/01",
            "2012/07/31",
            "/XCCYDHQ",
            "0000"
        );

        // Server error
        $this->expectException(RequestException::class);
        $invoice = $eInvoiceClient->getCarriedEInvoiceList(
            "2012/07/01",
            "2012/07/31",
            "/XCCYDHQ",
            "0000"
        );
    }
}
