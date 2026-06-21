<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Log;

class NotificationGateway
{
    public function sendEmail(string $to, string $subject, string $message): bool
    {
        Log::info("SIMULATION: Email SMTP sent to [{$to}] | Subject: [{$subject}] | Body: [{$message}]");
        return true;
    }

    public function sendWhatsApp(string $to, string $message): bool
    {
        Log::info("SIMULATION: WhatsApp API message sent to [{$to}] | Body: [{$message}]");
        return true;
    }
}
