<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class SendBorrowingReminders extends Command
{
    protected $signature = 'app:send-borrowing-reminders {--date= : The specific date to check reminders for (format Y-m-d)}';

    protected $description = 'Checks active borrowings and sends Email/WhatsApp notifications for H-1 return reminders and late warnings';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    public function handle(): int
    {
        $dateParam = $this->option('date');
        $this->info("Starting borrowing reminders check...");
        if ($dateParam) {
            $this->info("Checking relative to date: {$dateParam}");
        }

        $results = $this->notificationService->processDailyReminders($dateParam);

        $this->success("Finished checking.");
        $this->line("Reminders sent (H-1): <info>{$results['reminders_sent']}</info>");
        $this->line("Late warnings sent: <info>{$results['warnings_sent']}</info>");

        return Command::SUCCESS;
    }

    private function success(string $message): void
    {
        $this->line("<fg=green;options=bold>{$message}</>");
    }
}
