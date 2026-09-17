<?php

namespace App\Http\Controllers;

use App\Models\CampSeason;
use App\Models\Registration;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $activeSeason = CampSeason::getActive();
        $registeredCount = 0;
        $spotsRemaining = 0;

        $kittyBalance = 0;
        $kittyDisbursed = 0;

        if ($activeSeason) {
            $registeredCount = Registration::where('camp_season_id', $activeSeason->id)
                ->where('status', '!=', 'withdrawn')
                ->count();
            $spotsRemaining = max(0, $activeSeason->capacity - $registeredCount);
            $kittyBalance = \App\Models\AdoptATeenKitty::getCurrentBalance($activeSeason->id);
            $kittyDisbursed = \App\Models\AdoptATeenKitty::where('camp_season_id', $activeSeason->id)
                ->where('type', 'adopt_out')
                ->sum('amount');
        }

        return view('public.landing', [
            'season' => $activeSeason,
            'registeredCount' => $registeredCount,
            'spotsRemaining' => $spotsRemaining,
            'kittyBalance' => $kittyBalance,
            'kittyDisbursed' => $kittyDisbursed,
        ]);
    }
}
