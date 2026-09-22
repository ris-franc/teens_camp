<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\BackofficeAuthController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackingListController;
use App\Http\Controllers\ParentDashboardController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\PinResetController;
use App\Http\Controllers\PublicAuthController;
use App\Http\Controllers\PublicRegistrationController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\TeenDashboardController;
use App\Models\CampSeason;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Teen Camp 2026 Management System
|--------------------------------------------------------------------------
*/

// 1. Public Landing Page, 2-Minute Intake & Receipts
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/register-camper', [PublicRegistrationController::class, 'showForm'])->name('public.register');
Route::post('/register-camper', [PublicRegistrationController::class, 'process'])->name('public.register.post');
Route::get('/receipts/{receiptNumber}', [PaymentReceiptController::class, 'show'])->name('receipts.show');

// M-Pesa Daraja STK Push & Callbacks
Route::get('/mpesa/paybill-info', [\App\Http\Controllers\MpesaController::class, 'getPaybillInfo'])->name('mpesa.paybill-info');
Route::post('/mpesa/stk-push', [\App\Http\Controllers\MpesaController::class, 'initiateStk'])->name('mpesa.stk-push');
Route::post('/api/mpesa/callback', [\App\Http\Controllers\MpesaController::class, 'darajaCallback'])->name('api.mpesa.callback');
Route::post('/mpesa/callback', [\App\Http\Controllers\MpesaController::class, 'darajaCallback']);

