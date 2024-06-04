<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly(); everyFiveMinutes
        // Sample mail schedule
        // ->command('sampleemail:send')
        // ->everyMinute()
        // ->appendOutputTo("scheduler-output.log");

        // Delete Requested quote schedule
        $schedule->command('ReqQuote:delete')->everyTenMinutes()->appendOutputTo('delreqquote_scheduler-output.log');  
      
        //Send Quote To Specific Supplier Condition
        //$schedule->command('QuoteToSpecificSupp:send')->everyMinute()->appendOutputTo('quotetospecificsupp_scheduler-output.log');

        //Send Quotation schedule
        //$schedule->command('quotation:send')->everyMinute()->appendOutputTo('quotation_scheduler-output.log');

        //get quotation by customer based on specific supplier 
        $schedule->command('suppQuotationStatus:change')->everyFiveMinutes()->appendOutputTo('changequotationstatus_specificsupp_scheduler-output.log');

        //get quotation by customer based on all suppliers 
        $schedule->command('allSuppQuotationStatus:change')->everyFiveMinutes()->appendOutputTo('changequotationstatus_allsupp_scheduler-output.log');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
