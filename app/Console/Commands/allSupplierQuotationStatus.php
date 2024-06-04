<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomerMail;
use App\Mail\SupplierQuotationMail;

class allSupplierQuotationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'allSuppQuotationStatus:change';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This is a schedule for change supplier status after 5 minute  go get quotation , based on all supplier.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $requestedQuotes = DB::table('requestedquotes')->where('status', 'New')->get();
        if(count($requestedQuotes)>0){
            foreach($requestedQuotes as $reqquote){
                if($reqquote->supplierid ==''){
                    $IprogressResps = DB::table('request_response')->where('request_quote_id', $reqquote->id)
                    ->where('status', 'Inprogress')->get();
                    if(count($IprogressResps)>0){
                       foreach ($inProgressResps as $resp) {
                            // Check if 'InProgress' is older than 3 hours
                            if ($resp->created_at->diffInHours(now()) > 12) {
                                $resp->status = 'Closed';
                                $resp->save();
                                $reqResps = DB::table('request_response')->where('request_quote_id', $reqquote->id)
                                ->where('status', 'Delay')
                                ->get();      
                                if(count($reqResps) > 0){
                                    foreach($reqResps as $resp){
                                        $resp->status = 'Closed';
                                        $resp->save();
                                    }
                                }
                                $reqquote->status='Closed';
                            }  
                            $this->info('Quoation is closed becoase it it extended 12 hr.');
                        }
                    }
                    else{
                        // Check for 'Cancelled' quotations
                        $cancelledResps = DB::table('request_response')->where('request_quote_id', $reqquote->id)
                        ->where('status', 'Cancelled')
                        ->get();
                        if ($cancelledResps->count() > 0) {
                            // Handle cancelled quotations logic here
                            $reqResps = DB::table('request_response')->where('request_quote_id', $reqquote->id)
                            ->where('status', 'Delay')
                            ->take(1)
                            ->get();       
                            if(count($reqResps) > 0){
                                foreach($reqResps as $resp){
                                    $resp->status = 'InProgress';
                                    $resp->save();
                                    $user = DB::table('users')->where("id", $reqquote->customerid)->first();
                                    $productname = DB::table('productlist')->where('id', '=', $reqquote->product)->first('name');
                                    Mail::to( $user->email)->send(new CustomerMail($resp->id,$user->name,$resp->suplierid,$productname->name,$reqquote->qty,
                                    $reqquote->created_at,
                                    $reqquote->unit_of_measurement,'Customer'));
                                    $userrolemap = DB::table('user_role_mapping')->where('role_id', '=', '1')->first();
                                    $user = DB::table('users')->where('id', $userrolemap->user_id)->first();
                                    Mail::to($user->email)->send(new CustomerMail($resp->id,$user->name,$resp->suplierid,$productname->name,$reqquote->qty,
                                    $reqquote->created_at,$reqquote->unit_of_measurement,'Admin'));
                                    $user = DB::table('users')->where('id', $resp->suplierid)->first();
                                    Mail::to($user->email)->send(new SupplierQuotationMail($resp->id,$user->name,$resp->suplierid,$productname->name,$reqquote->qty,
                                    $reqquote->created_at,$reqquote->unit_of_measurement,'Supplier'));
                                }
                            }
                        } else {
                            $reqResps = DB::table('request_response')->where('request_quote_id', $reqquote->id)
                            ->where('status', 'Delay')
                            ->get();
                            if(count($reqResps) > 0){
                                foreach($reqResps as $resp){
                                    $resp->status = 'Closed';
                                    $resp->save();
                                }
                            }
                            $this->info('No quotations, this order is delivered');
                        }
                    }
                }
            }
        }
    }
}
