<?php

namespace App\Http\Controllers;

use App\Models\CampSeason;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();

        $notifications = Notification::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('target_role', 'all')
              ->orWhere('target_role', $user->role);
        })->latest('id')->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
            'user' => $user,
        ]);
    }

    public function unreadJson()
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();
        if (!$user) {
            return response()->json(['unreadCount' => 0, 'notifications' => []]);
        }

        $query = Notification::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhere('target_role', 'all')
              ->orWhere('target_role', $user->role);
        })->latest('id');

        $unreadCount = (clone $query)->where('is_read', false)->count();
        $notifications = $query->take(8)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->title,
                'message' => $n->message,
                'type' => $n->type,
                'icon' => $n->icon_class,
                'link' => $n->link,
                'is_read' => (bool)$n->is_read,
                'time_ago' => $n->created_at ? $n->created_at->diffForHumans() : 'Just now',
            ];
        });

        return response()->json([
            'unreadCount' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    public function markAsRead(Notification $notification)
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();

        // Mark as read
        $notification->update(['is_read' => true]);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead()
    {
        $user = Auth::guard('web')->user() ?? Auth::guard('staff')->user();

        if ($user) {
            Notification::where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('target_role', 'all')
                  ->orWhere('target_role', $user->role);
            })->update(['is_read' => true]);
        }

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    public function broadcast(Request $request)
    {
        $user = Auth::guard('staff')->user();
        if (!$user || (!$user->isAdmin() && !$user->isPastor())) {
            abort(403, 'Unauthorized.');
        }

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
            'created_by' => $user->id,
        ]);

        return back()->with('success', "Broadcast announcement sent to {$request->target_role}!");
    }
}
