<?php

namespace App\Http\Controllers;

use App\Models\AdoptATeenKitty;
use App\Models\AdoptATeenRequest;
use App\Models\CampaignWeeklyBatch;
use App\Models\CampSeason;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('staff')->user();
        $seasonId = $request->query('season_id') ?? session('admin_selected_season_id');

        $season = null;
        if ($seasonId) {
            $season = CampSeason::find($seasonId);
        }
        if (!$season) {
            $season = CampSeason::getActive();
        }

        if ($season) {
            session(['admin_selected_season_id' => $season->id]);
        }

        $allSeasons = CampSeason::orderByDesc('year')->get();

        if (!$season) {
            return view('backoffice.admin.dashboard', [
                'season' => null,
                'allSeasons' => $allSeasons,
                'stats' => [
                    'totalRegistered' => 0,
                    'totalSignedIn' => 0,
                    'totalWithdrawn' => 0,
                    'spotsRemaining' => 0,
                    'capacityPercent' => 0,
                    'attendancePercent' => 0,
                    'totalRevenue' => 0,
                    'targetRevenue' => 0,
                    'revenuePercent' => 0,
                    'kittyBalance' => 0,
                    'totalKittyIn' => 0,
                    'totalKittyOut' => 0,
                    'maleCount' => 0,
                    'femaleCount' => 0,
                    'medicalFlagsCount' => 0,
                    'phonesCarriedCount' => 0,
                    'fullyPaidCount' => 0,
                    'partialPaidCount' => 0,
                    'unpaidCount' => 0,
                ],
                'pendingAdoptRequests' => collect(),
                'pendingBatches' => collect(),
                'recentRegistrations' => collect(),
                'recentPayments' => collect(),
                'recentKittyLedger' => collect(),
                'recentNotifications' => Notification::latest('id')->take(10)->get(),
                'mpesaSettings' => \App\Models\MpesaSetting::getSettings(),
                'allTeens' => collect(),
            ]);
        }

        // Calculate Season Stats
        $totalRegistered = Registration::where('camp_season_id', $season->id)
            ->where('status', '!=', 'withdrawn')
            ->count();
        $totalSignedIn = Registration::where('camp_season_id', $season->id)
            ->where('status', 'signed_in')
            ->count();
        $totalWithdrawn = Registration::where('camp_season_id', $season->id)
            ->where('status', 'withdrawn')
            ->count();
        $spotsRemaining = max(0, $season->capacity - $totalRegistered);

        $totalRevenue = Payment::where('camp_season_id', $season->id)
            ->where('status', 'completed')
            ->sum('amount');

        // Kitty stats
        $currentKittyBalance = AdoptATeenKitty::getCurrentBalance($season->id);
        $totalKittyIn = AdoptATeenKitty::where('camp_season_id', $season->id)
            ->whereIn('type', ['donation_in', 'campaign_profit_in'])
            ->sum('amount');
        $totalKittyOut = AdoptATeenKitty::where('camp_season_id', $season->id)
            ->where('type', 'adopt_out')
            ->sum('amount');

        // Demographics & Medical Readiness
        $maleCount = Registration::where('camp_season_id', $season->id)
            ->where('status', '!=', 'withdrawn')
            ->whereHas('teen', fn($q) => $q->where('gender', 'male'))
            ->count();
        $femaleCount = Registration::where('camp_season_id', $season->id)
            ->where('status', '!=', 'withdrawn')
            ->whereHas('teen', fn($q) => $q->where('gender', 'female'))
            ->count();
        $medicalFlagsCount = Registration::where('camp_season_id', $season->id)
            ->where('status', '!=', 'withdrawn')
            ->where(function ($q) {
                $q->whereNotNull('medical_conditions')->where('medical_conditions', '!=', '')
                  ->orWhere(function ($sq) {
                      $sq->whereNotNull('medication_notes')->where('medication_notes', '!=', '');
                  });
            })
            ->count();
        $phonesCarriedCount = Registration::where('camp_season_id', $season->id)
            ->where('status', '!=', 'withdrawn')
            ->where('phone_carried', true)
            ->count();

        // Payment status breakdowns
        $activeRegistrations = Registration::where('camp_season_id', $season->id)
            ->where('status', '!=', 'withdrawn')
            ->with('payments')
            ->get();
        $fullyPaidCount = 0;
        $partialPaidCount = 0;
        $unpaidCount = 0;
        foreach ($activeRegistrations as $r) {
            $paid = $r->payments->where('status', 'completed')->sum('amount');
            if ($paid >= $season->price) {
                $fullyPaidCount++;
            } elseif ($paid > 0) {
                $partialPaidCount++;
            } else {
                $unpaidCount++;
            }
        }

        $targetRevenue = $season->capacity * $season->price;
        $revenuePercent = $targetRevenue > 0 ? min(100, round(($totalRevenue / $targetRevenue) * 100, 1)) : 0;
        $capacityPercent = $season->capacity > 0 ? min(100, round(($totalRegistered / $season->capacity) * 100, 1)) : 0;
        $attendancePercent = $totalRegistered > 0 ? min(100, round(($totalSignedIn / $totalRegistered) * 100, 1)) : 0;

        // Queues
        $pendingAdoptRequests = AdoptATeenRequest::where('camp_season_id', $season->id)
            ->whereIn('status', ['pending', 'approved-awaiting-funds'])
            ->with(['parent', 'teen'])
            ->get();

        $pendingBatches = CampaignWeeklyBatch::where('camp_season_id', $season->id)
            ->where('status', 'pending_review')
            ->with('approver')
            ->get();

        // Recent streams
        $recentRegistrations = Registration::where('camp_season_id', $season->id)
            ->with(['teen.parents.teens', 'payments'])
            ->latest('id')
            ->take(50)
            ->get();

        $recentPayments = Payment::where('camp_season_id', $season->id)
            ->where('status', 'completed')
            ->with(['registration.teen', 'parent'])
            ->latest('id')
            ->take(8)
            ->get();

        $recentKittyLedger = AdoptATeenKitty::where('camp_season_id', $season->id)
            ->with('receipt')
            ->latest('id')
            ->take(12)
            ->get();

        $recentNotifications = Notification::where(function ($q) use ($season) {
            $q->where('camp_season_id', $season->id)
              ->orWhereNull('camp_season_id');
        })
        ->latest('id')
        ->take(25)
        ->get();

        return view('backoffice.admin.dashboard', [
            'season' => $season,
            'allSeasons' => $allSeasons,
            'stats' => [
                'totalRegistered' => $totalRegistered,
                'totalSignedIn' => $totalSignedIn,
                'totalWithdrawn' => $totalWithdrawn,
                'spotsRemaining' => $spotsRemaining,
                'capacityPercent' => $capacityPercent,
                'attendancePercent' => $attendancePercent,
                'totalRevenue' => $totalRevenue,
                'targetRevenue' => $targetRevenue,
                'revenuePercent' => $revenuePercent,
                'kittyBalance' => $currentKittyBalance,
                'totalKittyIn' => $totalKittyIn,
                'totalKittyOut' => $totalKittyOut,
                'maleCount' => $maleCount,
                'femaleCount' => $femaleCount,
                'medicalFlagsCount' => $medicalFlagsCount,
                'phonesCarriedCount' => $phonesCarriedCount,
                'fullyPaidCount' => $fullyPaidCount,
                'partialPaidCount' => $partialPaidCount,
                'unpaidCount' => $unpaidCount,
            ],
            'pendingAdoptRequests' => $pendingAdoptRequests,
            'pendingBatches' => $pendingBatches,
            'recentRegistrations' => $recentRegistrations,
            'recentPayments' => $recentPayments,
            'recentKittyLedger' => $recentKittyLedger,
            'recentNotifications' => $recentNotifications,
            'mpesaSettings' => \App\Models\MpesaSetting::getSettings(),
            'allTeens' => User::where('role', 'teen')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Database search & filter across all teens and registrations.
     */
    public function searchDatabase(Request $request)
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();
        $allSeasons = CampSeason::orderBy('year', 'desc')->get();

        $query = Registration::with(['teen.parents.teens', 'payments', 'campSeason']);

        if ($request->filled('season_id')) {
            $query->where('camp_season_id', $request->season_id);
        } elseif ($season) {
            $query->where('camp_season_id', $season->id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('teen', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('teen.parents', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('gender')) {
            $gender = $request->gender;
            $query->whereHas('teen', function ($q) use ($gender) {
                $q->where('gender', $gender);
            });
        }

        if ($request->filled('has_medical')) {
            if ($request->has_medical === 'yes') {
                $query->where(function ($q) {
                    $q->whereNotNull('medical_conditions')->where('medical_conditions', '!=', '')
                      ->orWhere(function ($sq) {
                          $sq->whereNotNull('medication_notes')->where('medication_notes', '!=', '');
                      });
                });
            } elseif ($request->has_medical === 'no') {
                $query->where(function ($q) {
                    $q->whereNull('medical_conditions')->orWhere('medical_conditions', '');
                })->where(function ($q) {
                    $q->whereNull('medication_notes')->orWhere('medication_notes', '');
                });
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->latest('id')->paginate(20)->withQueryString();

        return view('backoffice.admin.database-search', [
            'season' => $season,
            'allSeasons' => $allSeasons,
            'registrations' => $registrations,
            'filters' => $request->all(),
            'allTeens' => User::where('role', 'teen')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    /**
     * Manual Kitty Donation entry (Mission tithe / Church fund / Sponsor).
     */
    public function addKittyDonation(Request $request)
    {
        $admin = Auth::guard('staff')->user();
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        $request->validate([
            'donor_name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $receiptNumber = Receipt::generateReceiptNumber('KTY');
        $currentBalance = AdoptATeenKitty::getCurrentBalance($season->id);
        $newBalance = $currentBalance + $request->amount;

        $receipt = Receipt::create([
            'camp_season_id' => $season->id,
            'receipt_number' => $receiptNumber,
            'type' => 'kitty_donation_in',
            'user_id' => null,
            'amount' => $request->amount,
            'description' => "Direct kitty donation from {$request->donor_name}",
            'meta_data' => [
                'donor_name' => $request->donor_name,
                'reference' => $request->reference,
                'entered_by' => $admin->name,
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
            route('backoffice.admin.dashboard'),
            'bi-piggy-bank-fill text-danger'
        );

        return back()->with('success', "Donation of KES " . number_format($request->amount, 2) . " recorded into Kitty! Receipt #{$receiptNumber} generated.");
    }

    /**
     * Approve or deny Adopt-a-Teen request (shared logic with Pastor).
     */
    public function reviewAdoptRequest(Request $request, AdoptATeenRequest $adoptRequest)
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
                return back()->withErrors(['error' => "Insufficient funds in Kitty (Current Balance: \${$kittyBalance}). You can select 'Approve - Awaiting Funds' instead."]);
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

                // 3. Notify Staff / Admin
                Notification::notifyStaff(
                    "Adopt-a-Teen Aid Granted",
                    "{$reviewer->name} approved KES " . number_format($adoptRequest->amount_requested, 2) . " scholarship for {$adoptRequest->teen->name} from Kitty pool.",
                    'adopt_a_teen',
                    route('backoffice.admin.dashboard'),
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
     * Season Management: List, Create, Update, Set Active.
     */
    public function seasons()
    {
        $seasons = CampSeason::withCount(['registrations', 'payments'])->orderByDesc('year')->get();
        return view('backoffice.admin.seasons', ['seasons' => $seasons]);
    }

    public function createSeason(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'digits:4'],
            'theme' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'venue' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,active,archived'],
            'description' => ['nullable', 'string'],
        ]);

        if ($request->status === 'active') {
            CampSeason::where('status', 'active')->update(['status' => 'archived']);
        }

        $season = CampSeason::create($request->all());

        return back()->with('success', "Camp Season '{$season->name}' created successfully!");
    }

    public function updateSeason(Request $request, CampSeason $season)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'year' => ['required', 'digits:4'],
            'theme' => ['nullable', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'venue' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,active,archived'],
            'description' => ['nullable', 'string'],
            'landing_subtitle' => ['nullable', 'string'],
            'poster' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'core_values' => ['nullable', 'array'],
        ]);

        if ($request->status === 'active') {
            CampSeason::where('id', '!=', $season->id)->where('status', 'active')->update(['status' => 'archived']);
        }

        $data = $request->except(['poster', 'core_values', 'remove_poster']);

        if ($request->boolean('remove_poster')) {
            if ($season->poster_path) {
                \App\Services\SupabaseStorageService::delete($season->poster_path);
            }
            $data['poster_path'] = null;
        } elseif ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'poster_season_' . $season->id . '_' . time() . '.' . $extension;
            
            // Backup locally in public/images/posters
            $directory = public_path('images/posters');
            if (!file_exists($directory)) {
                @mkdir($directory, 0755, true);
            }
            @copy($file->getRealPath(), $directory . DIRECTORY_SEPARATOR . $filename);

            // Upload permanently to Supabase Storage
            $storedPath = \App\Services\SupabaseStorageService::upload($file, 'posters/' . $filename);
            $data['poster_path'] = $storedPath;
        }

        if ($request->has('core_values') && is_array($request->core_values)) {
            $formattedValues = [];
            foreach ($request->core_values as $item) {
                if (!empty($item['title'])) {
                    $formattedValues[] = [
                        'title' => trim($item['title']),
                        'icon' => !empty($item['icon']) ? trim($item['icon']) : 'bi-stars',
                        'description' => trim($item['description'] ?? ''),
                    ];
                }
            }
            $data['core_values'] = $formattedValues;
        }

        $season->update($data);

        return back()->with('success', "Camp Season '{$season->name}' updated successfully!");
    }

    /**
     * User & Access Management.
     */
    public function users(Request $request)
    {
        $query = User::withCount('registrations')->with(['parents.teens', 'teens.parents.teens']);
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);
        $allTeens = User::where('role', 'teen')->orderBy('name')->get(['id', 'name', 'email']);
        return view('backoffice.admin.users', [
            'users' => $users, 
            'roleFilter' => $request->role,
            'allTeens' => $allTeens,
        ]);
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'in:teen,parent,admin,pastor,registration,campaign,campaign_head'],
            'pin' => ['nullable', 'digits:4'],
            'phone' => ['nullable', 'string'],
        ]);

        $pin = $request->filled('pin') ? $request->pin : '0000';

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'pin' => Hash::make($pin),
            'role' => $request->role,
            'phone' => $request->phone,
            'pin_reset_required' => ($pin === '0000'),
        ]);

        return back()->with('success', "User {$request->name} created with initial PIN {$pin}.");
    }

    public function updateUser(Request $request, User $user)
    {
        $admin = Auth::guard('staff')->user();

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'         => ['nullable', 'string', 'max:50'],
            'role'          => ['required', 'in:teen,parent,admin,pastor,registration,campaign,campaign_head'],
            'gender'        => ['nullable', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date'],
            'address'       => ['nullable', 'string', 'max:500'],
            'parent_name'   => ['nullable', 'string', 'max:255'],
            'parent_email'  => ['nullable', 'email'],
            'parent_phone'  => ['nullable', 'string', 'max:50'],
            'parent_relationship' => ['nullable', 'string', 'max:50'],
            'add_sibling_id' => ['nullable', 'exists:users,id'],
        ]);

        $previousRole = $user->role;
        $roleChanged  = $previousRole !== $request->role;

        $user->update([
            'name'          => $request->name,
            'email'         => $request->email,
            'phone'         => $request->phone,
            'role'          => $request->role,
            'gender'        => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'address'       => $request->address,
        ]);

        if ($request->boolean('force_pin_reset')) {
            $user->update([
                'pin'               => Hash::make('0000'),
                'pin_reset_required' => true,
            ]);
        }

        // If parent details provided for a teen, update/link parent
        if ($user->role === 'teen' && ($request->filled('parent_name') || $request->filled('parent_email') || $request->filled('parent_phone'))) {
            $parent = $user->parents()->first();
            $relationship = $request->parent_relationship ?: 'Parent';

            if ($parent) {
                $parentUpdate = [];
                if ($request->filled('parent_name')) $parentUpdate['name'] = $request->parent_name;
                if ($request->filled('parent_phone')) $parentUpdate['phone'] = $request->parent_phone;
                if ($request->filled('parent_email') && $request->parent_email !== $parent->email) {
                    $existing = User::where('email', $request->parent_email)->where('id', '!=', $parent->id)->first();
                    if (!$existing) {
                        $parentUpdate['email'] = $request->parent_email;
                    }
                }
                if (!empty($parentUpdate)) {
                    $parent->update($parentUpdate);
                }
                $user->parents()->updateExistingPivot($parent->id, ['relationship' => $relationship]);
            } else {
                $parentEmail = $request->parent_email ?: ('parent.' . strtolower(preg_replace('/[^a-z0-9]/', '', $request->parent_name ?: 'guardian')) . rand(100, 999) . '@family.local');
                $parent = User::where('email', $parentEmail)->first();

                if (!$parent) {
                    $parent = User::create([
                        'name' => $request->parent_name ?: 'Parent / Guardian',
                        'email' => $parentEmail,
                        'phone' => $request->parent_phone,
                        'role' => 'parent',
                        'pin' => Hash::make('0000'),
                        'pin_reset_required' => true,
                    ]);
                } else {
                    if ($request->filled('parent_name')) $parent->name = $request->parent_name;
                    if ($request->filled('parent_phone')) $parent->phone = $request->parent_phone;
                    $parent->save();
                }

                $user->parents()->syncWithoutDetaching([
                    $parent->id => ['relationship' => $relationship]
                ]);
            }

            if ($request->filled('add_sibling_id')) {
                $sibling = User::find($request->add_sibling_id);
                if ($sibling && $sibling->isTeen() && $sibling->id !== $user->id) {
                    $sibling->parents()->syncWithoutDetaching([
                        $parent->id => ['relationship' => $relationship]
                    ]);
                }
            }
        }

        // Notify user about the change
        if (in_array($user->role, ['teen', 'parent'])) {
            Notification::notifyUser(
                $user->id,
                'Your Account Has Been Updated',
                "Your account details were updated by admin staff ({$admin->name}). " . ($roleChanged ? "Your role was changed to {$request->role}." : ''),
                'registration',
                route(($user->isTeen() ? 'teen' : 'parent') . '.dashboard'),
                'bi-person-gear text-info'
            );
        }

        Notification::notifyStaff(
            'User Account Updated',
            "{$admin->name} updated account for {$user->name} ({$user->email})." . ($roleChanged ? " Role changed: {$previousRole} → {$request->role}." : ''),
            'registration',
            route('backoffice.admin.users'),
            'bi-person-check-fill text-info'
        );

        return back()->with('success', "✓ {$user->name}'s account updated successfully.");
    }

    /**
     * Update Teen profile, linked Parent information, and family relationships.
     */
    public function updateTeenFamily(Request $request, User $user)
    {
        $admin = Auth::guard('staff')->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:male,female'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string', 'max:500'],
            'parent_name' => ['nullable', 'string', 'max:255'],
            'parent_email' => ['nullable', 'email'],
            'parent_phone' => ['nullable', 'string', 'max:50'],
            'parent_relationship' => ['nullable', 'string', 'max:50'],
            'add_sibling_id' => ['nullable', 'exists:users,id'],
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
        ]);

        $relationship = $request->parent_relationship ?: 'Parent';

        if ($request->filled('parent_name') || $request->filled('parent_email') || $request->filled('parent_phone')) {
            $parent = $user->parents()->first();

            if ($parent) {
                $parentUpdate = [];
                if ($request->filled('parent_name')) $parentUpdate['name'] = $request->parent_name;
                if ($request->filled('parent_phone')) $parentUpdate['phone'] = $request->parent_phone;
                if ($request->filled('parent_email') && $request->parent_email !== $parent->email) {
                    $existing = User::where('email', $request->parent_email)->where('id', '!=', $parent->id)->first();
                    if (!$existing) {
                        $parentUpdate['email'] = $request->parent_email;
                    }
                }
                if (!empty($parentUpdate)) {
                    $parent->update($parentUpdate);
                }
                $user->parents()->updateExistingPivot($parent->id, ['relationship' => $relationship]);
            } else {
                $parentEmail = $request->parent_email ?: ('parent.' . strtolower(preg_replace('/[^a-z0-9]/', '', $request->parent_name ?: 'guardian')) . rand(100, 999) . '@family.local');
                $parent = User::where('email', $parentEmail)->first();

                if (!$parent) {
                    $parent = User::create([
                        'name' => $request->parent_name ?: 'Parent / Guardian',
                        'email' => $parentEmail,
                        'phone' => $request->parent_phone,
                        'role' => 'parent',
                        'pin' => Hash::make('0000'),
                        'pin_reset_required' => true,
                    ]);
                } else {
                    if ($request->filled('parent_name')) $parent->name = $request->parent_name;
                    if ($request->filled('parent_phone')) $parent->phone = $request->parent_phone;
                    $parent->save();
                }

                $user->parents()->syncWithoutDetaching([
                    $parent->id => ['relationship' => $relationship]
                ]);
            }

            if ($request->filled('add_sibling_id')) {
                $sibling = User::find($request->add_sibling_id);
                if ($sibling && $sibling->isTeen() && $sibling->id !== $user->id) {
                    $sibling->parents()->syncWithoutDetaching([
                        $parent->id => ['relationship' => $relationship]
                    ]);
                }
            }
        }

        Notification::notifyStaff(
            'Camper & Family Updated',
            "{$admin->name} updated family information for {$user->name}.",
            'registration',
            route('backoffice.admin.users'),
            'bi-people-fill text-info'
        );

        return back()->with('success', "✓ {$user->name}'s details, parent info, and siblings updated successfully.");
    }

    /**
     * Permanently delete a user account.
     */
    public function deleteUser(User $user)
    {
        $admin = Auth::guard('staff')->user();
        if ($user->id === $admin->id) {
            return back()->with('warning', 'You cannot delete your own active administrator account.');
        }

        $name = $user->name;
        $email = $user->email;
        $user->delete();

        Notification::notifyStaff(
            "User Account Deleted: {$name}",
            "{$admin->name} permanently deleted account for {$name} ({$email}).",
            'security',
            route('backoffice.admin.users'),
            'bi-trash3-fill text-danger'
        );

        return back()->with('success', "User account for {$name} ({$email}) has been permanently deleted.");
    }

    /**
     * Toggle Registration Open / Closed for a Camp Season.
     */
    public function toggleRegistration(CampSeason $season)
    {
        $admin = Auth::guard('staff')->user();
        $isOpen = $season->isRegistrationOpen();
        $season->update([
            'is_registration_open' => !$isOpen,
        ]);

        $newState = !$isOpen ? 'Opened' : 'Closed';

        Notification::notifyStaff(
            "Registration {$newState}: {$season->name}",
            "{$admin->name} changed registration status to {$newState}.",
            'system',
            route('backoffice.admin.dashboard'),
            !$isOpen ? 'bi-door-open-fill text-success' : 'bi-door-closed-fill text-danger'
        );

        return back()->with('success', "Registration for {$season->name} is now {$newState}.");
    }

    /**
     * Suspend or Reactivate a user account.
     */
    public function toggleUserSuspension(User $user)
    {
        $admin = Auth::guard('staff')->user();
        if ($user->id === $admin->id) {
            return back()->with('warning', 'You cannot suspend your own active administrator account.');
        }

        $newStatus = !$user->isSuspended();
        $user->update([
            'is_suspended' => $newStatus,
        ]);

        $statusText = $newStatus ? 'Suspended' : 'Reactivated';

        Notification::notifyStaff(
            "User Account {$statusText}: {$user->name}",
            "{$admin->name} has {$statusText} user {$user->name} ({$user->email}).",
            'system',
            route('backoffice.admin.users'),
            $newStatus ? 'bi-slash-circle text-danger' : 'bi-check-circle text-success'
        );

        return back()->with('success', "User account for {$user->name} is now {$statusText}.");
    }


    /**
     * Clean/Reset database leaving ONLY the system admin who performed the reset.
     * All other records (registrations, product catalogue, donations, receipts, auxiliary users) are cleared.
     */
    public function resetDatabase(Request $request)
    {
        $admin = Auth::guard('staff')->user();

        $request->validate([
            'admin_pin' => ['required', 'string', 'size:4'],
        ]);

        if (!Hash::check($request->admin_pin, $admin->pin)) {
            return back()->with('error', 'Authentication failed: Incorrect 4-digit Admin PIN.');
        }

        try {
            // Snapshot admin attributes to guarantee preservation
            $adminAttributes = [
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'role' => 'admin',
                'pin' => $admin->pin,
                'pin_reset_required' => false,
                'is_suspended' => false,
                'created_at' => $admin->created_at ?? now(),
                'updated_at' => now(),
            ];

            Schema::disableForeignKeyConstraints();

            // 1. Clear all registrations, payments, and receipts
            DB::table('payments')->delete();
            DB::table('receipts')->delete();
            DB::table('registrations')->delete();
            DB::table('parent_teen')->delete();

            // 2. Clear product catalogue, sales, and weekly batches
            DB::table('campaign_sales')->delete();
            DB::table('campaign_weekly_batches')->delete();
            DB::table('campaign_products')->delete();

            // 3. Clear adopt-a-teen requests and kitty donations
            DB::table('adopt_a_teen_requests')->delete();
            DB::table('adopt_a_teen_kitty')->delete();

            // 4. Clear packing lists, forms, submissions, and values
            DB::table('packing_lists')->delete();
            DB::table('form_submission_values')->delete();
            DB::table('form_submissions')->delete();
            DB::table('form_fields')->delete();
            DB::table('forms')->delete();

            // 5. Clear notifications, seasons, and desk intake configs
            DB::table('notifications')->delete();
            DB::table('camp_seasons')->delete();
            if (Schema::hasTable('desk_form_configs')) {
                DB::table('desk_form_configs')->delete();
            }

            // 6. Delete ALL users except the system admin performing this reset
            DB::table('users')->where('id', '!=', $admin->id)->delete();

            Schema::enableForeignKeyConstraints();

            // Clear selected season from session
            session()->forget('admin_selected_season_id');

            // Ensure preserved admin is present and re-authenticated
            $freshAdmin = User::find($admin->id);
            if (!$freshAdmin) {
                $freshAdmin = User::create($adminAttributes);
            }
            Auth::guard('staff')->login($freshAdmin);

            return redirect()->route('backoffice.admin.dashboard')
                ->with('success', 'Database reset complete: All registrations, product catalogue, donations, and records have been cleaned. Only your system administrator account has been retained.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reset database: ' . $e->getMessage());
        }
    }

    /**
     * Update M-Pesa Daraja Settings (Paybill & 2 Account Types)
     */
    public function updateMpesaSettings(Request $request)
    {
        $request->validate([
            'paybill_number' => ['required', 'string', 'max:50'],
            'camp_fee_account' => ['required', 'string', 'max:100'],
            'adopt_account' => ['required', 'string', 'max:100'],
            'consumer_key' => ['nullable', 'string', 'max:255'],
            'consumer_secret' => ['nullable', 'string', 'max:255'],
            'passkey' => ['nullable', 'string', 'max:255'],
            'environment' => ['required', 'in:sandbox,live'],
            'is_mock_enabled' => ['nullable'],
        ]);

        $settings = \App\Models\MpesaSetting::getSettings();
        $settings->update([
            'paybill_number' => $request->paybill_number,
            'camp_fee_account' => $request->camp_fee_account,
            'adopt_account' => $request->adopt_account,
            'consumer_key' => $request->consumer_key,
            'consumer_secret' => $request->consumer_secret,
            'passkey' => $request->passkey,
            'environment' => $request->environment,
            'is_mock_enabled' => $request->boolean('is_mock_enabled'),
        ]);

        return back()->with('success', 'M-Pesa Daraja Paybill and dual account settings (Camp Fees & Adopt-a-Teen) updated successfully.');
    }

    /**
     * Send test email notification via Brevo.
     */
    public function testBrevoEmail(Request $request, \App\Services\BrevoMailService $brevo)
    {
        $admin = Auth::guard('staff')->user();
        $targetEmail = $request->input('email', $admin->email ?: 'b47b4b001@smtp-brevo.com');

        $sent = $brevo->sendEmail(
            toEmail: $targetEmail,
            toName: $admin->name,
            subject: '⚡ Teen Camp - Brevo Email Notification Test',
            htmlContent: '<h2>Teen Camp Email Notification System</h2><p>Brevo SMTP and API keys are successfully configured on Teen Camp system.</p><p>Recipient: ' . e($targetEmail) . '<br>Sent at: ' . now()->toDayDateTimeString() . '</p>'
        );

        if ($sent) {
            return back()->with('success', "Test email successfully dispatched to {$targetEmail} via Brevo!");
        }

        return back()->with('error', "Could not dispatch email to {$targetEmail}. If Brevo returned IP authorization error, add your IP in Brevo: https://app.brevo.com/security/authorised_ips");
    }
}

