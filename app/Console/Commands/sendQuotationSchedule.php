<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class sendQuotationSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotation:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a send quotation to customer schedule.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $reqQuotes = DB::table('requestedquotes')->where('status', 'New')->get();

        // Check if the quote exists
        if ($reqQuotes) {
            foreach($reqQuotes as $reqQuote){
                $reqResponses = DB::table('request_response')->where('request_quote_id', $reqQuote->id)->get();
                // $reqResponses
            }
            
        }
    }
}
