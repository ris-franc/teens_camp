<?php

namespace App\Http\Controllers;

use App\Models\AdoptATeenKitty;
use App\Models\AdoptATeenRequest;
use App\Models\CampSeason;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackofficeAdoptATeenController extends Controller
{
    /**
     * Adopt-a-Teen Management Deck for Staff, Admins, and Pastors.
     */
    public function index(Request $request)
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();
        $allSeasons = CampSeason::orderByDesc('year')->get();

        if (!$season) {
            return view('backoffice.adopt.index', [
                'season' => null,
                'allSeasons' => $allSeasons,
                'metrics' => [],
                'requests' => collect(),
                'unsponsoredCampers' => collect(),
                'ledger' => collect(),
            ]);
        }

        // 1. Financial Pool Metrics
        $kittyBalance = AdoptATeenKitty::getCurrentBalance($season->id);
        $totalKittyIn = AdoptATeenKitty::where('camp_season_id', $season->id)
            ->whereIn('type', ['donation_in', 'campaign_profit_in'])
            ->sum('amount');
        $totalKittyOut = AdoptATeenKitty::where('camp_season_id', $season->id)
            ->where('type', 'adopt_out')
            ->sum('amount');
        $donationsCount = AdoptATeenKitty::where('camp_season_id', $season->id)
            ->where('type', 'donation_in')
            ->count();
        $campaignProfitsCount = AdoptATeenKitty::where('camp_season_id', $season->id)
            ->where('type', 'campaign_profit_in')
            ->count();

        // 2. Applications Metrics
        $pendingCount = AdoptATeenRequest::where('camp_season_id', $season->id)
            ->where('status', 'pending')
            ->count();
        $pendingAmount = AdoptATeenRequest::where('camp_season_id', $season->id)
            ->where('status', 'pending')
            ->sum('amount_requested');
        $approvedCount = AdoptATeenRequest::where('camp_season_id', $season->id)
            ->where('status', 'approved')
            ->count();
        $approvedAmount = AdoptATeenRequest::where('camp_season_id', $season->id)
            ->where('status', 'approved')
            ->sum('amount_requested');

        // 3. Applications Query with Filters
        $reqQuery = AdoptATeenRequest::with(['parent', 'teen', 'reviewer'])
            ->where('camp_season_id', $season->id);

        if ($request->filled('status') && $request->status !== 'all') {
            $reqQuery->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $reqQuery->where(function ($q) use ($search) {
                $q->whereHas('teen', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                  ->orWhereHas('parent', fn($sq) => $sq->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        $requestsList = $reqQuery->latest('id')->paginate(15, ['*'], 'requests_page')->withQueryString();

        // 4. Unsponsored Campers List (Campers with remaining tuition due)
        $unsponsoredCampers = Registration::with(['teen.parents', 'payments'])
            ->where('camp_season_id', $season->id)
            ->where('status', '!=', 'withdrawn')
            ->get()
            ->filter(fn($r) => $r->balance_remaining > 0);

        // 5. Immutable Kitty Audit Ledger
        $ledgerQuery = AdoptATeenKitty::with(['receipt', 'createdByUser'])
            ->where('camp_season_id', $season->id);

        if ($request->filled('ledger_type') && $request->ledger_type !== 'all') {
            $ledgerQuery->where('type', $request->ledger_type);
        }

        $ledger = $ledgerQuery->latest('id')->paginate(15, ['*'], 'ledger_page')->withQueryString();

        return view('backoffice.adopt.index', [
            'season' => $season,
            'allSeasons' => $allSeasons,
            'metrics' => [
                'kittyBalance' => $kittyBalance,
                'totalKittyIn' => $totalKittyIn,
                'totalKittyOut' => $totalKittyOut,
                'donationsCount' => $donationsCount,
                'campaignProfitsCount' => $campaignProfitsCount,
                'pendingCount' => $pendingCount,
                'pendingAmount' => $pendingAmount,
                'approvedCount' => $approvedCount,
                'approvedAmount' => $approvedAmount,
            ],
            'requestsList' => $requestsList,
            'unsponsoredCampers' => $unsponsoredCampers,
            'ledger' => $ledger,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Review an Adopt-a-Teen request: Approve & Disburse, Approve Awaiting Funds, or Deny.
     */
    public function review(Request $request, AdoptATeenRequest $adoptRequest)
    {
        $reviewer = Auth::guard('staff')->user();

        $request->validate([
            'action' => ['required', 'in:approve,approve_awaiting_funds,deny'],
            'decision_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $season = $adoptRequest->campSeason;
        $kittyBalance = AdoptATeenKitty::getCurrentBalance($season->id);

        if ($request->action === 'approve') {
            if ($kittyBalance < $adoptRequest->amount_requested) {
                return back()->withErrors(['error' => "Insufficient funds in Kitty (Current Balance: KES " . number_format($kittyBalance, 2) . "). You can select 'Approve - Awaiting Funds' instead."]);
            }

            DB::transaction(function () use ($adoptRequest, $reviewer, $season, $kittyBalance, $request) {
                $newBalance = $kittyBalance - $adoptRequest->amount_requested;
                $receiptNumber = Receipt::generateReceiptNumber('ADP');

                $receipt = Receipt::create([
                    'camp_season_id' => $season->id,
                    'receipt_number' => $receiptNumber,
                    'type' => 'kitty_adopt_out',
                    'user_id' => $adoptRequest->parent_id,
                    'amount' => $adoptRequest->amount_requested,
                    'description' => "Adopt-a-Teen sponsorship for {$adoptRequest->teen->name}",
                    'meta_data' => [
                        'teen_id' => $adoptRequest->teen_id,
                        'parent_id' => $adoptRequest->parent_id,
                        'approved_by' => $reviewer->name,
                    ],
                ]);

                AdoptATeenKitty::create([
                    'camp_season_id' => $season->id,
                    'type' => 'adopt_out',
                    'amount' => $adoptRequest->amount_requested,
                    'balance_after' => $newBalance,
                    'description' => "Sponsorship: {$adoptRequest->teen->name}",
                    'reference' => "REQ-{$adoptRequest->id}",
                    'receipt_id' => $receipt->id,
                    'created_by' => $reviewer->id,
                ]);

                // Record payment against teen registration
                $reg = Registration::where('camp_season_id', $season->id)
                    ->where('teen_id', $adoptRequest->teen_id)
                    ->first();

                if ($reg) {
                    Payment::create([
                        'camp_season_id' => $season->id,
                        'registration_id' => $reg->id,
                        'parent_id' => $adoptRequest->parent_id,
                        'amount' => $adoptRequest->amount_requested,
                        'source' => 'adopt_a_teen_transfer',
                        'reference' => "KITTY-AID-{$adoptRequest->id}",
                        'receipt_number' => $receiptNumber,
                        'payment_method' => 'Adopt-a-Teen Kitty',
                        'status' => 'completed',
                        'created_by' => $reviewer->id,
                    ]);
                }

                $adoptRequest->update([
                    'status' => 'approved',
                    'reviewed_by' => $reviewer->id,
                    'reviewed_at' => now(),
                    'decision_notes' => $request->decision_notes,
                ]);

                // 1. Notify Parent Account
                Notification::notifyUser(
                    $adoptRequest->parent_id,
                    "Adopt-a-Teen Aid Approved!",
                    "Great news! Your financial assistance application for {$adoptRequest->teen->name} was approved. KES " . number_format($adoptRequest->amount_requested, 2) . " has been credited to camp tuition.",
                    'adopt_a_teen',
                    route('parent.dashboard'),
                    'bi-heart-fill text-danger'
                );

                // 2. Notify Teen Account
                Notification::notifyUser(
                    $adoptRequest->teen_id,
                    "Camp Sponsorship Confirmed",
                    "A camp scholarship of KES " . number_format($adoptRequest->amount_requested, 2) . " was approved for your camp fees.",
                    'adopt_a_teen',
                    route('teen.dashboard'),
                    'bi-gift-fill text-danger'
                );

                // 3. Notify Staff
                Notification::notifyStaff(
                    "Adopt-a-Teen Aid Granted",
                    "{$reviewer->name} approved KES " . number_format($adoptRequest->amount_requested, 2) . " scholarship for {$adoptRequest->teen->name} from Kitty pool.",
                    'adopt_a_teen',
                    route('backoffice.adopt.index'),
                    'bi-heart-fill text-danger'
                );
            });

            return back()->with('success', "Adopt-a-Teen request approved! KES " . number_format($adoptRequest->amount_requested, 2) . " transferred from Kitty to payment record.");
        } elseif ($request->action === 'approve_awaiting_funds') {
            $adoptRequest->update([
                'status' => 'approved-awaiting-funds',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'decision_notes' => $request->decision_notes,
            ]);

            Notification::notifyUser(
                $adoptRequest->parent_id,
                "Adopt-a-Teen Status: Awaiting Funds",
                "Your application for {$adoptRequest->teen->name} was approved in principle and will be disbursed as soon as donations replenish the kitty.",
                'adopt_a_teen',
                route('parent.dashboard'),
                'bi-hourglass-split text-warning'
            );

            return back()->with('info', "Adopt-a-Teen request set to 'Approved - Awaiting Funds'. Funds will be disbursed once kitty is topped up.");
        } else {
            $adoptRequest->update([
                'status' => 'denied',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'decision_notes' => $request->decision_notes,
            ]);

            Notification::notifyUser(
                $adoptRequest->parent_id,
                "Adopt-a-Teen Request Update",
                "Your application for {$adoptRequest->teen->name} could not be approved at this time." . ($request->decision_notes ? " Reason: {$request->decision_notes}" : ""),
                'adopt_a_teen',
                route('parent.dashboard'),
                'bi-x-circle text-secondary'
            );

            return back()->with('warning', "Adopt-a-Teen request has been denied.");
        }
    }

    /**
     * Record a direct donation into the Adopt-a-Teen Kitty pool.
     */
    public function addDonation(Request $request)
    {
        $admin = Auth::guard('staff')->user();

        $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'donor_name' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();
        if (!$season) {
            return back()->withErrors(['error' => 'No active season selected.']);
        }

        $currentBalance = AdoptATeenKitty::getCurrentBalance($season->id);
        $newBalance = $currentBalance + $request->amount;
        $receiptNumber = Receipt::generateReceiptNumber('DON');

        $receipt = Receipt::create([
            'camp_season_id' => $season->id,
            'receipt_number' => $receiptNumber,
            'type' => 'kitty_donation_in',
            'user_id' => $admin->id,
            'amount' => $request->amount,
            'description' => "Kitty Donation from {$request->donor_name}",
            'meta_data' => [
                'donor_name' => $request->donor_name,
                'reference' => $request->reference,
                'notes' => $request->notes,
                'recorded_by' => $admin->name,
            ],
        ]);

        AdoptATeenKitty::create([
            'camp_season_id' => $season->id,
            'type' => 'donation_in',
            'amount' => $request->amount,
            'balance_after' => $newBalance,
            'description' => "Donation: {$request->donor_name}" . ($request->notes ? " ({$request->notes})" : ""),
            'reference' => $request->reference,
            'receipt_id' => $receipt->id,
            'created_by' => $admin->id,
        ]);

        // Notify Staff
        Notification::notifyStaff(
            "Kitty Donation Received",
            "Donation of KES " . number_format($request->amount, 2) . " from {$request->donor_name} credited to Adopt-a-Teen Kitty (Receipt #{$receiptNumber}).",
            'donation',
            route('backoffice.adopt.index'),
            'bi-piggy-bank-fill text-danger'
        );

        return back()->with('success', "Donation of KES " . number_format($request->amount, 2) . " credited to Adopt-a-Teen Kitty! Receipt #{$receiptNumber} generated.");
    }

    /**
     * Direct Sponsor a camper from the Kitty Pool or an external sponsor.
     */
    public function directSponsor(Request $request)
    {
        $staff = Auth::guard('staff')->user();

        $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'sponsor_type' => ['required', 'in:kitty_pool,direct_donor'],
            'sponsor_name' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $registration = Registration::with('teen.parents')->findOrFail($request->registration_id);
        $season = $registration->campSeason;

        if ($request->amount > $registration->balance_remaining) {
            return back()->withErrors(['amount' => "Sponsorship amount (KES " . number_format($request->amount, 2) . ") exceeds remaining camper balance (KES " . number_format($registration->balance_remaining, 2) . ")."]);
        }

        $sponsorName = $request->sponsor_name ?: ($request->sponsor_type === 'kitty_pool' ? 'Adopt-a-Teen Kitty Pool' : 'Anonymous Church Sponsor');

        DB::transaction(function () use ($request, $registration, $season, $staff, $sponsorName) {
            $receiptNumber = Receipt::generateReceiptNumber('ADP');

            if ($request->sponsor_type === 'kitty_pool') {
                $kittyBalance = AdoptATeenKitty::getCurrentBalance($season->id);
                if ($kittyBalance < $request->amount) {
                    throw new \Exception("Insufficient balance in Kitty Pool (Current: KES " . number_format($kittyBalance, 2) . ").");
                }

                $newBalance = $kittyBalance - $request->amount;

                $receipt = Receipt::create([
                    'camp_season_id' => $season->id,
                    'receipt_number' => $receiptNumber,
                    'type' => 'kitty_adopt_out',
                    'user_id' => $registration->teen->parents->first()?->id ?? $registration->teen_id,
                    'amount' => $request->amount,
                    'description' => "Direct sponsorship from Kitty for {$registration->teen->name}",
                    'meta_data' => [
                        'teen_id' => $registration->teen_id,
                        'registration_id' => $registration->id,
                        'sponsored_by' => $sponsorName,
                        'staff' => $staff->name,
                    ],
                ]);

                AdoptATeenKitty::create([
                    'camp_season_id' => $season->id,
                    'type' => 'adopt_out',
                    'amount' => $request->amount,
                    'balance_after' => $newBalance,
                    'description' => "Direct Sponsorship: {$registration->teen->name}",
                    'reference' => "DIR-SPONSOR-{$registration->id}",
                    'receipt_id' => $receipt->id,
                    'created_by' => $staff->id,
                ]);
            } else {
                $receipt = Receipt::create([
                    'camp_season_id' => $season->id,
                    'receipt_number' => $receiptNumber,
                    'type' => 'direct_payment',
                    'user_id' => $registration->teen->parents->first()?->id ?? $registration->teen_id,
                    'amount' => $request->amount,
                    'description' => "Direct donor sponsorship by {$sponsorName} for {$registration->teen->name}",
                    'meta_data' => [
                        'teen_id' => $registration->teen_id,
                        'registration_id' => $registration->id,
                        'sponsor_name' => $sponsorName,
                        'reference' => $request->reference,
                    ],
                ]);
            }

            // Create Payment record against registration
            Payment::create([
                'camp_season_id' => $season->id,
                'registration_id' => $registration->id,
                'parent_id' => $registration->teen->parents->first()?->id,
                'amount' => $request->amount,
                'source' => $request->sponsor_type === 'kitty_pool' ? 'adopt_a_teen_transfer' : 'direct',
                'reference' => $request->reference ?: "SPONSOR-{$registration->id}",
                'receipt_number' => $receiptNumber,
                'payment_method' => $request->sponsor_type === 'kitty_pool' ? 'Adopt-a-Teen Kitty' : 'Donor Sponsorship',
                'status' => 'completed',
                'created_by' => $staff->id,
            ]);

            // Notify Parent
            if ($parent = $registration->teen->parents->first()) {
                Notification::notifyUser(
                    $parent->id,
                    "Camper Sponsorship Credited!",
                    "A sponsorship payment of KES " . number_format($request->amount, 2) . " from {$sponsorName} was applied to {$registration->teen->name}'s camp fees.",
                    'adopt_a_teen',
                    route('parent.dashboard'),
                    'bi-heart-fill text-danger'
                );
            }

            // Notify Teen
            Notification::notifyUser(
                $registration->teen_id,
                "Tuition Sponsorship Applied",
                "Great news! A sponsorship of KES " . number_format($request->amount, 2) . " from {$sponsorName} was applied to your camp registration.",
                'adopt_a_teen',
                route('teen.dashboard'),
                'bi-gift-fill text-danger'
            );

            // Notify Staff
            Notification::notifyStaff(
                "Camper Directly Sponsored",
                "{$staff->name} applied KES " . number_format($request->amount, 2) . " sponsorship for {$registration->teen->name} ({$sponsorName}).",
                'adopt_a_teen',
                route('backoffice.adopt.index'),
                'bi-heart-fill text-danger'
            );
        });

        return back()->with('success', "KES " . number_format($request->amount, 2) . " sponsorship successfully credited to {$registration->teen->name}!");
    }
}
