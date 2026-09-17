<?php

namespace App\Providers;

use App\Models\CampSeason;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                if (Schema::hasTable('camp_seasons')) {
                    $selectedSeasonId = session('admin_selected_season_id');
                    $season = null;
                    if ($selectedSeasonId) {
                        $season = CampSeason::find($selectedSeasonId);
                    }
                    if (!$season) {
                        $season = CampSeason::getActive();
                    }
                    $allSeasons = CampSeason::orderByDesc('year')->get();
                    $view->with('currentSeason', $season);
                    $view->with('allSeasons', $allSeasons);
                }

                $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();
                if ($user && Schema::hasTable('notifications')) {
                    $unreadCount = Notification::where(function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                          ->orWhere('target_role', 'all')
                          ->orWhere('target_role', $user->role);
                    })->where('is_read', false)->count();

                    $view->with('currentUser', $user);
                    $view->with('unreadNotificationsCount', $unreadCount);
                }
            } catch (\Throwable $e) {
                // Ignore during early bootstrap/migrations
            }
        });
    }
}
