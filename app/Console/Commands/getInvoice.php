<?php

namespace App\Console\Commands;

use App\Repositories\EInvoiceRepository;
use Illuminate\Console\Command;

class getInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "invoice:get {invNum} {invDate} {invTerm} {randomNumber} {amount} {--O|output=output.json}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Get invoice";

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
        $inputData = array(
            array(
                "invNum" => $this->argument("invNum"),
                "invDate" => $this->argument("invDate"),
                "invTerm" => $this->argument("invTerm"),
                "randomNumber" => $this->argument("randomNumber"),
                "amount" => $this->argument("amount")
            )
        );
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
