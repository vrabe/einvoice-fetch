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
     * Check the request result is success or not.
     *
     * @param object $result
     * @return void
     */
    private function checkResult(object $result): void
    {
        if ($result === null) {
            throw new EInvoiceResponseException("回應不是 JSON");
        }

        if ($result->code != "200") {
            throw new EInvoiceResponseException($result->msg, $result->code);
        }

        if (property_exists($result, "invStatus") && $result->invStatus === "該筆發票並無開立") {
            throw new EInvoiceResponseException("該筆發票並無開立");
        }
    }

    /**
     * Generate a EInvoice instance from a request result.
     *
     * @param object $result
     * @param string|null $amount External amount source
     * @return EInvoice
     */
    private function resultToEInvoice(object $result, ?string $amount = null): EInvoice
    {
        /*
            trim leading 0s of rowNum because rowNum in the return value of
            getCarriedEInvoice() doesn't have leading 0s.
        */
        foreach ($result->details as $detail) {
            $detail->rowNum = ltrim($detail->rowNum, "0");
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
        $invoice->currency = $result->currency;
        $invoice->details = $result->details;

        // buyerBan field may not exist.
        if (property_exists($result, "buyerBan")) {
            $invoice->buyerBan = $result->buyerBan;
        }

        // amount field may not exist.
        if (property_exists($result, "amount")) {
            $invoice->amount = $result->amount;
        } else if ($amount != null) {
            $invoice->amount = $amount;
        } else {
            $sum = 0.0;
            foreach ($result->details as $detail) {
                $sum += (float) $detail->amount;
            }
            $invoice->amount = (string) $sum;
        }

        return $invoice;
    }

    /**
     * Get the detail about an e-invoice that you know it's random number from data in barcode.
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

        $this->checkResult($result);

        $invoice = $this->resultToEInvoice($result, $amount);

        $invoice->note = array(
            "randomNumber" => $randomNumber
        );

        return $invoice;
    }

    /**
     * Get the detail about an e-invoice that you know it's random number from data in QRcode.
     *
     * @param string $invNum
     * @param string $invDate
     * @param string $sellerID
     * @param string $encrypt
     * @param string $randomNumber
     * @param string|null $amount
     * @return EInvoice
     */
    public function getEInvoiceByQRCode(
        string $invNum,
        string $invDate,
        string $sellerID,
        string $encrypt,
        string $randomNumber,
        ?string $amount = null
    ): EInvoice {
        $res = $this->client->request("POST", self::URL . "/PB2CAPIVAN/invapp/InvApp", [
            "form_params" => [
                "version" => "0.5",
                "type" => "QRCode",
                "invNum" => $invNum,
                "action" => "qryInvDetail",
                "generation" => "V2",
                "encrypt" => $encrypt,
                "sellerID" => $sellerID,
                "invDate" => $invDate,
                "UUID" => $this->uuid,
                "randomNumber" => $randomNumber,
                "appID" => $this->appID
            ]
        ]);

        $result = json_decode((string) $res->getBody());

        $this->checkResult($result);

        $invoice = $this->resultToEInvoice($result, $amount);

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
        $res = $this->client->request("POST", self::URL . "/PB2CAPIVAN/invServ/InvServ", [
            "form_params" => [
                "version" => "0.5",
                "cardType" => "3J0002",
                "cardNo" => $cardNo,
                "expTimeStamp" => time() + 110,
                "action" => "carrierInvDetail",
                "timeStamp" => time() + 10, // delay 10s to prevent timeout
                "invNum" => $invNum,
                "invDate" => $invDate,
                "uuid" => $this->uuid,
                "appID" => $this->appID,
                "cardEncrypt" => $cardEncrypt
            ]
        ]);

        $result = json_decode((string) $res->getBody());

        $this->checkResult($result);

        $invoice = $this->resultToEInvoice($result);

        return $invoice;
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
        $res = $this->client->request("POST", self::URL . "/PB2CAPIVAN/invServ/InvServ", [
            "form_params" => [
                "version" => "0.5",
                "cardType" => "3J0002",
                "cardNo" => $cardNo,
                "expTimeStamp" => time() + 110,
                "action" => "carrierInvChk",
                "timeStamp" => time() + 10, // delay 10s to prevent timeout
                "startDate" => $startDate,
                "endDate" => $endDate,
                "onlyWinningInv" => $onlyWinningInv ? "Y" : "N",
                "uuid" => $this->uuid,
                "appID" => $this->appID,
                "cardEncrypt" => $cardEncrypt
            ]
        ]);

        $result = json_decode((string) $res->getBody());

        $this->checkResult($result);

        $invoiceList = array();

        foreach ($result->details as $detail) {
            $invoiceData = array();

            $invoiceData["invNum"] = $detail->invNum;
            $invoiceData["cardType"] = $detail->cardType;
            $invoiceData["cardNo"] = $detail->cardNo;
            $invoiceData["invDate"] = CarbonImmutable::createFromTimestampMs(
                $detail->invDate->time,
                "Asia/Taipei"
            )->format("Y/m/d");

            $invoiceList[] = $invoiceData;
        }

        return $invoiceList;
    }
}
