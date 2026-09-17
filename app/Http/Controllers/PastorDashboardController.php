<?php

namespace App\Http\Controllers;

use App\Models\AdoptATeenKitty;
use App\Models\AdoptATeenRequest;
use App\Models\CampSeason;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PastorDashboardController extends Controller
{
    public function index()
    {
        $pastor = Auth::guard('staff')->user();
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        if (!$season) {
            return view('backoffice.pastor.dashboard', [
                'season' => null,
                'stats' => [],
                'pendingAdoptRequests' => collect(),
            ]);
        }

        $totalRegistered = Registration::where('camp_season_id', $season->id)
            ->where('status', '!=', 'withdrawn')
            ->count();
        $totalSignedIn = Registration::where('camp_season_id', $season->id)
            ->where('status', 'signed_in')
            ->count();
        $totalRevenue = Payment::where('camp_season_id', $season->id)
            ->where('status', 'completed')
            ->sum('amount');
        $kittyBalance = AdoptATeenKitty::getCurrentBalance($season->id);

        $pendingAdoptRequests = AdoptATeenRequest::where('camp_season_id', $season->id)
            ->whereIn('status', ['pending', 'approved-awaiting-funds'])
            ->with(['parent', 'teen'])
            ->get();

        return view('backoffice.pastor.dashboard', [
            'season' => $season,
            'stats' => [
                'totalRegistered' => $totalRegistered,
                'totalSignedIn' => $totalSignedIn,
                'totalRevenue' => $totalRevenue,
                'kittyBalance' => $kittyBalance,
            ],
            'pendingAdoptRequests' => $pendingAdoptRequests,
        ]);
    }

    public function broadcastNotification(Request $request)
    {
        $pastor = Auth::guard('staff')->user();
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'target_role' => ['required', 'in:all,teen,parent,staff'],
        ]);

        Notification::create([
            'camp_season_id' => $season?->id,
            'user_id' => null,
            'target_role' => $request->target_role,
            'title' => $request->title,
            'message' => $request->message,
            'type' => 'system',
            'created_by' => $pastor->id,
        ]);

        return back()->with('success', 'System-wide announcement broadcasted to ' . ucfirst($request->target_role) . ' successfully!');
    }
}
