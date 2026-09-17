<?php

namespace App\Http\Controllers;

use App\Models\CampSeason;
use App\Models\Notification;
use App\Models\PackingList;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PackingListController extends Controller
{
    /**
     * Display the backoffice packing list manager.
     */
    public function index()
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();
        $items = collect();

        if ($season) {
            $items = PackingList::where('camp_season_id', $season->id)->orderBy('category')->get()->groupBy('category');
        }

        return view('backoffice.packing.index', [
            'season' => $season,
            'items' => $items,
        ]);
    }

    /**
     * Add a new item to the packing checklist.
     */
    public function store(Request $request)
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        if (!$season) {
            return back()->withErrors(['error' => 'No active season found. Please create or select a camp season first.']);
        }

        $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'item_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_essential' => ['nullable', 'boolean'],
        ]);

        // If other items in this season are already released, match that status
        $isReleased = PackingList::where('camp_season_id', $season->id)->where('is_released', true)->exists();

        PackingList::create([
            'camp_season_id' => $season->id,
            'category' => trim($request->category),
            'item_name' => trim($request->item_name),
            'notes' => trim($request->notes),
            'is_essential' => $request->boolean('is_essential'),
            'is_released' => $isReleased,
        ]);

        return back()->with('success', "Item '{$request->item_name}' added to packing list.");
    }

    /**
     * Update an existing packing item.
     */
    public function update(Request $request, PackingList $item)
    {
        $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'item_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_essential' => ['nullable', 'boolean'],
        ]);

        $item->update([
            'category' => trim($request->category),
            'item_name' => trim($request->item_name),
            'notes' => trim($request->notes),
            'is_essential' => $request->boolean('is_essential'),
        ]);

        return back()->with('success', "Item '{$item->item_name}' updated successfully.");
    }

    /**
     * Remove an item from the packing list.
     */
    public function destroy(PackingList $item)
    {
        $itemName = $item->item_name;
        $item->delete();

        return back()->with('success', "Item '{$itemName}' removed from packing checklist.");
    }

    /**
     * Toggle public release status of the packing checklist.
     */
    public function toggleRelease(Request $request)
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        if (!$season) {
            return back()->withErrors(['error' => 'No active season.']);
        }

        $allReleased = PackingList::where('camp_season_id', $season->id)->where('is_released', true)->count() > 0;
        $newStatus = !$allReleased;

        PackingList::where('camp_season_id', $season->id)->update(['is_released' => $newStatus]);

        if ($newStatus) {
            $this->notifyUsersOfPackingList($season);
            $msg = "Packing checklist for {$season->name} is now LIVE & PUBLIC! Notification alerts and emails have been dispatched to all parents and teens.";
        } else {
            $msg = "Packing checklist for {$season->name} has been UNRELEASED (hidden from teens and parents).";
        }

        return back()->with('success', $msg);
    }

    /**
     * Broadcast / re-broadcast notification to all registered parents and teens.
     */
    public function broadcastNotification(Request $request)
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        if (!$season) {
            return back()->withErrors(['error' => 'No active season found.']);
        }

        // Ensure packing list is released
        PackingList::where('camp_season_id', $season->id)->update(['is_released' => true]);

        $count = $this->notifyUsersOfPackingList($season);

        return back()->with('success', "Broadcast successful! Packing list notification and email sent to {$count} camper and parent accounts.");
    }

    /**
     * Helper to notify parents and teens of the packing list.
     */
    protected function notifyUsersOfPackingList(CampSeason $season): int
    {
        $notifiedUserIds = [];

        // 1. Gather all parents: those who registered teens (registered_by) + all parent-role users
        // Registration.registered_by holds the user ID who submitted the form (usually the parent account)
        $regByIds = Registration::where('camp_season_id', $season->id)->pluck('registered_by')->filter()->toArray();
        // Also get parent-role users linked via the parent_teen pivot table for this season's teens
        $teenIds = Registration::where('camp_season_id', $season->id)->pluck('teen_id')->filter()->toArray();
        $pivotParentIds = DB::table('parent_teen')->whereIn('teen_id', $teenIds)->pluck('parent_id')->filter()->toArray();
        $allParents = User::where('role', 'parent')->pluck('id')->toArray();
        $parentIds = array_unique(array_merge($regByIds, $pivotParentIds, $allParents));

        foreach ($parentIds as $parentId) {
            if (!in_array($parentId, $notifiedUserIds)) {
                Notification::notifyUser(
                    $parentId,
                    "Official Camp Packing Checklist Released!",
                    "The official packing checklist for {$season->name} is now live! Log in to view required items and download your printable PDF checklist.",
                    'packing',
                    route('parent.dashboard'),
                    'bi-backpack-fill text-danger'
                );
                $notifiedUserIds[] = $parentId;
            }
        }

        // 2. Gather all teens with registrations for this season + general teen users
        // $teenIds was already computed above from registrations; just merge with all teen users
        $allTeens = User::where('role', 'teen')->pluck('id')->toArray();
        $allTeenIds = array_unique(array_merge($teenIds, $allTeens));

        foreach ($allTeenIds as $teenId) {
            if (!in_array($teenId, $notifiedUserIds)) {
                Notification::notifyUser(
                    $teenId,
                    "Official Camp Packing Checklist Live!",
                    "The packing checklist for {$season->name} is out! Check your portal to view required items and download your printable PDF checklist.",
                    'packing',
                    route('teen.dashboard'),
                    'bi-backpack-fill text-danger'
                );
                $notifiedUserIds[] = $teenId;
            }
        }

        return count($notifiedUserIds);
    }

    /**
     * Download / Print PDF version of the Packing Checklist.
     */
    public function downloadPdf(Request $request)
    {
        $season = CampSeason::find($request->season_id) ?? CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        if (!$season) {
            return redirect()->back()->with('error', 'No camp season found.');
        }

        // Check release status for non-staff
        $user = Auth::guard('web')->user();
        $isStaff = Auth::guard('staff')->check();

        $itemsQuery = PackingList::where('camp_season_id', $season->id);
        
        if (!$isStaff) {
            $isReleased = (clone $itemsQuery)->where('is_released', true)->exists();
            if (!$isReleased) {
                return redirect()->back()->with('error', 'The packing checklist has not yet been released by leadership.');
            }
        }

        $categories = $itemsQuery->orderBy('category')->get()->groupBy('category');

        $camperName = null;
        if ($user) {
            if ($user->role === 'teen') {
                $camperName = $user->name;
            } elseif ($user->role === 'parent') {
                $firstTeen = $user->teens()->first();
                $camperName = $firstTeen ? $firstTeen->name : $user->name;
            }
        }

        return view('reports.packing-list', [
            'season' => $season,
            'categories' => $categories,
            'camperName' => $camperName,
        ]);
    }
}
