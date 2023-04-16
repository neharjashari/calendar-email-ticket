<?php

namespace App\Console\Commands;

use App\Services\EmailSchedulingAndSendingService;
use Illuminate\Console\Command;

class ScheduleEmailsTestCommand extends Command
{
    protected $signature = 'test:emails';
    protected $description = 'Test emails for today';

    public function handle()
    {
        $emailSchedulingAndSendingService = app(EmailSchedulingAndSendingService::class);
        $emailSchedulingAndSendingService->scheduleEmailsForToday(true);

        $this->info('Emails scheduled successfully for our simulated date of 2022-07-01 09:30:00');
    }
}
