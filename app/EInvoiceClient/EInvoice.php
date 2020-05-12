<?php

declare(strict_types=1);

namespace App\EInvoiceClient;

class EInvoice implements \JsonSerializable
{
    public string $number = "";
    public $issuedAt;
    public string $period = "";
    public string $status = "";
    public string $sellerName = "";
    public string $sellerAddress = "";
    public string $sellerBan = "";
    public string $buyerBan = "";
    public string $currency = "";
    public string $amount = "";
    public array $details = array();
    public array $note = array();

    /**
     * Return an EInvoice object with these data but without any notes.
     *
     * @return self
     */
    public function withoutNote() : self
    {
        $newObject = clone $this;
        $newObject->note = array();
        return $newObject;
    }

    public function jsonSerialize()
    {
        $array = array(
            "number" => $this->number,
            "issuedAt" => $this->issuedAt->toDateTimeString(),
            "period" => $this->period,
            "status" => $this->status,
            "sellerName" => $this->sellerName,
            "sellerAddress" => $this->sellerAddress,
            "sellerBan" => $this->sellerBan,
            "buyerBan" => $this->buyerBan,
            "currency" => $this->currency,
            "amount" => $this->amount,
            "details" => $this->details
        );
        if ($this->note !== array()) {
            $array["note"] = $this->note;
        }
        return $array;
    }
}
