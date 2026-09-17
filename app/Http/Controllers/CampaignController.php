<?php

namespace App\Http\Controllers;

use App\Models\AdoptATeenKitty;
use App\Models\CampaignProduct;
use App\Models\CampaignSale;
use App\Models\CampaignWeeklyBatch;
use App\Models\CampSeason;
use App\Models\Notification;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CampaignController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('staff')->user();
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        if (!$season) {
            return view('backoffice.campaign.dashboard', [
                'season' => null,
                'products' => collect(),
                'recentSales' => collect(),
                'activeBatch' => null,
                'batches' => collect(),
                'myProfit' => 0,
                'totalProfit' => 0,
                'isHead' => $user->isCampaignHead() || $user->isAdmin(),
            ]);
        }

        $products = CampaignProduct::where('camp_season_id', $season->id)
            ->where('is_active', true)
            ->get();

        $activeBatch = CampaignWeeklyBatch::where('camp_season_id', $season->id)
            ->where('status', 'pending_review')
            ->latest('id')
            ->first();

        // If no active batch exists, prepare a new pending batch
        if (!$activeBatch) {
            $weekStart = now()->startOfWeek();
            $weekEnd = now()->endOfWeek();
            $activeBatch = CampaignWeeklyBatch::create([
                'camp_season_id' => $season->id,
                'week_label' => $season->year . ' - Week ' . now()->weekOfYear,
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'total_sales_amount' => 0,
                'total_profit_amount' => 0,
                'status' => 'pending_review',
            ]);
        }

        $isHead = $user->isCampaignHead() || $user->isAdmin();

        $salesQuery = CampaignSale::where('camp_season_id', $season->id)
            ->with(['seller', 'product']);

        if (!$isHead) {
            $salesQuery->where('seller_id', $user->id);
        }

        $recentSales = $salesQuery->latest('id')->take(20)->get();

        $myProfit = CampaignSale::where('camp_season_id', $season->id)
            ->where('seller_id', $user->id)
            ->sum('total_profit');

        $totalProfit = CampaignSale::where('camp_season_id', $season->id)
            ->sum('total_profit');

        $batches = CampaignWeeklyBatch::where('camp_season_id', $season->id)
            ->with('approver')
            ->latest('id')
            ->get();

        return view('backoffice.campaign.dashboard', [
            'season' => $season,
            'products' => $products,
            'recentSales' => $recentSales,
            'activeBatch' => $activeBatch,
            'batches' => $batches,
            'myProfit' => $myProfit,
            'totalProfit' => $totalProfit,
            'isHead' => $isHead,
        ]);
    }

    /**
     * Record a new sale.
     */
    public function recordSale(Request $request)
    {
        $seller = Auth::guard('staff')->user();
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        $request->validate([
            'product_id' => ['required', 'exists:campaign_products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'batch_id' => ['required', 'exists:campaign_weekly_batches,id'],
        ]);

        $product = CampaignProduct::findOrFail($request->product_id);
        $batch = CampaignWeeklyBatch::findOrFail($request->batch_id);

        $totalAmount = $product->unit_price * $request->quantity;
        $unitProfit = $product->profit_per_unit;
        $totalProfit = $unitProfit * $request->quantity;

        DB::transaction(function () use ($season, $batch, $seller, $product, $request, $totalAmount, $unitProfit, $totalProfit) {
            CampaignSale::create([
                'camp_season_id' => $season->id,
                'batch_id' => $batch->id,
                'seller_id' => $seller->id,
                'product_id' => $product->id,
                'quantity' => $request->quantity,
                'unit_price' => $product->unit_price,
                'total_amount' => $totalAmount,
                'unit_profit' => $unitProfit,
                'total_profit' => $totalProfit,
                'sold_at' => now(),
            ]);

            // Update batch total
            $batch->increment('total_sales_amount', $totalAmount);
            $batch->increment('total_profit_amount', $totalProfit);

            // 1. Notify Seller
            Notification::notifyUser(
                $seller->id,
                "Campaign Sale Recorded",
                "Successfully logged {$request->quantity}x {$product->name} (KES " . number_format($totalAmount, 2) . ") in Batch #{$batch->id}.",
                'campaign_sale',
                route('backoffice.campaign.dashboard'),
                'bi-bag-check-fill text-warning'
            );

            // 2. Notify Staff / Admins
            Notification::notifyStaff(
                "New Merchandise Sale",
                "{$seller->name} sold {$request->quantity}x {$product->name} for KES " . number_format($totalAmount, 2) . " (Profit: KES " . number_format($totalProfit, 2) . ")",
                'campaign_sale',
                route('backoffice.campaign.dashboard'),
                'bi-bag-check-fill text-warning'
            );
        });

        return back()->with('success', "Sale recorded: {$request->quantity}x {$product->name} (KES " . number_format($totalAmount, 2) . ", Profit: KES " . number_format($totalProfit, 2) . ").");
    }

    /**
     * Product Catalogue Management (Campaign Head / Admin).
     */
    public function addProduct(Request $request)
    {
        $user = Auth::guard('staff')->user();
        if (!$user->isCampaignHead() && !$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['required', 'numeric', 'gt:unit_cost'],
        ]);

        $profitPerUnit = $request->unit_price - $request->unit_cost;

        CampaignProduct::create([
            'camp_season_id' => $season->id,
            'name' => $request->name,
            'unit_cost' => $request->unit_cost,
            'unit_price' => $request->unit_price,
            'profit_per_unit' => $profitPerUnit,
            'is_active' => true,
        ]);

        return back()->with('success', "Product '{$request->name}' added to campaign catalogue!");
    }

    /**
     * Remove product from catalogue with account PIN confirmation (Admin & Campaign Head).
     */
    public function deleteProduct(Request $request, CampaignProduct $product)
    {
        $user = Auth::guard('staff')->user();
        if (!$user->isCampaignHead() && !$user->isAdmin()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'pin' => ['required', 'string', 'size:4'],
        ]);

        if (!Hash::check($request->pin, $user->pin)) {
            return back()->with('error', 'Authentication failed: Incorrect 4-digit Account PIN.');
        }

        $productName = $product->name;

        DB::transaction(function () use ($product) {
            $product->sales()->delete();
            $product->delete();
        });

        Notification::notifyStaff(
            "Catalogue Item Removed",
            "Product '{$productName}' was removed from the catalogue by {$user->name}.",
            'system',
            route('backoffice.campaign.dashboard'),
            'bi-trash3-fill text-danger'
        );

        return back()->with('success', "Product '{$productName}' has been removed from the catalogue.");
    }

    /**
     * Admin/Head approves weekly batch and moves profit to Kitty ledger.
     */
    public function approveBatch(Request $request, CampaignWeeklyBatch $batch)
    {
        $approver = Auth::guard('staff')->user();
        if (!$approver->isAdmin() && !$approver->isCampaignHead()) {
            abort(403, 'Unauthorized.');
        }

        $season = $batch->campSeason;

        DB::transaction(function () use ($batch, $season, $approver, $request) {
            $batch->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'reviewed_at' => now(),
                'notes' => $request->input('notes', 'Approved weekly batch'),
            ]);

            $currentBalance = AdoptATeenKitty::getCurrentBalance($season->id);
            $newBalance = $currentBalance + $batch->total_profit_amount;
            $receiptNumber = Receipt::generateReceiptNumber('CPN');

            $receipt = Receipt::create([
                'camp_season_id' => $season->id,
                'receipt_number' => $receiptNumber,
                'type' => 'kitty_campaign_in',
                'user_id' => null,
                'amount' => $batch->total_profit_amount,
                'description' => "Campaign profit transferred from batch: {$batch->week_label}",
                'meta_data' => [
                    'batch_id' => $batch->id,
                    'total_sales' => $batch->total_sales_amount,
                    'approved_by' => $approver->name,
                ],
            ]);

            AdoptATeenKitty::create([
                'camp_season_id' => $season->id,
                'type' => 'campaign_profit_in',
                'amount' => $batch->total_profit_amount,
                'balance_after' => $newBalance,
                'description' => "Campaign batch profit: {$batch->week_label}",
                'reference' => "BATCH-{$batch->id}",
                'receipt_id' => $receipt->id,
                'created_by' => $approver->id,
            ]);

            // Notify staff & campaign team
            Notification::notifyStaff(
                "Campaign Batch Approved",
                "Batch #{$batch->id} ({$batch->week_label}) approved by {$approver->name}. KES " . number_format($batch->total_profit_amount, 2) . " profit transferred to Adopt-a-Teen Kitty.",
                'batch_approved',
                route('backoffice.admin.dashboard'),
                'bi-shield-check text-success'
            );
        });

        return back()->with('success', "Weekly Batch #{$batch->id} approved! KES " . number_format($batch->total_profit_amount, 2) . " campaign profit successfully transferred to Adopt-a-Teen Kitty.");
    }

    /**
     * Return weekly batch for correction.
     */
    public function returnBatch(Request $request, CampaignWeeklyBatch $batch)
    {
        $approver = Auth::guard('staff')->user();
        if (!$approver->isAdmin() && !$approver->isCampaignHead()) {
            abort(403, 'Unauthorized.');
        }

        $batch->update([
            'status' => 'returned',
            'approved_by' => $approver->id,
            'reviewed_at' => now(),
            'notes' => $request->input('notes', 'Returned for correction'),
        ]);

        return back()->with('info', "Batch #{$batch->id} returned for correction.");
    }
}
