<?php

namespace App\Console\Commands;

use App\Services\EmailSchedulingAndSendingService;
use Illuminate\Console\Command;

class ScheduleEmailsCommand extends Command
{
    protected $signature = 'schedule:emails';
    protected $description = 'Schedule emails for today';

    public function handle()
    {
        $emailSchedulingAndSendingService = app(EmailSchedulingAndSendingService::class);
        $emailSchedulingAndSendingService->scheduleEmailsForToday();

        $this->info('Emails scheduled successfully!');
    }
}
