<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Jobs\sendQuoteToSuppOnSpecificSuppBase;

class sendQuoteInSpecificSuppCondition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'QuoteToSpecificSupp:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a send quote first to specific supplier then others schedule.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    
    public function handle(Request $request)
    {
        $requestData=[
            'user_id' => $request->user_id,
            'categoryname'=>$request->categoryname,
            'usertype' =>$request->usertype,
            'product'=> $request->product,
            'quoteflag' => $request->quoteflag
        ];
        sendQuoteToSuppOnSpecificSuppBase::dispatch($requestData)->delay(now()->addMinutes(1));
        $this->info('Job dispatched tosend quote to other supplier after 1 minute.');
    }
}
