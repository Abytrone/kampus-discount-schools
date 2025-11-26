<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailSending extends Command
{
    protected $signature = 'app:email-send';

    protected $description = 'Command description';

    public function handle(): void
    {
        Mail::to('mahmudsheikh25@gmail.com')->send(new TestMail);
    }
}
