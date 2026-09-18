<?php

namespace App\Providers;

use App\Models\CampSeason;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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
        if (app()->environment('production') || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // Fast, request-memoized view composer (queries database at most once per request)
        View::composer('*', function ($view) {
            try {
                static $memoizedSeason = null;
                static $memoizedAllSeasons = null;
                static $memoizedUser = null;
                static $memoizedUnreadCount = null;

                if (app()->runningUnitTests() || $memoizedAllSeasons === null) {
                    $memoizedAllSeasons = CampSeason::orderByDesc('year')->get();
                    $selectedSeasonId = session('admin_selected_season_id');
                    $season = null;
                    if ($selectedSeasonId) {
                        $season = $memoizedAllSeasons->firstWhere('id', $selectedSeasonId);
                    }
                    if (!$season) {
                        $season = $memoizedAllSeasons->firstWhere('status', 'active') ?? $memoizedAllSeasons->first();
                    }
                    $memoizedSeason = $season;
                }

                $view->with('currentSeason', $memoizedSeason);
                $view->with('allSeasons', $memoizedAllSeasons);

                $user = Auth::guard('staff')->user() ?? Auth::guard('web')->user();
                if ($user) {
                    static $memoizedTopNotifs = null;
                    if (app()->runningUnitTests() || $memoizedUser !== $user->id) {
                        $memoizedUser = $user->id;
                        $memoizedTopNotifs = Notification::where(function ($q) use ($user) {
                            $q->where('user_id', $user->id)
                              ->orWhere('target_role', 'all')
                              ->orWhere('target_role', $user->role);
                        })->latest('id')->take(6)->get();

                        $memoizedUnreadCount = Notification::where(function ($q) use ($user) {
                            $q->where('user_id', $user->id)
                              ->orWhere('target_role', 'all')
                              ->orWhere('target_role', $user->role);
                        })->where('is_read', false)->count();
                    }

                    $view->with('currentUser', $user);
                    $view->with('unreadNotificationsCount', $memoizedUnreadCount);
                    $view->with('unreadNotifsCount', $memoizedUnreadCount);
                    $view->with('webUnreadCount', $memoizedUnreadCount);
                    $view->with('topNavNotifs', $memoizedTopNotifs ?? collect());
                    $view->with('webNotifs', $memoizedTopNotifs ?? collect());
                }
            } catch (\Throwable $e) {
                // Fail gracefully during migrations or bootstrap
            }
        });
    }
}
