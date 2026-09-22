<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class BackofficeAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('staff')->check()) {
            return redirect($this->redirectToStaffDashboard(Auth::guard('staff')->user()));
        }

        return view('auth.backoffice-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'pin' => ['required', 'digits:4'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput(['email'])->withErrors(['email' => 'Staff account not found.']);
        }

        if (!$user->isStaff()) {
            return back()->withInput(['email'])->withErrors(['email' => 'Unauthorized. This portal is strictly for Camp Staff.']);
        }

        if ($user->isSuspended()) {
            return back()->withInput(['email'])->withErrors(['email' => 'This staff account has been suspended. Please contact system administration.']);
        }

        if (!Hash::check($request->pin, $user->pin)) {
            return back()->withInput(['email'])->withErrors(['pin' => 'Incorrect 4-digit PIN.']);
        }

        Auth::guard('staff')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->pin_reset_required) {
            return redirect()->route('backoffice.pin.setup')->with('warning', 'Please set your new staff 4-digit PIN.');
        }

        return redirect()->intended($this->redirectToStaffDashboard($user));
    }

    public function showPinSetup()
    {
        $user = Auth::guard('staff')->user();
        if (!$user) {
            return redirect()->route('backoffice.login');
        }

        return view('auth.pin-setup', [
            'user' => $user,
            'isStaff' => true,
        ]);
    }

    public function savePinSetup(Request $request)
    {
        $request->validate([
            'pin' => ['required', 'digits:4', 'confirmed'],
        ]);

        if ($request->pin === '0000') {
            return back()->withErrors(['pin' => '0000 is a default reset PIN. Please choose a different 4-digit PIN.']);
        }

        $user = Auth::guard('staff')->user();
        $user->pin = Hash::make($request->pin);
        $user->pin_reset_required = false;
        $user->save();

        return redirect($this->redirectToStaffDashboard($user))
            ->with('success', 'Your staff PIN has been set successfully!');
    }

    public function showProfile()
    {
        $user = Auth::guard('staff')->user();
        return view('profile.show', [
            'user' => $user,
            'isStaff' => true,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('staff')->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'current_pin' => ['nullable', 'digits:4'],
            'new_pin' => ['nullable', 'digits:4', 'confirmed'],
        ]);

        $user->name = $request->name;
        $user->phone = $request->phone;

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                \App\Services\SupabaseStorageService::delete($user->avatar);
            }
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'avatar_user_' . $user->id . '_' . time() . '.' . $extension;
            $path = \App\Services\SupabaseStorageService::upload($file, 'avatars/' . $filename);
            $user->avatar = $path;
        }

        if ($request->filled('new_pin')) {
            if (!$request->filled('current_pin') || !Hash::check($request->current_pin, $user->pin)) {
                return back()->withErrors(['current_pin' => 'Current 4-digit PIN is incorrect.']);
            }
            if ($request->new_pin === '0000') {
                return back()->withErrors(['new_pin' => '0000 is reserved as the system reset PIN. Please choose a different PIN.']);
            }
            $user->pin = Hash::make($request->new_pin);
        }

        $user->save();

        return back()->with('success', 'Staff profile updated successfully!');
    }

    public function logout(Request $request)
    {
        Auth::guard('staff')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('backoffice.login')->with('info', 'You have been logged out of the Back-Office.');
    }

    protected function redirectToStaffDashboard(User $user): string
    {
        if ($user->isAdmin()) {
            return route('backoffice.admin.dashboard');
        }
        if ($user->isPastor()) {
            return route('backoffice.pastor.dashboard');
        }
        if ($user->isRegistration()) {
            return route('backoffice.registration.dashboard');
        }
        if ($user->isCampaign() || $user->isCampaignHead()) {
            return route('backoffice.campaign.dashboard');
        }

        return route('backoffice.admin.dashboard');
    }
}
