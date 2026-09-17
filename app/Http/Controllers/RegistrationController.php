<?php

namespace App\Http\Controllers;

use App\Models\CampSeason;
use App\Models\DeskFormConfig;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegistrationController extends Controller
{
    public function dashboard(Request $request)
    {
        $staff = Auth::guard('staff')->user();
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        $totalRegistered = 0;
        $totalSignedIn = 0;
        $recentRegistrations = collect();

        if ($season) {
            $totalRegistered = Registration::where('camp_season_id', $season->id)
                ->where('status', '!=', 'withdrawn')
                ->count();
            $totalSignedIn = Registration::where('camp_season_id', $season->id)
                ->where('status', 'signed_in')
                ->count();
            $recentRegistrations = Registration::where('camp_season_id', $season->id)
                ->with(['teen.parents.teens', 'payments'])
                ->latest('id')
                ->take(30)
                ->get();
        }

        return view('backoffice.registration.dashboard', [
            'season' => $season,
            'totalRegistered' => $totalRegistered,
            'totalSignedIn' => $totalSignedIn,
            'recentRegistrations' => $recentRegistrations,
            'allTeens' => \App\Models\User::where('role', 'teen')->orderBy('name')->get(['id', 'name', 'email']),
        ]);

    }

    /**
     * 2-Minute Desk Intake View & Management.
     */
    public function deskForm(Request $request)
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();
        $recentRegistrations = collect();
        $formConfig = DeskFormConfig::getCurrent();

        if ($season) {
            $query = Registration::where('camp_season_id', $season->id)
                ->with(['teen.parents', 'payments.receipt'])
                ->latest('id');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('teen', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                })->orWhereHas('teen.parents', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            $recentRegistrations = $query->take(50)->get();
        }

        return view('backoffice.registration.desk', [
            'season' => $season,
            'recentRegistrations' => $recentRegistrations,
            'search' => $request->search,
            'formConfig' => $formConfig,
            'coreKeys' => DeskFormConfig::$coreKeys,
        ]);
    }

    /**
     * Save Desk Form Configuration (field order, labels, types, required, enabled).
     */
    public function saveFormConfig(Request $request)
    {
        $request->validate([
            'fields' => ['required', 'array'],
            'fields.*.key' => ['required', 'string'],
            'fields.*.label' => ['required', 'string', 'max:120'],
            'fields.*.type' => ['required', 'in:text,email,tel,date,number,textarea,checkbox,select'],
            'fields.*.section' => ['required', 'in:parent,teen,declarations,payment,custom'],
            'fields.*.required' => ['boolean'],
            'fields.*.enabled' => ['boolean'],
            'fields.*.order' => ['integer'],
        ]);

        $config = DeskFormConfig::getCurrent();
        $coreKeys = DeskFormConfig::$coreKeys;

        // Merge incoming with existing to preserve core field constraints
        $existingByKey = collect($config->fields ?? [])->keyBy('key');
        $updatedFields = [];

        foreach ($request->fields as $idx => $field) {
            $key = $field['key'];
            $isCore = in_array($key, $coreKeys);

            $updatedFields[] = [
                'key' => $key,
                'label' => $field['label'],
                'type' => $field['type'],
                'section' => $field['section'],
                'required' => $isCore ? true : (bool) ($field['required'] ?? false),
                'enabled' => $isCore ? true : (bool) ($field['enabled'] ?? false),
                'order' => (int) ($field['order'] ?? $idx + 1),
                'options' => $existingByKey->get($key, [])['options'] ?? ($field['options'] ?? null),
            ];
        }

        // Handle custom field additions (new key not in existing)
        if ($request->filled('new_field_label') && $request->filled('new_field_type')) {
            $newKey = 'custom_' . strtolower(preg_replace('/[^a-z0-9]+/i', '_', $request->new_field_label)) . '_' . time();
            $updatedFields[] = [
                'key' => $newKey,
                'label' => $request->new_field_label,
                'type' => $request->new_field_type,
                'section' => $request->input('new_field_section', 'custom'),
                'required' => (bool) $request->boolean('new_field_required'),
                'enabled' => true,
                'order' => count($updatedFields) + 1,
                'options' => $request->filled('new_field_options')
                    ? array_map('trim', explode(',', $request->new_field_options))
                    : null,
            ];
        }

        $config->update(['fields' => $updatedFields]);

        Notification::notifyStaff(
            'Desk Form Configuration Updated',
            Auth::guard('staff')->user()->name . ' updated the 2-minute desk intake form field configuration.',
            'registration',
            route('backoffice.registration.desk'),
            'bi-sliders text-info'
        );

        return back()->with('success', '✓ Form field configuration saved successfully.');
    }

    /**
     * Process 2-minute Desk Intake:
     * - Parent lookup or creation
     * - Teen creation
     * - Link parent-teen
     * - Create registration
     * - Optional deposit payment
     */
    public function processDeskRegistration(Request $request)
    {
        $staff = Auth::guard('staff')->user();
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        if (!$season) {
            return back()->withErrors(['error' => 'No active camp season found.']);
        }

        // Build validation rules from active form config
        $formConfig = DeskFormConfig::getCurrent();
        $rules = [
            // Core fields always required
            'parent_name'  => ['required', 'string', 'max:255'],
            'parent_email' => ['required', 'email'],
            'parent_phone' => ['required', 'string', 'max:50'],
            'teen_name'    => ['required', 'string', 'max:255'],
            'teen_email'   => ['required', 'email'],
            'teen_gender'  => ['required', 'in:male,female'],
        ];

        // Add dynamic rules from config
        $typeRules = [
            'email'    => ['nullable', 'email'],
            'date'     => ['nullable', 'date'],
            'number'   => ['nullable', 'numeric', 'min:0'],
            'checkbox' => ['nullable', 'boolean'],
            'tel'      => ['nullable', 'string', 'max:50'],
            'text'     => ['nullable', 'string'],
            'textarea' => ['nullable', 'string'],
            'select'   => ['nullable', 'string'],
        ];

        foreach ($formConfig->enabledFields() as $field) {
            $key = $field['key'];
            if (in_array($key, ['parent_name', 'parent_email', 'parent_phone', 'teen_name', 'teen_email', 'teen_gender'])) {
                continue; // Already handled above
            }
            $baseRule = $typeRules[$field['type']] ?? ['nullable', 'string'];
            if ($field['required'] ?? false) {
                $baseRule[0] = 'required';
            }
            $rules[$key] = $baseRule;
        }

        $request->validate($rules);

        $result = DB::transaction(function () use ($request, $season, $staff) {
            // 1. Parent
            $parent = User::where('email', $request->parent_email)->first();
            $parentIsNew = false;
            if (!$parent) {
                $parent = User::create([
                    'name' => $request->parent_name,
                    'email' => $request->parent_email,
                    'phone' => $request->parent_phone,
                    'pin' => Hash::make('0000'),
                    'role' => 'parent',
                    'pin_reset_required' => true,
                ]);
                $parentIsNew = true;
            } else {
                // Update phone if missing
                if (!$parent->phone) {
                    $parent->update(['phone' => $request->parent_phone]);
                }
            }

            // 2. Teen
            $teen = User::where('email', $request->teen_email)->first();
            $teenIsNew = false;
            if (!$teen) {
                $teen = User::create([
                    'name' => $request->teen_name,
                    'email' => $request->teen_email,
                    'phone' => $request->teen_phone,
                    'gender' => $request->teen_gender,
                    'date_of_birth' => $request->teen_dob,
                    'pin' => Hash::make('0000'),
                    'role' => 'teen',
                    'pin_reset_required' => true,
                ]);
                $teenIsNew = true;
            }

            // 3. Link Parent & Teen
            if (!$parent->teens()->where('users.id', $teen->id)->exists()) {
                $parent->teens()->attach($teen->id, ['relationship' => $request->relationship ?: 'Parent/Guardian']);
            }

            // 4. Registration
            $registration = Registration::firstOrCreate(
                ['camp_season_id' => $season->id, 'teen_id' => $teen->id],
                [
                    'registered_by' => $staff->id,
                    'status' => 'registered',
                    'phone_carried' => $request->boolean('phone_carried'),
                    'medication_notes' => $request->medication_notes,
                    'medical_conditions' => $request->medical_conditions,
                    'emergency_contact_name' => $parent->name,
                    'emergency_contact_phone' => $parent->phone,
                ]
            );

            // 5. Initial Payment if provided
            $paymentReceipt = null;
            if ($request->filled('initial_payment') && $request->initial_payment > 0) {
                $isMpesa = str_contains($request->payment_method ?? '', 'M-Pesa');
                $receiptNumber = Receipt::generateReceiptNumber($isMpesa ? 'MPESA' : 'DSK');
                $reference = $request->filled('payment_reference') 
                    ? strtoupper(trim($request->payment_reference))
                    : ($isMpesa ? 'MP-' : 'DESK-') . strtoupper(substr(uniqid(), -5));

                $paymentMethod = $request->payment_method ?: 'M-Pesa Paybill';

                Payment::create([
                    'camp_season_id' => $season->id,
                    'registration_id' => $registration->id,
                    'parent_id' => $parent->id,
                    'amount' => $request->initial_payment,
                    'source' => 'direct_payment',
                    'reference' => $reference,
                    'receipt_number' => $receiptNumber,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                    'created_by' => $staff->id,
                ]);

                $paymentReceipt = Receipt::create([
                    'camp_season_id' => $season->id,
                    'receipt_number' => $receiptNumber,
                    'type' => 'direct_payment',
                    'user_id' => $parent->id,
                    'amount' => $request->initial_payment,
                    'description' => "Desk registration payment for {$teen->name}",
                    'meta_data' => [
                        'registration_id' => $registration->id,
                        'teen_name' => $teen->name,
                        'payment_method' => $paymentMethod,
                        'reference' => $reference,
                    ],
                ]);

                // Notification for Payment
                Notification::notifyUser(
                    $parent->id,
                    "Tuition Payment Receipt",
                    "Payment of KES " . number_format($request->initial_payment, 2) . " received for {$teen->name}. Receipt #{$receiptNumber}.",
                    'payment',
                    route('parent.dashboard'),
                    'bi-cash-stack text-success'
                );
            }

            // 1. Notify Parent Account
            Notification::notifyUser(
                $parent->id,
                "Camp Registration Confirmed",
                "{$teen->name} is registered for {$season->name}. Default PIN: 0000. Log in anytime to manage forms and packing lists.",
                'registration',
                route('parent.dashboard'),
                'bi-person-plus-fill text-primary'
            );

            // 2. Notify Teen Account
            Notification::notifyUser(
                $teen->id,
                "Welcome to Teen Camp!",
                "You have been registered for {$season->name}. Default PIN: 0000. Log in to fill out camp forms.",
                'registration',
                route('teen.dashboard'),
                'bi-emoji-smile-fill text-success'
            );

            // 3. Notify Staff
            Notification::notifyStaff(
                "New Desk Intake Recorded",
                "{$teen->name} enrolled by staff {$staff->name}." . ($request->filled('initial_payment') ? " Initial payment: KES " . number_format($request->initial_payment, 2) : ""),
                'registration',
                route('backoffice.admin.dashboard'),
                'bi-person-plus-fill text-primary'
            );

            return [
                'parent' => $parent,
                'teen' => $teen,
                'registration' => $registration,
                'parentIsNew' => $parentIsNew,
                'teenIsNew' => $teenIsNew,
                'paymentReceipt' => $paymentReceipt,
            ];
        });

        return view('backoffice.registration.confirmation', [
            'data' => $result,
            'season' => $season,
        ]);
    }

    /**
     * Camp-Day Live Sign-in Search & Action.
     */
    public function showSignIn(Request $request)
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        $query = Registration::with(['teen.parents', 'payments'])
            ->where('camp_season_id', $season?->id)
            ->where('status', '!=', 'withdrawn');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('teen', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $registrations = $query->orderBy('status', 'asc')->paginate(30);

        return view('backoffice.registration.sign-in', [
            'season' => $season,
            'registrations' => $registrations,
            'search' => $request->search,
        ]);
    }

    /**
     * 1-Click Sign-in Action.
     */
    public function checkIn(Request $request, Registration $registration)
    {
        $staff = Auth::guard('staff')->user();

        $registration->update([
            'status' => 'signed_in',
            'signed_in_at' => now(),
            'signed_in_by' => $staff->id,
        ]);

        // Notify parents of this teen
        foreach ($registration->teen->parents as $p) {
            Notification::notifyUser(
                $p->id,
                "Camper Checked In Safely!",
                "{$registration->teen->name} safely arrived and checked in at the campsite at " . now()->format('h:i A') . ".",
                'checkin',
                route('parent.dashboard'),
                'bi-geo-alt-fill text-success'
            );
        }

        // Notify Staff
        Notification::notifyStaff(
            "Camper Checked In",
            "{$registration->teen->name} signed in by {$staff->name} at " . now()->format('h:i A') . ".",
            'checkin',
            route('backoffice.admin.dashboard'),
            'bi-geo-alt-fill text-success'
        );

        return back()->with('success', "✓ {$registration->teen->name} signed in successfully at " . now()->format('h:i A'));
    }

    /**
     * Undo Sign-in Action.
     */
    public function undoCheckIn(Request $request, Registration $registration)
    {
        $staff = Auth::guard('staff')->user();

        $registration->update([
            'status' => 'registered',
            'signed_in_at' => null,
            'signed_in_by' => null,
        ]);

        Notification::notifyStaff(
            "Sign-in Reversed",
            "Sign-in for {$registration->teen->name} reversed by {$staff->name}.",
            'checkin',
            route('backoffice.admin.dashboard'),
            'bi-arrow-counterclockwise text-warning'
        );

        return back()->with('info', "Sign-in reversed for {$registration->teen->name}.");
    }

    /**
     * Edit 2-Minute Desk Registration.
     */
    public function edit(Registration $registration)
    {
        $registration->load(['teen.parents', 'payments.receipt', 'campSeason']);
        $teen = $registration->teen;
        $parent = $teen->parents->first();
        $season = $registration->campSeason ?? CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        return view('backoffice.registration.edit', [
            'registration' => $registration,
            'teen' => $teen,
            'parent' => $parent,
            'season' => $season,
        ]);
    }

    /**
     * Update 2-Minute Desk Registration contents.
     */
    public function update(Request $request, Registration $registration)
    {
        $staff = Auth::guard('staff')->user();
        $registration->load(['teen.parents']);
        $teen = $registration->teen;
        $parent = $teen->parents->first();

        $request->validate([
            // Parent
            'parent_name' => ['required', 'string', 'max:255'],
            'parent_email' => ['required', 'email'],
            'parent_phone' => ['required', 'string', 'max:50'],
            'relationship' => ['nullable', 'string', 'max:50'],
            // Teen
            'teen_name' => ['required', 'string', 'max:255'],
            'teen_email' => ['required', 'email', 'unique:users,email,' . $teen->id],
            'teen_gender' => ['required', 'in:male,female'],
            'teen_dob' => ['nullable', 'date'],
            'teen_phone' => ['nullable', 'string', 'max:50'],
            // Camp declarations
            'phone_carried' => ['nullable', 'boolean'],
            'medication_notes' => ['nullable', 'string'],
            'medical_conditions' => ['nullable', 'string'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
            // Status & Notes
            'status' => ['required', 'in:registered,signed_in,withdrawn'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($request, $registration, $teen, $parent, $staff) {
            // 1. Update Teen User info
            $teen->update([
                'name' => $request->teen_name,
                'email' => $request->teen_email,
                'gender' => $request->teen_gender,
                'date_of_birth' => $request->teen_dob,
                'phone' => $request->teen_phone,
            ]);

            // 2. Update Parent User info & Relationship
            if ($parent) {
                $parent->update([
                    'name' => $request->parent_name,
                    'email' => $request->parent_email,
                    'phone' => $request->parent_phone,
                ]);

                $relationship = $request->relationship ?: 'Guardian';
                $parent->teens()->updateExistingPivot($teen->id, ['relationship' => $relationship]);
            } else {
                $newParent = User::where('email', $request->parent_email)->first();
                if (!$newParent) {
                    $newParent = User::create([
                        'name' => $request->parent_name,
                        'email' => $request->parent_email,
                        'phone' => $request->parent_phone,
                        'pin' => Hash::make('0000'),
                        'role' => 'parent',
                        'pin_reset_required' => true,
                    ]);
                }
                $newParent->teens()->syncWithoutDetaching([$teen->id => ['relationship' => $request->relationship ?: 'Guardian']]);
                $parent = $newParent;
            }

            // 3. Update Registration fields
            $registration->update([
                'phone_carried' => $request->boolean('phone_carried'),
                'medication_notes' => $request->medication_notes,
                'medical_conditions' => $request->medical_conditions,
                'emergency_contact_name' => $request->emergency_contact_name ?: $request->parent_name,
                'emergency_contact_phone' => $request->emergency_contact_phone ?: $request->parent_phone,
                'status' => $request->status,
                'notes' => $request->notes,
            ]);

            // 4. Notifications
            if ($parent) {
                Notification::notifyUser(
                    $parent->id,
                    "Registration Details Updated",
                    "Registration record for {$request->teen_name} was updated by registration staff ({$staff->name}).",
                    'registration',
                    route('parent.dashboard'),
                    'bi-pencil-square text-primary'
                );
            }

            Notification::notifyStaff(
                "Desk Registration Updated",
                "Registration for {$request->teen_name} was updated by {$staff->name}.",
                'registration',
                route('backoffice.registration.desk'),
                'bi-pencil-square text-info'
            );
        });

        return redirect()->route('backoffice.registration.desk')
            ->with('success', "✓ Registration details for {$request->teen_name} updated successfully.");
    }
}
