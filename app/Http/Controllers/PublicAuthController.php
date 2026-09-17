<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PublicAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return redirect()->route($user->isTeen() ? 'teen.dashboard' : 'parent.dashboard');
        }

        return view('auth.public-login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'pin' => ['required', 'digits:4'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withInput(['email'])->withErrors(['email' => 'Account not found with this email.']);
        }

        if (!in_array($user->role, ['teen', 'parent'])) {
            return back()->withInput(['email'])->withErrors(['email' => 'This portal is for Teens and Parents only. Staff must access the Back-Office.']);
        }

        if ($user->isSuspended()) {
            return back()->withInput(['email'])->withErrors(['email' => 'This account has been suspended. Please contact church administration.']);
        }

        if (!Hash::check($request->pin, $user->pin)) {
            return back()->withInput(['email'])->withErrors(['pin' => 'Incorrect 4-digit PIN.']);
        }

        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        if ($user->pin_reset_required) {
            return redirect()->route('public.pin.setup')->with('warning', 'Please set your new 4-digit PIN.');
        }

        return redirect()->intended(route($user->isTeen() ? 'teen.dashboard' : 'parent.dashboard'));
    }

    public function showPinSetup()
    {
        $user = Auth::guard('web')->user();
        if (!$user) {
            return redirect()->route('login');
        }

        return view('auth.pin-setup', [
            'user' => $user,
            'isStaff' => false,
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

        $user = Auth::guard('web')->user();
        $user->pin = Hash::make($request->pin);
        $user->pin_reset_required = false;
        $user->save();

        return redirect()->route($user->isTeen() ? 'teen.dashboard' : 'parent.dashboard')
            ->with('success', 'Your 4-digit PIN has been set successfully!');
    }

    public function showProfile()
    {
        $user = Auth::guard('web')->user();
        return view('profile.show', [
            'user' => $user,
            'isStaff' => false,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::guard('web')->user();

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
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public');
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

        return back()->with('success', 'Profile updated successfully!');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')->with('info', 'You have been logged out.');
    }
}
