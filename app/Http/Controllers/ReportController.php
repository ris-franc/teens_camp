<?php

namespace App\Http\Controllers;

use App\Models\AdoptATeenKitty;
use App\Models\AdoptATeenRequest;
use App\Models\CampaignProduct;
use App\Models\CampaignSale;
use App\Models\CampaignWeeklyBatch;
use App\Models\CampSeason;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    /**
     * 1. Official Camp Registrations Roster Report
     */
    public function registrationsPdf(Request $request)
    {
        $season = CampSeason::find($request->season_id) ?? CampSeason::getActive();
        $registrations = collect();
        if ($season) {
            $registrations = Registration::where('camp_season_id', $season->id)
                ->with(['teen.parents', 'payments'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $stats = [
            'total' => $registrations->count(),
            'boys' => $registrations->filter(fn($r) => $r->teen?->gender === 'male')->count(),
            'girls' => $registrations->filter(fn($r) => $r->teen?->gender === 'female')->count(),
            'fullyPaid' => $registrations->filter(fn($r) => $r->balance_remaining <= 0)->count(),
            'totalPaid' => $registrations->sum('total_paid'),
            'totalBalance' => $registrations->sum('balance_remaining'),
        ];

        return view('reports.registrations', [
            'season' => $season,
            'registrations' => $registrations,
            'stats' => $stats,
            'generatedBy' => Auth::guard('staff')->user()->name ?? 'System Administrator',
            'generatedAt' => now()->format('M d, Y • h:i A'),
        ]);
    }

    /**
     * 2. Campaign Merchandise Sales & Profit Report
     */
    public function salesPdf(Request $request)
    {
        $season = CampSeason::find($request->season_id) ?? CampSeason::getActive();
        $sales = collect();
        $products = collect();
        $batches = collect();

        if ($season) {
            $sales = CampaignSale::where('camp_season_id', $season->id)
                ->with(['product', 'batch', 'seller'])
                ->orderBy('created_at', 'desc')
                ->get();
            $products = CampaignProduct::where('camp_season_id', $season->id)->get();
            $batches = CampaignWeeklyBatch::where('camp_season_id', $season->id)->get();
        }

        $totalRevenue = $sales->sum('total_amount');
        $totalProfit = $sales->sum('total_profit');
        $totalUnits = $sales->sum('quantity');

        return view('reports.sales', [
            'season' => $season,
            'sales' => $sales,
            'products' => $products,
            'batches' => $batches,
            'totalRevenue' => $totalRevenue,
            'totalProfit' => $totalProfit,
            'totalUnits' => $totalUnits,
            'generatedBy' => Auth::guard('staff')->user()->name ?? 'System Administrator',
            'generatedAt' => now()->format('M d, Y • h:i A'),
        ]);
    }

    /**
     * 3. Adopt-a-Teen Sponsorship & Kitty Ledger Report
     */
    public function adoptPdf(Request $request)
    {
        $season = CampSeason::find($request->season_id) ?? CampSeason::getActive();
        $requests = collect();
        $ledger = collect();
        $kittyBalance = 0;

        if ($season) {
            $requests = AdoptATeenRequest::where('camp_season_id', $season->id)
                ->with(['teen', 'parent'])
                ->orderBy('created_at', 'desc')
                ->get();
            $ledger = AdoptATeenKitty::where('camp_season_id', $season->id)
                ->orderBy('created_at', 'desc')
                ->get();
            $kittyBalance = AdoptATeenKitty::getCurrentBalance($season->id);
        }

        $totalDonationsIn = $ledger->where('type', 'donation_in')->sum('amount');
        $totalDisbursedOut = $ledger->where('type', 'disbursement_out')->sum('amount');

        return view('reports.adopt', [
            'season' => $season,
            'requests' => $requests,
            'ledger' => $ledger,
            'kittyBalance' => $kittyBalance,
            'totalDonationsIn' => $totalDonationsIn,
            'totalDisbursedOut' => $totalDisbursedOut,
            'generatedBy' => Auth::guard('staff')->user()->name ?? 'System Administrator',
            'generatedAt' => now()->format('M d, Y • h:i A'),
        ]);
    }

    /**
     * 4. Camp Payments & Receipts Financial Report
     */
    public function paymentsPdf(Request $request)
    {
        $season = CampSeason::find($request->season_id) ?? CampSeason::getActive();
        $payments = collect();

        if ($season) {
            $payments = Payment::where('camp_season_id', $season->id)
                ->with(['registration.teen', 'parent'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $totalCollected = $payments->where('status', 'completed')->sum('amount');
        $mpesaCollected = $payments->where('status', 'completed')->filter(fn($p) => str_contains(strtolower($p->payment_method), 'mpesa') || str_contains(strtolower($p->payment_method), 'm-pesa'))->sum('amount');

        return view('reports.payments', [
            'season' => $season,
            'payments' => $payments,
            'totalCollected' => $totalCollected,
            'mpesaCollected' => $mpesaCollected,
            'generatedBy' => Auth::guard('staff')->user()->name ?? 'System Administrator',
            'generatedAt' => now()->format('M d, Y • h:i A'),
        ]);
    }

    /**
     * 5. Filtered Database Search Export Report
     */
    public function databasePdf(Request $request)
    {
        $seasonId = $request->query('season_id');
        $search = $request->query('q');
        $status = $request->query('status');
        $gender = $request->query('gender');

        $query = Registration::with(['teen.parents', 'campSeason', 'payments']);

        if ($seasonId && $seasonId !== 'all') {
            $query->where('camp_season_id', $seasonId);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($gender && $gender !== 'all') {
            $query->whereHas('teen', fn($q) => $q->where('gender', $gender));
        }
        if ($search) {
            $query->where(function ($sub) use ($search) {
                $sub->whereHas('teen', fn($t) => $t->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                    ->orWhereHas('teen.parents', fn($p) => $p->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();
        $activeSeason = CampSeason::getActive();

        return view('reports.database', [
            'registrations' => $registrations,
            'searchQuery' => $search,
            'filterStatus' => $status,
            'filterGender' => $gender,
            'season' => $activeSeason,
            'generatedBy' => Auth::guard('staff')->user()->name ?? 'System Administrator',
            'generatedAt' => now()->format('M d, Y • h:i A'),
        ]);
    }
}
