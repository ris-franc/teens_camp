<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     * Roles can be passed like: role:admin,pastor
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->is('backoffice*')
            ? (Auth::guard('staff')->user() ?? Auth::guard('web')->user())
            : (Auth::guard('web')->user() ?? Auth::guard('staff')->user());

        if (!$user) {
            return redirect()->route($request->is('backoffice*') ? 'backoffice.login' : 'login');
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (in_array($user->role, $roles) || ($user->isAdmin() && in_array('staff', $roles))) {
            return $next($request);
        }

        abort(403, 'Unauthorized access for your role.');
    }
}
