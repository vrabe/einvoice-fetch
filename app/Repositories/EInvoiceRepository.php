<?php

declare(strict_types = 1);

namespace App\Repositories;

use Carbon\Carbon;
use App\EInvoiceClient\EInvoiceClient;
use App\EInvoiceClient\EInvoice;

class EInvoiceRepository {

    protected $client;

    /**
     * Constructor
     *
     * @param EInvoiceClient $client
     */
    public function __construct(EInvoiceClient $client)
    {
        $this->client = $client;
    }

    public function getEInvoices(array $data) : array
    {
        $invoices = array();

        foreach($data as $entry) {
            $invoices[] = $this->client->getEInvoice(
                $entry["invNum"],
                $entry["invDate"],
                $entry["invTerm"],
                $entry["randomNumber"],
                isset($entry["amount"]) ? $entry["amount"] : null
            );
        }

        return $invoices;
    }

    public function getCarriedEInvoices(
        string $startDate,
        string $endDate,
        string $cardNo,
        string $cardEncrypt,
        bool $onlyWinningInv = false
    ) : array {
        $requestInvoiceData = array();
        
        $periodStart = Carbon::createFromFormat("Y/m/d", $startDate);
        $periodEnd = Carbon::createFromFormat("Y/m/d", $endDate);

        $currentStart = $periodStart;
        if($periodEnd->lessThan($periodStart->copy()->endOfMonth())) {
            $currentEnd = $periodEnd;
        } else {
            $currentEnd = $periodStart->copy()->endOfMonth();
        }

        while($currentStart->lessThan($periodEnd)) {
            $currentInvoiceData = $this->client->getCarriedEInvoiceList(
                $currentStart->format("Y/m/d"),
                $currentEnd->format("Y/m/d"),
                $cardNo,
                $cardEncrypt
            );

            $requestInvoiceData = array_merge($requestInvoiceData, $currentInvoiceData);

            $currentStart->addMonthsNoOverflow(1)->startOfMonth();
            if($periodEnd->lessThan($currentStart->copy()->endOfMonth())) {
                $currentEnd = $periodEnd;
            } else {
                $currentEnd = $currentStart->copy()->endOfMonth();
            }
        }

        $invoices = array();

        foreach($requestInvoiceData as $entry) {
            $currentInvoice = $this->client->getCarriedEInvoice(
                $entry["invNum"],
                $entry["invDate"],
                $cardNo,
                $cardEncrypt
            );
            $currentInvoice->note["cardType"] = $entry["cardType"];
            $currentInvoice->note["cardNo"] = $entry["cardNo"];

            $invoices[] = $currentInvoice;
        }

        return $invoices;
    }
}