<?php

namespace App\Console\Commands;

use App\Models\Email;
use App\Services\EmailSchedulingAndSendingService;
use Illuminate\Console\Command;

class SendScheduledEmailsCommand extends Command
{
    protected $signature = 'send:emails';
    protected $description = 'Send scheduled emails';

    public function handle()
    {
        $emailSchedulingAndSendingService = app(EmailSchedulingAndSendingService::class);
        $emails = Email::whereNull('sent_at')->get();

        foreach ($emails as $email) {
            $emailSchedulingAndSendingService->sendEmail($email);
            $this->info('Email sent successfully to ' . $email->user->email);
        }

        $this->info('Scheduled emails sent successfully!');
    }
}
