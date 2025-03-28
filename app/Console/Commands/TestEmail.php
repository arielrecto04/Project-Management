<?php

namespace App\Console\Commands;

use App\Mail\TestMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    protected $signature = 'mail:test';
    protected $description = 'Test email configuration';

    public function handle()
    {
        $this->info('Testing email configuration...');

        try {
            Mail::to('test@example.com')->send(new TestMail());
            $this->info('Email sent successfully! Check your Mailtrap inbox.');
        } catch (\Exception $e) {
            $this->error('Email failed to send!');
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