// 2. Public Authentication (Teens & Parents - Guard: web)
Route::get('/login', [PublicAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [PublicAuthController::class, 'login'])->name('public.login.post');
Route::match(['get', 'post'], '/logout', [PublicAuthController::class, 'logout'])->name('public.logout');

Route::get('/pin', function () {
    if (\Illuminate\Support\Facades\Auth::guard('staff')->check()) {
        return redirect()->route('backoffice.profile');
    }
    if (\Illuminate\Support\Facades\Auth::guard('web')->check()) {
        return redirect()->route('public.profile');
    }
    return redirect()->route('login');
})->name('pin');

Route::middleware('auth:web')->group(function () {
    Route::get('/set-pin', [PublicAuthController::class, 'showPinSetup'])->name('public.pin.setup');
    Route::post('/set-pin', [PublicAuthController::class, 'savePinSetup'])->name('public.pin.setup.post');

    Route::middleware('pin.check')->group(function () {
        Route::get('/profile', [PublicAuthController::class, 'showProfile'])->name('public.profile');
        Route::get('/user/profile', [PublicAuthController::class, 'showProfile'])->name('profile');
        Route::post('/profile', [PublicAuthController::class, 'updateProfile'])->name('public.profile.update');

        // Printable Packing List PDF
        Route::get('/packing-list/pdf', [PackingListController::class, 'downloadPdf'])->name('packing-list.pdf');

        // Teen Portal
        Route::middleware('role:teen')->prefix('teen')->name('teen.')->group(function () {
            Route::get('/dashboard', [TeenDashboardController::class, 'index'])->name('dashboard');
            Route::get('/packing-list/pdf', [PackingListController::class, 'downloadPdf'])->name('packing.pdf');
            Route::get('/forms/{form}', [TeenDashboardController::class, 'showForm'])->name('forms.show');
            Route::post('/forms/{form}/submit', [TeenDashboardController::class, 'submitForm'])->name('forms.submit');
            Route::get('/forms/submissions/{submission}', [TeenDashboardController::class, 'showSubmission'])->name('forms.submission.show');
        });

        // Parent Portal
        Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
            Route::get('/dashboard', [ParentDashboardController::class, 'index'])->name('dashboard');
            Route::get('/packing-list/pdf', [PackingListController::class, 'downloadPdf'])->name('packing.pdf');
            Route::get('/forms/{form}', [ParentDashboardController::class, 'showForm'])->name('forms.show');
            Route::post('/forms/{form}/submit', [ParentDashboardController::class, 'submitForm'])->name('forms.submit');
            Route::get('/forms/submissions/{submission}', [ParentDashboardController::class, 'showSubmission'])->name('forms.submission.show');
            Route::post('/declarations/{registration}', [ParentDashboardController::class, 'updateDeclaration'])->name('declarations.update');
            Route::post('/withdraw/{registration}', [ParentDashboardController::class, 'withdrawTeen'])->name('withdraw');
            Route::post('/forms/approve/{submission}', [ParentDashboardController::class, 'approveFormSubmission'])->name('forms.approve');
            Route::post('/forms/return/{submission}', [ParentDashboardController::class, 'returnFormSubmission'])->name('forms.return');
            Route::post('/adopt-a-teen', [ParentDashboardController::class, 'applyAdoptATeen'])->name('adopt.apply');
            Route::post('/pay', [ParentDashboardController::class, 'makePayment'])->name('pay');
            Route::post('/register-teen', [ParentDashboardController::class, 'registerTeen'])->name('register-teen');
            Route::post('/reset-teen-pin/{teen}', [PinResetController::class, 'parentResetTeenPin'])->name('teen.reset-pin');
        });

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread', [NotificationController::class, 'unreadJson'])->name('notifications.unread');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    });
});

// 3. Private Back-Office (Staff - Guard: staff)
Route::prefix('backoffice')->name('backoffice.')->group(function () {
    Route::get('/login', [BackofficeAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [BackofficeAuthController::class, 'login'])->name('login.post');
    Route::match(['get', 'post'], '/logout', [BackofficeAuthController::class, 'logout'])->name('logout');
    Route::get('/pin', function () {
        return redirect()->route('backoffice.profile');
    })->name('pin');

    Route::middleware('auth:staff')->group(function () {
        Route::get('/set-pin', [BackofficeAuthController::class, 'showPinSetup'])->name('pin.setup');
        Route::post('/set-pin', [BackofficeAuthController::class, 'savePinSetup'])->name('pin.setup.post');

        Route::middleware('pin.check')->group(function () {
            Route::get('/profile', [BackofficeAuthController::class, 'showProfile'])->name('profile');
            Route::post('/profile', [BackofficeAuthController::class, 'updateProfile'])->name('profile.update');

            // Notifications for Backoffice
            Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
            Route::get('/notifications/unread', [NotificationController::class, 'unreadJson'])->name('notifications.unread');
            Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
            Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

            // Season Switcher for backoffice view
            Route::get('/switch-season/{season}', function (CampSeason $season) {
                session(['admin_selected_season_id' => $season->id]);
                return back()->with('info', "Viewing {$season->name} ({$season->status})");
            })->name('switch-season');

            // Downloadable PDF Reports
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/registrations/pdf', [\App\Http\Controllers\ReportController::class, 'registrationsPdf'])->name('registrations.pdf');
                Route::get('/sales/pdf', [\App\Http\Controllers\ReportController::class, 'salesPdf'])->name('sales.pdf');
                Route::get('/adopt/pdf', [\App\Http\Controllers\ReportController::class, 'adoptPdf'])->name('adopt.pdf');
                Route::get('/payments/pdf', [\App\Http\Controllers\ReportController::class, 'paymentsPdf'])->name('payments.pdf');
                Route::get('/database/pdf', [\App\Http\Controllers\ReportController::class, 'databasePdf'])->name('database.pdf');
            });

            // Admin Portal
            Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
                Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
                Route::get('/database', [AdminDashboardController::class, 'searchDatabase'])->name('database');
                Route::post('/kitty/donation', [AdminDashboardController::class, 'addKittyDonation'])->name('kitty.donation');
                Route::post('/adopt-requests/{adoptRequest}/review', [AdminDashboardController::class, 'reviewAdoptRequest'])->name('adopt.review');
                
                // Season Management
                Route::get('/seasons', [AdminDashboardController::class, 'seasons'])->name('seasons');
                Route::post('/seasons', [AdminDashboardController::class, 'createSeason'])->name('seasons.create');
                Route::post('/seasons/{season}', [AdminDashboardController::class, 'updateSeason'])->name('seasons.update');
                Route::post('/seasons/{season}/toggle-registration', [AdminDashboardController::class, 'toggleRegistration'])->name('seasons.toggle-registration');

                // User & Access
                Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
                Route::post('/users', [AdminDashboardController::class, 'createUser'])->name('users.create');
                Route::put('/users/{user}', [AdminDashboardController::class, 'updateUser'])->name('users.update');
                Route::delete('/users/{user}', [AdminDashboardController::class, 'deleteUser'])->name('users.delete');
                Route::post('/users/{user}/delete', [AdminDashboardController::class, 'deleteUser'])->name('users.delete.post');
                Route::post('/users/{user}/reset-pin', [PinResetController::class, 'adminResetUserPin'])->name('users.reset-pin');
                Route::post('/users/{user}/toggle-suspension', [AdminDashboardController::class, 'toggleUserSuspension'])->name('users.toggle-suspension');
                Route::post('/teens/{user}/family', [AdminDashboardController::class, 'updateTeenFamily'])->name('teens.update-family');


                // Database Wipe / Reset
                Route::post('/reset-database', [AdminDashboardController::class, 'resetDatabase'])->name('reset-database');

                // M-Pesa Daraja Settings & Destination Accounts
                Route::post('/mpesa-settings', [AdminDashboardController::class, 'updateMpesaSettings'])->name('mpesa.settings');

                // Brevo Email Test Route
                Route::post('/test-brevo-email', [AdminDashboardController::class, 'testBrevoEmail'])->name('test-brevo-email');
            });

            // Pastor Portal
            Route::middleware('role:pastor,admin')->prefix('pastor')->name('pastor.')->group(function () {
                Route::get('/', [\App\Http\Controllers\PastorDashboardController::class, 'index'])->name('dashboard');
                Route::post('/adopt-requests/{adoptRequest}/review', [AdminDashboardController::class, 'reviewAdoptRequest'])->name('adopt.review');
                Route::post('/broadcast', [\App\Http\Controllers\PastorDashboardController::class, 'broadcastNotification'])->name('broadcast');
            });

            // Adopt-a-Teen Portal (Admin & Pastor)
            Route::middleware('role:pastor,admin')->prefix('adopt-a-teen')->name('adopt.')->group(function () {
                Route::get('/', [\App\Http\Controllers\BackofficeAdoptATeenController::class, 'index'])->name('index');
                Route::post('/requests/{adoptRequest}/review', [\App\Http\Controllers\BackofficeAdoptATeenController::class, 'review'])->name('review');
                Route::post('/donation', [\App\Http\Controllers\BackofficeAdoptATeenController::class, 'addDonation'])->name('donation');
                Route::post('/direct-sponsor', [\App\Http\Controllers\BackofficeAdoptATeenController::class, 'directSponsor'])->name('direct-sponsor');
            });

            // Registration Desk Portal
            Route::middleware('role:registration,admin')->prefix('registration')->name('registration.')->group(function () {
                Route::get('/', [RegistrationController::class, 'dashboard'])->name('dashboard');
                Route::get('/desk', [RegistrationController::class, 'deskForm'])->name('desk');
                Route::post('/desk', [RegistrationController::class, 'processDeskRegistration'])->name('desk.process');
                Route::post('/desk/form-config', [RegistrationController::class, 'saveFormConfig'])->name('desk.form-config.save');
                Route::get('/{registration}/edit', [RegistrationController::class, 'edit'])->name('edit');
                Route::put('/{registration}', [RegistrationController::class, 'update'])->name('update');
                Route::get('/sign-in', [RegistrationController::class, 'showSignIn'])->name('signin');
                Route::post('/sign-in/{registration}', [RegistrationController::class, 'checkIn'])->name('signin.checkin');
                Route::post('/sign-in/{registration}/undo', [RegistrationController::class, 'undoCheckIn'])->name('signin.undo');
            });

            // Dynamic Forms (Registration & Admin)
            Route::middleware('role:registration,admin')->prefix('forms')->name('forms.')->group(function () {
                Route::get('/', [FormBuilderController::class, 'index'])->name('index');
                Route::get('/create', [FormBuilderController::class, 'create'])->name('create');
                Route::post('/', [FormBuilderController::class, 'store'])->name('store');
                Route::get('/{form}', [FormBuilderController::class, 'show'])->name('show');
                Route::post('/submissions/{submission}/return', [FormBuilderController::class, 'returnSubmission'])->name('submissions.return');
            });

            // Packing Lists (Registration & Admin)
            Route::middleware('role:registration,admin')->prefix('packing')->name('packing.')->group(function () {
                Route::get('/', [PackingListController::class, 'index'])->name('index');
                Route::get('/pdf', [PackingListController::class, 'downloadPdf'])->name('pdf');
                Route::post('/', [PackingListController::class, 'store'])->name('store');
                Route::match(['put', 'post'], '/{item}/update', [PackingListController::class, 'update'])->name('update');
                Route::match(['delete', 'post'], '/{item}/delete', [PackingListController::class, 'destroy'])->name('destroy');
                Route::post('/toggle-release', [PackingListController::class, 'toggleRelease'])->name('toggle-release');
                Route::post('/broadcast-notification', [PackingListController::class, 'broadcastNotification'])->name('broadcast');
            });

            // Campaign Portal (Campaign Team, Campaign Head, Admin)
            Route::middleware('role:campaign,campaign_head,admin')->prefix('campaign')->name('campaign.')->group(function () {
                Route::get('/', [CampaignController::class, 'index'])->name('dashboard');
                Route::post('/sale', [CampaignController::class, 'recordSale'])->name('sale.record');
                Route::post('/products', [CampaignController::class, 'addProduct'])->name('products.add');
                Route::delete('/products/{product}', [CampaignController::class, 'deleteProduct'])->name('products.delete');
                Route::post('/products/{product}/delete', [CampaignController::class, 'deleteProduct'])->name('products.delete.post');
                Route::post('/batches/{batch}/approve', [CampaignController::class, 'approveBatch'])->name('batches.approve');
                Route::post('/batches/{batch}/return', [CampaignController::class, 'returnBatch'])->name('batches.return');
            });

            // Notifications broadcast (Admin & Pastor)
            Route::post('/notifications/broadcast', [NotificationController::class, 'broadcast'])->name('notifications.broadcast');
        });
    });
});

// 4. Storage Media Fallback Route (Serves local public storage or redirects seamlessly to Supabase Storage)
Route::get('/storage/{path}', function (string $path) {
    $cleanPath = ltrim($path, '/');
    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($cleanPath)) {
        return \Illuminate\Support\Facades\Storage::disk('public')->response($cleanPath);
    }
    $supabaseUrl = rtrim(config('services.supabase.url', 'https://mhrcuhiocqkpfljyddyo.supabase.co'), '/');
    $bucket = config('services.supabase.bucket', 'camp-media');
    return redirect("{$supabaseUrl}/storage/v1/object/public/{$bucket}/{$cleanPath}");
})->where('path', '.*')->name('storage.fallback');

