<?php

namespace App\Console\Commands;

use App\Services\BrevoMailService;
use Illuminate\Console\Command;

class TestBrevoEmailCommand extends Command
{
    protected $signature = 'camp:test-email {to=b47b4b001@smtp-brevo.com}';
    protected $description = 'Send a test email using Brevo SMTP and API configuration';

    public function handle(BrevoMailService $brevo): int
    {
        $to = $this->argument('to');
        $this->info("Sending test notification to: {$to}...");

        $success = $brevo->sendEmail(
            toEmail: $to,
            toName: 'Camp Administrator',
            subject: '⚡ Teen Camp - Brevo Email Notification Test',
            htmlContent: '<h2>Teen Camp Email Notification System</h2><p>Your Brevo configuration has been successfully installed and verified!</p><p>Time: ' . now()->toDayDateTimeString() . '</p>'
        );

        if ($success) {
            $this->info("✓ Test email dispatched successfully!");
            return Command::SUCCESS;
        } else {
            $this->error("✕ Test email dispatch failed. Check storage/logs/laravel.log for details.");
            return Command::FAILURE;
        }
    }
}
