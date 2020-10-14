<?php

namespace App\Console\Commands;

use App\Repositories\EInvoiceRepository;
use Illuminate\Console\Command;

class getCarriedInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:get-carried {carrier} {from} {to} {--P|password} {--O|output=output.json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $carrier = $this->argument("carrier");
        $password = $this->option("password");
        $from = $this->argument("from");
        $to = $this->argument("to");
        if ($password == null) {
            $password = $this->secret('Password of the carrier?');
        }
        $outputData = $repo->getCarriedEInvoices($from, $to, $carrier, $password);
        $outputJson = json_encode($outputData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $outputFilename = $this->option("output");
        $handle = fopen($outputFilename, "w");
        $writen = fwrite($handle, $outputJson);
        if ($writen === false) {
            return 3;
        }
        return 0;
    }
}
