<?php

namespace App\Http\Controllers;

use App\Models\MpesaSetting;
use App\Models\Registration;
use App\Services\DarajaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MpesaController extends Controller
{
    protected DarajaService $daraja;

    public function __construct(DarajaService $daraja)
    {
        $this->daraja = $daraja;
    }

    /**
     * Get active Paybill info and account references
     */
    public function getPaybillInfo()
    {
        $settings = MpesaSetting::getSettings();

        return response()->json([
            'paybill' => $settings->paybill_number,
            'camp_fee_account' => $settings->camp_fee_account,
            'adopt_account' => $settings->adopt_account,
            'environment' => $settings->environment,
            'is_mock' => $settings->isMock(),
        ]);
    }

    /**
     * Initiate STK Push
     */
    public function initiateStk(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'min:9', 'max:20'],
            'amount' => ['required', 'numeric', 'min:1'],
            'account_type' => ['required', 'in:camp_fee,adopt_a_teen'],
            'registration_id' => ['nullable', 'exists:registrations,id'],
            'donor_name' => ['nullable', 'string', 'max:150'],
            'reference' => ['nullable', 'string', 'max:30'],
        ]);

        $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();
        $accountType = $request->account_type;
        $amount = (float)$request->amount;
        $phone = $request->phone;

        $metadata = [
            'user_id' => $user?->id,
            'donor_name' => $request->donor_name ?: ($user?->name ?? 'M-Pesa Contributor'),
        ];

        if ($accountType === 'camp_fee') {
            $reg = null;
            if ($request->registration_id) {
                $reg = Registration::find($request->registration_id);
            } elseif ($user && $user->isParent()) {
                $reg = $user->teens()->first()?->registrations()->latest()->first();
            }

            if ($reg) {
                $metadata['registration_id'] = $reg->id;
                $metadata['parent_id'] = $reg->teen?->parents->first()?->id ?? $user?->id;
                $metadata['season_id'] = $reg->camp_season_id;
                $ref = $request->reference ?: ('TC-' . $reg->id);
            } else {
                $ref = $request->reference ?: 'CAMP-FEE';
            }
        } else {
            // Adopt-a-teen
            $ref = $request->reference ?: 'ADOPT-A-TEEN';
        }

        $result = $this->daraja->initiateStkPush(
            $phone,
            $amount,
            $accountType,
            $ref,
            $accountType === 'adopt_a_teen' ? 'Adopt-a-Teen Donation' : 'Camp Fee Payment',
            $metadata
        );

        return response()->json($result);
    }

    /**
     * Public Safaricom Daraja STK Push Callback URL
     */
    public function darajaCallback(Request $request)
    {
        $data = $request->all();
        Log::info('Daraja STK Callback Received:', $data);

        $stkCallback = $data['Body']['stkCallback'] ?? null;
        if (!$stkCallback) {
            return response()->json(['ResultCode' => 1, 'ResultDesc' => 'Invalid payload']);
        }

        $resultCode = $stkCallback['ResultCode'] ?? -1;
        $merchantRequestId = $stkCallback['MerchantRequestID'] ?? null;
        $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;

        if ($resultCode === 0) {
            $items = $stkCallback['CallbackMetadata']['Item'] ?? [];
            $amount = 0;
            $mpesaReceipt = null;
            $phone = null;

            foreach ($items as $item) {
                if ($item['Name'] === 'Amount') $amount = (float)$item['Value'];
                if ($item['Name'] === 'MpesaReceiptNumber') $mpesaReceipt = $item['Value'];
                if ($item['Name'] === 'PhoneNumber') $phone = (string)$item['Value'];
            }

            Log::info("Successful M-Pesa Payment recorded: {$mpesaReceipt}, Amount: {$amount}");
        } else {
            Log::warning("M-Pesa STK Failed for CheckoutRequestID: {$checkoutRequestId}. Code: {$resultCode}");
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
