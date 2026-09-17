<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BrevoMailService
{
    protected string $apiKey;
    protected string $smtpHost;
    protected int $smtpPort;
    protected string $smtpLogin;
    protected string $senderEmail;
    protected string $senderName;

    public function __construct()
    {
        $this->apiKey = config('services.brevo.api_key', env('BREVO_API_KEY', ''));
        $this->smtpHost = config('mail.mailers.smtp.host', env('MAIL_HOST', 'smtp-relay.brevo.com'));
        $this->smtpPort = (int)config('mail.mailers.smtp.port', env('MAIL_PORT', 587));
        $this->smtpLogin = config('mail.mailers.smtp.username', env('MAIL_USERNAME', 'b47b4b001@smtp-brevo.com'));
        $this->senderEmail = config('mail.from.address', env('MAIL_FROM_ADDRESS', 'b47b4b001@smtp-brevo.com'));
        $this->senderName = config('mail.from.name', env('MAIL_FROM_NAME', 'Teen Camp'));
    }

    /**
     * Dispatch email for a system notification.
     */
    public function dispatchNotificationEmail(Notification $notification): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $recipients = [];

        if ($notification->user_id) {
            $user = User::find($notification->user_id);
            if ($user && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                $recipients[] = [
                    'email' => $user->email,
                    'name' => $user->name,
                ];
            }
        } elseif ($notification->target_role) {
            $roles = in_array($notification->target_role, ['staff', 'admin'])
                ? ['admin', 'pastor', 'registration', 'campaign', 'campaign_head']
                : [$notification->target_role];

            $staffUsers = User::whereIn('role', $roles)->take(5)->get();
            foreach ($staffUsers as $su) {
                if (filter_var($su->email, FILTER_VALIDATE_EMAIL)) {
                    $recipients[] = [
                        'email' => $su->email,
                        'name' => $su->name,
                    ];
                }
            }
        }

        if (empty($recipients)) {
            return;
        }

        $subject = '📢 ' . $notification->title . ' — Teen Camp';
        $fullLink = $notification->link ? url($notification->link) : url('/login');

        $htmlContent = $this->buildEmailTemplate(
            title: $notification->title,
            message: $notification->message,
            link: $fullLink,
            type: $notification->type
        );

        foreach ($recipients as $recipient) {
            $this->sendEmail($recipient['email'], $recipient['name'], $subject, $htmlContent);
        }
    }

    /**
     * Resolve verified sender from Brevo account.
     */
    public function getVerifiedSender(): array
    {
        if (!empty($this->senderEmail) && !str_contains($this->senderEmail, '@smtp-brevo.com')) {
            return [
                'email' => $this->senderEmail,
                'name' => $this->senderName ?: 'Teen Camp Kenya',
            ];
        }

        if (!empty($this->apiKey)) {
            try {
                $response = Http::withHeaders([
                    'api-key' => $this->apiKey,
                    'accept' => 'application/json',
                ])->timeout(5)->get('https://api.brevo.com/v3/senders');

                if ($response->successful()) {
                    $senders = $response->json('senders') ?? [];
                    foreach ($senders as $s) {
                        if (!empty($s['active']) && !empty($s['email'])) {
                            return [
                                'email' => $s['email'],
                                'name' => !empty($this->senderName) && $this->senderName !== 'Laravel' ? $this->senderName : ($s['name'] ?? 'Teen Camp Kenya'),
                            ];
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Could not fetch Brevo verified senders: ' . $e->getMessage());
            }
        }

        return [
            'email' => 'karis123wer@gmail.com',
            'name' => $this->senderName ?: 'Teen Camp Kenya',
        ];
    }

    /**
     * Send an email via Brevo SMTP or fallback to Brevo v3 REST API.
     */
    public function sendEmail(string $toEmail, string $toName, string $subject, string $htmlContent): bool
    {
        if (app()->environment('testing')) {
            return true;
        }

        $sender = $this->getVerifiedSender();

        // 1. Attempt sending via Brevo v3 Transactional REST API
        if (!empty($this->apiKey)) {
            try {
                $response = Http::withHeaders([
                    'api-key' => $this->apiKey,
                    'accept' => 'application/json',
                    'content-type' => 'application/json',
                ])->timeout(8)->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => [
                        'name' => $sender['name'],
                        'email' => $sender['email'],
                    ],
                    'to' => [
                        [
                            'email' => $toEmail,
                            'name' => $toName,
                        ]
                    ],
                    'subject' => $subject,
                    'htmlContent' => $htmlContent,
                ]);

                if ($response->successful()) {
                    Log::info("Brevo API: Email delivered successfully to {$toEmail} from {$sender['email']} [{$subject}]");
                    return true;
                } else {
                    $errorData = $response->json();
                    Log::warning("Brevo API responded with error: " . ($errorData['message'] ?? $response->body()));
                }
            } catch (\Throwable $e) {
                Log::warning("Brevo API connection error: " . $e->getMessage());
            }
        }

        // 2. Fallback to Laravel Mail (Brevo SMTP relay)
        try {
            Mail::html($htmlContent, function ($msg) use ($toEmail, $toName, $subject, $sender) {
                $msg->to($toEmail, $toName)
                    ->from($sender['email'], $sender['name'])
                    ->subject($subject);
            });
            Log::info("Brevo SMTP: Email sent successfully to {$toEmail} from {$sender['email']} [{$subject}]");
            return true;
        } catch (\Throwable $e) {
            Log::error("Brevo SMTP sending failed to {$toEmail}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Premium email template with camp branding.
     */
    protected function buildEmailTemplate(string $title, string $message, string $link, string $type): string
    {
        $year = date('Y');
        $accentColor = match ($type) {
            'payment', 'donation' => '#16a34a',
            'adopt_a_teen' => '#dc2626',
            'registration', 'form' => '#2563eb',
            'campaign_sale' => '#d97706',
            default => '#dc2626',
        };

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{$title}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f0f12; margin: 0; padding: 24px; color: #f3f4f6; }
        .card { max-width: 580px; margin: 0 auto; background: #1a1a20; border: 1px solid #2e2e38; border-radius: 12px; overflow: hidden; }
        .header { background: #121216; padding: 28px; text-align: center; border-bottom: 2px solid {$accentColor}; }
        .title { color: #ffffff; font-size: 20px; font-weight: bold; margin: 0 0 8px 0; }
        .badge { display: inline-block; padding: 4px 12px; font-size: 11px; font-weight: bold; text-transform: uppercase; border-radius: 20px; background: rgba(220, 38, 38, 0.15); color: {$accentColor}; border: 1px solid {$accentColor}; }
        .body { padding: 32px 28px; font-size: 15px; line-height: 1.6; color: #d1d5db; }
        .message-box { background: #22222b; border-left: 4px solid {$accentColor}; padding: 16px 20px; border-radius: 6px; margin: 20px 0; font-size: 15px; color: #ffffff; }
        .btn-wrapper { text-align: center; margin: 30px 0 10px 0; }
        .btn { display: inline-block; padding: 12px 28px; background-color: #dc2626; color: #ffffff !important; text-decoration: none; font-weight: bold; border-radius: 6px; font-size: 14px; }
        .footer { background: #121216; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-top: 1px solid #2e2e38; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="badge">Teen Camp Notification</div>
            <h1 class="title" style="margin-top: 12px;">{$title}</h1>
        </div>
        <div class="body">
            <p style="margin-top:0;">Hello,</p>
            <p>You have received a new update from the <strong>Teen Camp Management System</strong>:</p>
            <div class="message-box">
                {$message}
            </div>
            <div class="btn-wrapper">
                <a href="{$link}" class="btn" target="_blank">View Details in Portal &rarr;</a>
            </div>
        </div>
        <div class="footer">
            &copy; {$year} Teen Camp. Sent via Brevo Email Notification Relay.<br>
            If you did not request this update, please disregard this notification.
        </div>
    </div>
</body>
</html>
HTML;
    }
}
