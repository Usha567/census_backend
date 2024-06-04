<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RequestedQuotes;
use App\Models\RequestResponse;
use App\Mail\deleteReqQuoteMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
class deleteRequestedQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ReqQuote:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is requested quote delete scheduling.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //get all requested quote
        $reqQuotes = DB::table('requestedquotes')->where('status', 'New')->get();

        // Check if the quote exists
        if (count($reqQuotes)>0) {
            foreach($reqQuotes as $reqQuote){
                $reqResponses = DB::table('request_response')->where('request_quote_id', $reqQuote->id)->get();
                // If there are no responses, delete the quote and send an email
                if ($reqResponses->isEmpty()) {
                    $userrolemap = DB::table('user_role_mapping')->where('role_id', 1)->first('user_id');
                    $user = DB::table('users')->where('id', $userrolemap->user_id)->first();
                    Mail::to($user->email)->send(new deleteReqQuoteMail($reqQuote->id));

                    $user = DB::table('users')->where('id', $reqQuote->customerid)->first();
                    Mail::to($user->email)->send(new deleteReqQuoteMail($reqQuote->id));
                    
                    $suppprodmaps = DB::table('suplierproductsmapping')->where('product_id',$reqQuote->product)->get();
                    if(!is_null($suppprodmaps)){
                        foreach($suppprodmaps as $supp){
                            $userrolemap = DB::table('user_role_mapping')->where('user_id', $supp->supplier_id)->where('role_id', 2)->first();
                            if($userrolemap->approval_status == 'Approved'){
                                $user = DB::table('users')->where('id', $supp->supplier_id)->first();
                                Mail::to($user->email)->send(new deleteReqQuoteMail($reqQuote->id));
                            }
                        }
                    } 
                    DB::table('requestedquotes')->where('id',$reqQuote->id)->delete();
                    // Log the success message
                    $this->info('Requested quote with ID ' . $reqQuote->id . ' deleted successfully.');
                } else {
                    // Log that the quote was not deleted due to existing responses
                    $this->info('Requested quote with ID ' . $reqQuote->id . ' was not deleted because it has responses.');
                }
            }
        } else {
            // Log that the quote was not found
            $this->info('No data found.');
        }
    }
}
