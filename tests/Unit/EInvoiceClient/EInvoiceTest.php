<?php

declare(strict_types = 1);

namespace Tests\Unit\EInvoiceClient;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;
use App\EInvoiceClient\EInvoice;

class EInvoiceTest extends TestCase
{
    public function testWithoutNote(): array
    {
        $invoice = new EInvoice();
        $invoice->number = "MH25570631";
        $invoice->issuedAt = CarbonImmutable::createFromFormat("Y-m-d H:i:s", "2019-01-19 00:00:33");
        $invoice->period = "10802";
        $invoice->status = "已確認";
        $invoice->sellerName = "旱溪夜市站";
        $invoice->sellerBan = "72452657";
        $invoice->amount = "50";
        $invoice->details = [
            [
                "unitPrice" => "50",
                "amount" => "50",
                "quantity" => "1",
                "rowNum" => "1",
                "description" => "停車費"
            ]
        ];
        $invoice->note = [
            "randomNumber" => "2355"
        ];
        $invoiceWithoutNote = $invoice->withoutNote();
        $this->assertSame($invoiceWithoutNote->number, "MH25570631");
        $this->assertEquals($invoiceWithoutNote->issuedAt, Carbon::create(2019, 1, 19, 0, 0, 33));
        $this->assertSame($invoiceWithoutNote->period, "10802");
        $this->assertSame($invoiceWithoutNote->status, "已確認");
        $this->assertSame($invoiceWithoutNote->sellerName, "旱溪夜市站");
        $this->assertSame($invoiceWithoutNote->sellerAddress, "");
        $this->assertSame($invoiceWithoutNote->sellerBan, "72452657");
        $this->assertSame($invoiceWithoutNote->buyerBan, "");
        $this->assertSame($invoiceWithoutNote->currency, "");
        $this->assertSame($invoiceWithoutNote->amount, "50");
        $this->assertSame($invoiceWithoutNote->details, [
            [
                "unitPrice" => "50",
                "amount" => "50",
                "quantity" => "1",
                "rowNum" => "1",
                "description" => "停車費"
            ]
        ]);
        $this->assertSame($invoiceWithoutNote->note, []);
        return [$invoice, $invoiceWithoutNote];
    }

    /**
     * Undocumented function
     *
     * @depends testWithoutNote
     */
    public function testJsonSerialize(array $invoices)
    {
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/EInvoiceTestOutput01.json", 
            json_encode($invoices[0])
        );
        $this->assertJsonStringEqualsJsonFile(
            __DIR__ . "/EInvoiceTestOutput02.json", 
            json_encode($invoices[1])
        );
    }
}
