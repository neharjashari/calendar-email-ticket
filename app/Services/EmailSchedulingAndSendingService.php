<?php

namespace App\Services;

use App\Models\Email;
use App\Models\User;

class EmailSchedulingAndSendingService
{
    private $emailContentGenerationService;
    private $eventEnrichmentService;

    public function __construct(EmailContentGenerationService $emailContentGenerationService, EventEnrichmentService $eventEnrichmentService)
    {
        $this->emailContentGenerationService = $emailContentGenerationService;
        $this->eventEnrichmentService = $eventEnrichmentService;
    }

    public function scheduleEmailsForToday($test_mode = false)
    {
        $users = User::all();

        foreach ($users as $user) {
            $events = $this->eventEnrichmentService->enrichEventsForUser($user, $test_mode);

            if (empty($events)) {
                $this->printMessage($user->email, true);
                continue;
            }

            $emailContent = $this->emailContentGenerationService->generateEmailContent($user, $events);

            // Save email in file
            $fileName = 'email_' . $user->id . '_' . now()->format('Y-m-d_H-i-s') . '.json';
            $filePath = storage_path('app/emails/' . $fileName);
            file_put_contents($filePath, $emailContent);

            // Save email in the database
            $email = new Email();
            $email->user_id = $user->id;
            $email->content = $emailContent;
            $email->sent_at = null; // Will be set by the scheduler
            $email->save();

            $this->printMessage($user->email, false);
        }
    }

    public function sendEmail(Email $email)
    {
        $email->sent_at = now();
        $email->save();
    }

    public function printMessage($email, $empty)
    {
        if ($empty)
            echo "==> No events today for user: {$email}" . PHP_EOL;
        else
            echo "==> Generated email content for user: {$email}" . PHP_EOL;
    }
}
