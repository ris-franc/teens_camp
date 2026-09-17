<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PinResetController extends Controller
{
    /**
     * Admin resets any user's PIN back to 0000.
     */
    public function adminResetUserPin(Request $request, User $user)
    {
        $admin = Auth::guard('staff')->user();
        if (!$admin || !$admin->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $user->pin = Hash::make('0000');
        $user->pin_reset_required = true;
        $user->save();

        $targetLink = $user->isStaff() 
            ? route('backoffice.profile') 
            : ($user->isTeen() ? route('teen.dashboard') : route('parent.dashboard'));

        Notification::notifyUser(
            $user->id,
            "Security Notice: PIN Reset",
            "Your 4-digit PIN was reset to default (0000) by Camp Administrator {$admin->name}. You will be required to set a new PIN on next login.",
            'security',
            $targetLink,
            'bi-shield-lock-fill text-warning'
        );

        return back()->with('success', "PIN for {$user->name} has been reset to 0000. They will be required to set a new PIN on next login.");
    }

    /**
     * Parent resets their own linked teen's PIN back to 0000.
     */
    public function parentResetTeenPin(Request $request, User $teen)
    {
        $parent = Auth::guard('web')->user();
        if (!$parent || !$parent->isParent()) {
            abort(403, 'Unauthorized.');
        }

        // Validate that this teen belongs to this parent
        if (!$parent->teens()->where('users.id', $teen->id)->exists()) {
            abort(403, 'You are only authorized to reset PINs for your own declared teens.');
        }

        $teen->pin = Hash::make('0000');
        $teen->pin_reset_required = true;
        $teen->save();

        Notification::notifyUser(
            $teen->id,
            "Security Notice: PIN Reset",
            "Your 4-digit PIN was reset to 0000 by your parent ({$parent->name}). You will be required to choose a new PIN upon login.",
            'security',
            route('teen.dashboard'),
            'bi-shield-lock-fill text-warning'
        );

        return back()->with('success', "PIN for {$teen->name} has been reset to 0000. They will be prompted to choose a new PIN on next login.");
    }
}
