<?php

declare(strict_types = 1);

namespace App\EInvoiceClient;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client;
use App\Exceptions\EInvoiceResponseException;

class EInvoiceClient
{
    private $appID;
    private $uuid;
    private $client;
    private const URL = "https://api.einvoice.nat.gov.tw";

    /**
     * Constructor
     *
     * @param Client|null $client
     */
    public function __construct(?Client $client = null)
    {
        $this->appID = config("einvoice.appID");
        $this->uuid = config("einvoice.uuid");
        if (is_null($client)) {
            $this->client = new Client(["headers" => [
                "User-Agent" => null
            ]]);
        } else {
            $this->client = $client;
        }
    }

    /**
     * Get the detail about an e-invoice that you know it's random number.
     *
     * @param string $invNum
     * @param string $invDate
     * @param string $invTerm
     * @param string $randomNumber
     * @param string|null $amount
     * @return EInvoice
     */
    public function getEInvoice(
        string $invNum,
        string $invDate,
        string $invTerm,
        string $randomNumber,
        ?string $amount = null
    ): EInvoice {
        $res = $this->client->request("POST", self::URL . "/PB2CAPIVAN/invapp/InvApp", [
            "form_params" => [
                "version" => "0.5",
                "type" => "Barcode",
                "invNum" => $invNum,
                "action" => "qryInvDetail",
                "generation" => "V2",
                "invTerm" => $invTerm,
                "invDate" => $invDate,
                "UUID" => $this->uuid,
                "randomNumber" => $randomNumber,
                "appID" => $this->appID
            ]
        ]);

        $result = json_decode((string) $res->getBody());

        if($result === null) {
            throw new EInvoiceResponseException("回應不是 JSON");
        }

        if ($result->code != "200") {
            throw new EInvoiceResponseException($result->msg, $result->code);
        }

        if($result->invStatus === "該筆發票並無開立") {
            throw new EInvoiceResponseException("該筆發票並無開立");
        }

        $invoice = new EInvoice();
        $invoice->number = $result->invNum;
        $invoice->issuedAt = CarbonImmutable::createFromFormat(
            "YmdH:i:s",
            $result->invDate . $result->invoiceTime
        );
        $invoice->period = $result->invPeriod;
        $invoice->status = $result->invStatus;
        $invoice->sellerName = $result->sellerName;
        $invoice->sellerAddress = $result->sellerAddress;
        $invoice->sellerBan = $result->sellerBan;
        $invoice->buyerBan = $result->buyerBan;
        $invoice->currency = $result->currency;
        $invoice->details = $result->details;

        if ($amount == null) {
            $sum = 0.0;
            foreach ($result->details as $detail) {
                $sum += (double) $detail->amount;
            }
            $invoice->amount = (string) $sum;
        } else {
            $invoice->amount = $amount;
        }

        $invoice->note = array(
            "randomNumber" => $randomNumber
        );

        return $invoice;
    }

    /**
     * Get the detail about an e-invoice that has been saved in a carrier.
     *
     * @param string $invNum
     * @param string $invDate
     * @param string $cardNo
     * @param string $cardEncrypt
     * @return EInvoice
     */
    public function getCarriedEInvoice(
        string $invNum,
        string $invDate,
        string $cardNo,
        string $cardEncrypt
    ): EInvoice {
    }

    /**
     * Get a list of e-invoices saved in a carrier.
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $cardNo
     * @param string $cardEncrypt
     * @param bool $onlyWinningInv
     * @return array
     */
    public function getCarriedEInvoiceList(
        string $startDate,
        string $endDate,
        string $cardNo,
        string $cardEncrypt,
        bool $onlyWinningInv = false
    ): array {
    }
}
