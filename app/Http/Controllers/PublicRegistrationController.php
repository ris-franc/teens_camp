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

class PublicRegistrationController extends Controller
{
    /**
     * Show Public 2-Minute Express Camper Registration Form.
     */
    public function showForm()
    {
        $season     = CampSeason::getActive();
        $formConfig = DeskFormConfig::getCurrent();

        return view('public.register', [
            'season'     => $season,
            'formConfig' => $formConfig,
        ]);
    }

    /**
     * Process Public 2-Minute Express Camper Registration:
     * - Parent lookup or creation (PIN 0000)
     * - Teen creation (PIN 0000)
     * - Link Parent & Teen
     * - Register in Active Season
     * - Optional M-Pesa / Cash payment
     * - Instant auto-login of parent and redirect to dashboard/pin setup
     */
    public function process(Request $request)
    {
        $season = CampSeason::getActive();

        if (!$season || !$season->isRegistrationOpen()) {
            return back()->withErrors(['error' => 'Registration is currently closed for this camp season.']);
        }

        // Build validation rules from active form config (same as desk intake)
        $formConfig = DeskFormConfig::getCurrent();
        $rules = [
            'parent_name'  => ['required', 'string', 'max:255'],
            'parent_email' => ['required', 'email'],
            'parent_phone' => ['required', 'string', 'max:50'],
            'teen_name'    => ['required', 'string', 'max:255'],
            'teen_email'   => ['required', 'email'],
            'teen_gender'  => ['required', 'in:male,female'],
        ];

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
                continue;
            }
            $baseRule = $typeRules[$field['type']] ?? ['nullable', 'string'];
            if ($field['required'] ?? false) {
                $baseRule[0] = 'required';
            }
            $rules[$key] = $baseRule;
        }

        $request->validate($rules);

        $parent = null;

        $result = DB::transaction(function () use ($request, $season, &$parent) {
            // 1. Parent Account
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
                if (!$parent->phone) {
                    $parent->update(['phone' => $request->parent_phone]);
                }
            }

            // 2. Teen Account
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

            // 4. Registration Record
            $registration = Registration::firstOrCreate(
                ['camp_season_id' => $season->id, 'teen_id' => $teen->id],
                [
                    'registered_by' => null, // Self-registered via public portal
                    'status' => 'registered',
                    'phone_carried' => $request->boolean('phone_carried'),
                    'medication_notes' => $request->medication_notes,
                    'medical_conditions' => $request->medical_conditions,
                    'emergency_contact_name' => $parent->name,
                    'emergency_contact_phone' => $parent->phone,
                ]
            );

            // 5. Optional Initial Payment / M-Pesa
            $paymentReceipt = null;
            if ($request->filled('initial_payment') && $request->initial_payment > 0) {
                $isMpesa = str_contains($request->payment_method ?? '', 'M-Pesa') || empty($request->payment_method);
                $receiptNumber = Receipt::generateReceiptNumber($isMpesa ? 'MPESA' : 'REG');
                $reference = $request->filled('payment_reference')
                    ? strtoupper(trim($request->payment_reference))
                    : ($isMpesa ? 'MP-' : 'REG-') . strtoupper(substr(uniqid(), -5));

                $paymentMethod = $request->payment_method ?: 'M-Pesa Paybill';

                $payment = Payment::create([
                    'camp_season_id' => $season->id,
                    'registration_id' => $registration->id,
                    'parent_id' => $parent->id,
                    'amount' => $request->initial_payment,
                    'source' => 'direct_payment',
                    'reference' => $reference,
                    'receipt_number' => $receiptNumber,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                    'created_by' => $parent->id,
                ]);

                $paymentReceipt = Receipt::create([
                    'camp_season_id' => $season->id,
                    'receipt_number' => $receiptNumber,
                    'type' => 'direct_payment',
                    'user_id' => $parent->id,
                    'amount' => $request->initial_payment,
                    'description' => "Registration payment for {$teen->name}",
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
                    "Payment of KES " . number_format($request->initial_payment, 2) . " received for {$teen->name}. Official Receipt #{$receiptNumber}.",
                    'payment',
                    route('receipts.show', $receiptNumber),
                    'bi-receipt-cutoff text-success'
                );
            }

            // Notifications
            Notification::notifyUser(
                $parent->id,
                "Camp Registration Confirmed",
                "{$teen->name} is successfully registered for {$season->name}. Default PIN is 0000. Welcome to our church camp family!",
                'registration',
                route('parent.dashboard'),
                'bi-check-circle-fill text-success'
            );

            Notification::notifyUser(
                $teen->id,
                "Welcome to Teen Camp 2026!",
                "You have been registered for {$season->name}. Default PIN: 0000. Log in anytime to access forms and track your packing list.",
                'registration',
                route('teen.dashboard'),
                'bi-fire text-danger'
            );

            Notification::notifyStaff(
                "New Online Camper Registration",
                "{$teen->name} was registered online by parent {$parent->name}." . ($request->filled('initial_payment') ? " Initial payment: KES " . number_format($request->initial_payment, 2) : ""),
                'registration',
                route('backoffice.registration.desk'),
                'bi-person-plus-fill text-primary'
            );

            return [
                'parent' => $parent,
                'teen' => $teen,
                'registration' => $registration,
                'paymentReceipt' => $paymentReceipt,
            ];
        });

        // Automatically log in the parent account
        Auth::guard('web')->login($parent);
        $request->session()->regenerate();

        if ($result['paymentReceipt']) {
            session(['confirmed_receipt_number' => $result['paymentReceipt']->receipt_number]);
        }

        return redirect()->route('parent.dashboard')->with('success', "🎉 Welcome, {$parent->name}! {$request->teen_name} is successfully registered for {$season->name}.");
    }
}
