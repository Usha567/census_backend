<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\sampleEmail;
class sendSampleMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sampleemail:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sample email send';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Mail::to('usha@divyaltech.com')->send(new sampleEmail());
        $this->info('Sample email sent successfully!');
    }
}
