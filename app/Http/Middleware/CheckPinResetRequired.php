<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPinResetRequired
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('staff')->user() ?? Auth::guard('web')->user();

        if ($user && $user->pin_reset_required) {
            $isStaff = $user->isStaff();
            $targetRoute = $isStaff ? 'backoffice.pin.setup' : 'public.pin.setup';
            $currentRoute = $request->route() ? $request->route()->getName() : '';

            // Allow reaching the setup page, submitting the new PIN, or logging out
            $allowedRoutes = [
                'public.pin.setup',
                'public.pin.setup.post',
                'backoffice.pin.setup',
                'backoffice.pin.setup.post',
                'public.logout',
                'backoffice.logout',
            ];

            if (!in_array($currentRoute, $allowedRoutes)) {
                return redirect()->route($targetRoute)->with('warning', 'Please choose your personal 4-digit PIN before proceeding.');
            }
        }

        return $next($request);
    }
}
