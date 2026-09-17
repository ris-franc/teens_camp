<?php

namespace App\Http\Controllers;

use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentReceiptController extends Controller
{
    public function show(string $receiptNumber)
    {
        $receipt = Receipt::where('receipt_number', $receiptNumber)->with(['campSeason', 'user'])->firstOrFail();

        // Access check:
        // Staff can view any receipt
        // Parent can view if payer or linked to teen
        // Teen can view if issued for them
        $staff = Auth::guard('staff')->user();
        $user = Auth::guard('web')->user();

        $allowed = false;
        if ($staff) {
            $allowed = true;
        } elseif ($user) {
            if ($receipt->user_id === $user->id) {
                $allowed = true;
            } elseif ($user->isParent()) {
                $teenId = $receipt->meta_data['teen_id'] ?? null;
                $regId = $receipt->meta_data['registration_id'] ?? null;
                if ($teenId && $user->teens()->where('users.id', $teenId)->exists()) {
                    $allowed = true;
                } elseif ($regId) {
                    $reg = \App\Models\Registration::find($regId);
                    if ($reg && $user->teens()->where('users.id', $reg->teen_id)->exists()) {
                        $allowed = true;
                    }
                }
            } elseif ($user->isTeen()) {
                $teenId = $receipt->meta_data['teen_id'] ?? null;
                $regId = $receipt->meta_data['registration_id'] ?? null;
                if ($teenId == $user->id) {
                    $allowed = true;
                } elseif ($regId) {
                    $reg = \App\Models\Registration::find($regId);
                    if ($reg && $reg->teen_id == $user->id) {
                        $allowed = true;
                    }
                }
            }
        }

        if (!$allowed) {
            abort(403, 'Unauthorized receipt access.');
        }

        return view('receipts.show', [
            'receipt' => $receipt,
            'isStaff' => (bool)$staff,
        ]);
    }
}
