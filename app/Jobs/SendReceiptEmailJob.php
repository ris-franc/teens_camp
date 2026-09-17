<?php

namespace App\Jobs;

use App\Models\Receipt;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendReceiptEmailJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Receipt $receipt,
        public User $recipient
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Queued Email: Receipt #{$this->receipt->receipt_number} (\${$this->receipt->amount}) dispatched to {$this->recipient->email}");
    }
}
