<?php

namespace App\Console\Commands;

use App\Repositories\EInvoiceRepository;
use Illuminate\Console\Command;

class getInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "invoice:get {invoiceCSV} {--O|output=output.json}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Get invoices";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param  App\Repositories\EInvoiceRepository  $repo
     * @return int
     */
    public function handle(EInvoiceRepository $repo)
    {
        $csvFilename = $this->argument("invoiceCSV");
        $handle = fopen($csvFilename, "r");
        $inputData = array();
        if($handle === false) {
            return 1;
        }
        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $num = count($data);
            if($num === 4 || $num === 5) {
                $inputEntry = array(
                    "invNum" => $data[0],
                    "invDate" => $data[1],
                    "invTerm" => $data[2],
                    "randomNumber" => $data[3]
                );
                if($num === 5) {
                    $inputEntry["amount"] = $data[4];
                }
                $inputData[] = $inputEntry;
            } else {
                return 2;
            }
        }
        fclose($handle);
        $outputData = $repo->getEInvoices($inputData);
        $outputJson = json_encode($outputData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $outputFilename = $this->option("output");
        $handle = fopen($outputFilename, "w");
        $writen = fwrite($handle, $outputJson);
        if($writen === false) {
            return 3;
        }
        return 0;
    }
}
