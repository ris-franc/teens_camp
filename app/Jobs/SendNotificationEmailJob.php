<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendNotificationEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Notification $notification,
        public User $recipient
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Queued Email Notification: [{$this->notification->title}] sent to {$this->recipient->email}");
    }
}
