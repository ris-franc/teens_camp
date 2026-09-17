<?php

namespace App\Services;

use App\Models\AdoptATeenKitty;
use App\Models\CampSeason;
use App\Models\MpesaSetting;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DarajaService
{
    protected MpesaSetting $settings;

    public function __construct()
    {
        $this->settings = MpesaSetting::getSettings();
    }

    /**
     * Format phone number to standard Safaricom 2547XXXXXXXX or 2541XXXXXXXX
     */
    public static function formatPhoneNumber(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            return '254' . substr($cleaned, 1);
        }

        if (str_starts_with($cleaned, '254')) {
            return $cleaned;
        }

        return '254' . $cleaned;
    }

    /**
     * Initiate Daraja STK Push (Lipa Na M-Pesa Online)
     *
     * @param string $phone Customer phone number
     * @param float $amount Amount in KES
     * @param string $accountType 'camp_fee' or 'adopt_a_teen'
     * @param string|null $reference Custom reference or child identifier
     * @param string|null $description Narration
     * @param array $metadata Additional context (registration_id, user_id, etc.)
     */
    public function initiateStkPush(
        string $phone,
        float $amount,
        string $accountType = 'camp_fee',
        ?string $reference = null,
        ?string $description = null,
        array $metadata = []
    ): array {
        $formattedPhone = self::formatPhoneNumber($phone);
        $destinationAccount = $this->settings->getAccountForType($accountType);
        $paybill = $this->settings->paybill_number ?: '880100';

        // Set account reference for STK prompt on phone
        $accountReference = $reference ?: $destinationAccount;
        // Limit to 12 chars per Safaricom spec
        $accountReference = substr(trim($accountReference), 0, 12);

        $transactionDesc = substr($description ?: "Camp {$accountType} payment", 0, 20);

        // Check if running in mock/simulation mode (offline / local dev / tests)
        if ($this->settings->isMock() || app()->environment('testing')) {
            $checkoutRequestId = 'ws_CO_' . date('dmYHis') . '_' . rand(10000, 99999);
            $simulatedMpesaCode = 'QK' . strtoupper(Str::random(8));

            // Record completed payment directly in simulated environment
            $paymentResult = $this->recordPayment(
                $accountType,
                $amount,
                $simulatedMpesaCode,
                $formattedPhone,
                $destinationAccount,
                $metadata
            );

            return [
                'success' => true,
                'is_mock' => true,
                'account_type' => $accountType,
                'account_reference' => $destinationAccount,
                'paybill' => $paybill,
                'CheckoutRequestID' => $checkoutRequestId,
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
                'CustomerMessage' => "Success! STK Push sent for KES " . number_format($amount, 2) . " to Paybill {$paybill} (Account: {$destinationAccount}).",
                'mpesa_code' => $simulatedMpesaCode,
                'payment' => $paymentResult['payment'] ?? null,
                'receipt' => $paymentResult['receipt'] ?? null,
            ];
        }

        // Live or Sandbox Daraja API Call
        try {
            $token = $this->generateToken();
            if (!$token) {
                return [
                    'success' => false,
                    'message' => 'Failed to obtain Safaricom Daraja access token. Check consumer key and secret.',
                ];
            }

            $baseUrl = $this->settings->isLive() 
                ? 'https://api.safaricom.co.ke' 
                : 'https://sandbox.safaricom.co.ke';

            $timestamp = date('YmdHis');
            $passkey = $this->settings->passkey;
            $password = base64_encode($paybill . $passkey . $timestamp);
            $callbackUrl = route('api.mpesa.callback');

            $payload = [
                'BusinessShortCode' => $paybill,
                'Password' => $password,
                'Timestamp' => $timestamp,
                'TransactionType' => 'CustomerPayBillOnline',
                'Amount' => round($amount),
                'PartyA' => $formattedPhone,
                'PartyB' => $paybill,
                'PhoneNumber' => $formattedPhone,
                'CallBackURL' => $callbackUrl,
                'AccountReference' => $accountReference,
                'TransactionDesc' => $transactionDesc,
            ];

            $response = Http::withToken($token)
                ->timeout(20)
                ->post("{$baseUrl}/mpesa/stkpush/v1/processrequest", $payload);

            $data = $response->json();

            if ($response->successful() && ($data['ResponseCode'] ?? null) === '0') {
                return [
                    'success' => true,
                    'is_mock' => false,
                    'account_type' => $accountType,
                    'account_reference' => $destinationAccount,
                    'paybill' => $paybill,
                    'CheckoutRequestID' => $data['CheckoutRequestID'] ?? null,
                    'ResponseCode' => '0',
                    'ResponseDescription' => $data['ResponseDescription'] ?? 'Accepted',
                    'CustomerMessage' => $data['CustomerMessage'] ?? "Please enter your M-Pesa PIN on your phone to complete payment.",
                ];
            }

            return [
                'success' => false,
                'message' => $data['errorMessage'] ?? ($data['ResponseDescription'] ?? 'STK push failed.'),
                'data' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Daraja STK Push Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Network error connecting to Daraja API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate Daraja OAuth Token
     */
    protected function generateToken(): ?string
    {
        $baseUrl = $this->settings->isLive() 
            ? 'https://api.safaricom.co.ke' 
            : 'https://sandbox.safaricom.co.ke';

        $key = $this->settings->consumer_key;
        $secret = $this->settings->consumer_secret;

        if (!$key || !$secret) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($key, $secret)
                ->timeout(15)
                ->get("{$baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            if ($response->successful()) {
                return $response->json('access_token');
            }
        } catch (\Throwable $e) {
            Log::error('Daraja Token generation error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Record completed payment in system according to account type
     */
    public function recordPayment(
        string $accountType,
        float $amount,
        string $mpesaCode,
        string $phone,
        string $destinationAccount,
        array $meta = []
    ): array {
        $season = isset($meta['season_id']) 
            ? CampSeason::find($meta['season_id']) 
            : CampSeason::getActive();

        $seasonId = $season?->id;

        if ($accountType === 'adopt_a_teen') {
            // Money goes to Adopt-a-Teen Kitty
            $donorName = $meta['donor_name'] ?? 'M-Pesa Donor';
            $recordedBy = $meta['user_id'] ?? null;

            $kittyEntry = AdoptATeenKitty::recordDonation(
                $seasonId,
                $amount,
                "M-Pesa Paybill ({$destinationAccount}) - {$donorName}",
                $donorName,
                $mpesaCode,
                $recordedBy
            );

            $receipt = Receipt::create([
                'camp_season_id' => $seasonId,
                'receipt_number' => Receipt::generateReceiptNumber('ADOPT'),
                'type' => 'kitty_donation_in',
                'user_id' => $recordedBy,
                'amount' => $amount,
                'description' => "Adopt-a-Teen Kitty Donation from {$donorName}",
                'meta_data' => [
                    'phone' => $phone,
                    'mpesa_code' => $mpesaCode,
                    'paybill' => $this->settings->paybill_number,
                    'destination_account' => $destinationAccount,
                    'kitty_entry_id' => $kittyEntry->id,
                ],
            ]);

            // Notify staff
            Notification::notifyRole(
                'staff',
                'Adopt-a-Teen Donation Received',
                "Received KES " . number_format($amount, 2) . " via M-Pesa ({$mpesaCode}) for Adopt-a-Teen Kitty (Account: {$destinationAccount}).",
                'donation',
                route('backoffice.adopt.index')
            );

            return [
                'type' => 'adopt_a_teen',
                'kitty' => $kittyEntry,
                'receipt' => $receipt,
            ];
        }

        // Default: Camp Fee Payment
        $registrationId = $meta['registration_id'] ?? null;
        $parentId = $meta['parent_id'] ?? ($meta['user_id'] ?? null);

        $registration = $registrationId ? Registration::find($registrationId) : null;

        $receiptNumber = Receipt::generateReceiptNumber('MPESA');

        $payment = Payment::create([
            'camp_season_id' => $seasonId,
            'registration_id' => $registration?->id,
            'parent_id' => $parentId,
            'amount' => $amount,
            'source' => 'direct_payment',
            'reference' => $mpesaCode,
            'receipt_number' => $receiptNumber,
            'payment_method' => "M-Pesa Paybill ({$this->settings->paybill_number})",
            'status' => 'completed',
            'notes' => "STK Push to Account: {$destinationAccount}. Phone: {$phone}",
            'created_by' => $parentId,
        ]);

        $receipt = Receipt::create([
            'camp_season_id' => $seasonId,
            'receipt_number' => $receiptNumber,
            'type' => 'direct_payment',
            'user_id' => $parentId,
            'amount' => $amount,
            'description' => "Camp fee payment for " . ($registration?->teen?->name ?? 'Camper') . " via Paybill",
            'meta_data' => [
                'phone' => $phone,
                'mpesa_code' => $mpesaCode,
                'paybill' => $this->settings->paybill_number,
                'destination_account' => $destinationAccount,
                'registration_id' => $registration?->id,
            ],
        ]);

        // Notifications
        if ($parentId) {
            Notification::notifyUser(
                $parentId,
                'M-Pesa Payment Received',
                "Payment of KES " . number_format($amount, 2) . " received via M-Pesa ({$mpesaCode}). Receipt #{$receiptNumber}.",
                'payment',
                route('parent.dashboard'),
                $seasonId
            );
        }

        Notification::notifyRole(
            'staff',
            'Camp Fee Paid via M-Pesa',
            "Payment of KES " . number_format($amount, 2) . " for " . ($registration?->teen?->name ?? 'Camper') . " received via Paybill Account {$destinationAccount} ({$mpesaCode}).",
            'payment',
            route('backoffice.admin.dashboard'),
            $seasonId
        );

        return [
            'type' => 'camp_fee',
            'payment' => $payment,
            'receipt' => $receipt,
        ];
    }
}
