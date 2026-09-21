<?php

namespace App\Http\Controllers;

use App\Models\AdoptATeenRequest;
use App\Models\CampSeason;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionValue;
use App\Models\Notification;
use App\Models\PackingList;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $parent = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        $teens = $parent->teens()->with(['registrations' => function ($q) use ($season) {
            if ($season) {
                $q->where('camp_season_id', $season->id);
            }
        }])->get();

        $selectedChildId = $request->query('child'); // 'all' or teen ID
        if (!$selectedChildId || ($selectedChildId !== 'all' && !$teens->pluck('id')->contains($selectedChildId))) {
            $selectedChildId = $teens->first()?->id ?? 'all';
        }

        // Active registrations
        $activeRegistrations = collect();
        if ($season) {
            $query = Registration::where('camp_season_id', $season->id)
                ->whereIn('teen_id', $teens->pluck('id'))
                ->with(['teen', 'payments']);

            if ($selectedChildId !== 'all') {
                $query->where('teen_id', $selectedChildId);
            }
            $activeRegistrations = $query->get();
        }

        // Pending Form Reviews for parent
        $pendingFormReviews = collect();
        if ($season) {
            $pendingFormReviews = FormSubmission::where('camp_season_id', $season->id)
                ->where('status', 'pending_parent_review')
                ->whereIn('teen_id', $teens->pluck('id'))
                ->with(['form', 'teen', 'values.field'])
                ->get();
        }

        // Adopt a teen requests
        $adoptRequests = collect();
        if ($season) {
            $adoptRequests = AdoptATeenRequest::where('camp_season_id', $season->id)
                ->where('parent_id', $parent->id)
                ->with('teen')
                ->latest()
                ->get();
        }

        // Packing list
        $packingList = collect();
        if ($season) {
            $packingList = PackingList::where('camp_season_id', $season->id)
                ->where('is_released', true)
                ->get()
                ->groupBy('category');
        }

        // Parent forms and submissions
        $parentForms = collect();
        $parentSubmissions = collect();
        $completedFamilySubmissions = collect();
        if ($season) {
            $parentForms = Form::where('camp_season_id', $season->id)
                ->where('is_published', true)
                ->whereIn('target_role', ['parent', 'both'])
                ->with('fields')
                ->get();

            $parentSubmissions = FormSubmission::where('camp_season_id', $season->id)
                ->where('user_id', $parent->id)
                ->with(['values.field', 'form', 'teen'])
                ->get()
                ->keyBy('form_id');

            $completedFamilySubmissions = FormSubmission::where('camp_season_id', $season->id)
                ->where('status', 'submitted')
                ->where(function ($q) use ($parent, $teens) {
                    $q->where('user_id', $parent->id)
                      ->orWhereIn('teen_id', $teens->pluck('id'));
                })
                ->with(['form', 'teen', 'user', 'values.field', 'reviewedByParent'])
                ->latest('submitted_at')
                ->get();
        }

        // Payments & Receipts for parent
        $payments = collect();
        $receipts = collect();
        $confirmedReceipt = null;
        if ($season) {
            $teenIds = $teens->pluck('id')->toArray();
            $regIds = $activeRegistrations->pluck('id')->toArray();

            $payments = Payment::where('camp_season_id', $season->id)
                ->where(function ($q) use ($parent, $regIds) {
                    $q->where('parent_id', $parent->id)
                      ->orWhereIn('registration_id', $regIds);
                })
                ->with(['registration.teen'])
                ->latest()
                ->get();

            $receipts = Receipt::where('camp_season_id', $season->id)
                ->where(function ($q) use ($parent, $teenIds, $regIds) {
                    $q->where('user_id', $parent->id)
                      ->orWhereIn('meta_data->teen_id', $teenIds)
                      ->orWhereIn('meta_data->registration_id', $regIds);
                })
                ->latest()
                ->get();

            if (session('confirmed_receipt_number')) {
                $confirmedReceipt = Receipt::where('receipt_number', session('confirmed_receipt_number'))->first();
            }
        }

        return view('parent.dashboard', [
            'parent' => $parent,
            'season' => $season,
            'teens' => $teens,
            'selectedChildId' => $selectedChildId,
            'activeRegistrations' => $activeRegistrations,
            'pendingFormReviews' => $pendingFormReviews,
            'adoptRequests' => $adoptRequests,
            'packingList' => $packingList,
            'parentForms' => $parentForms,
            'parentSubmissions' => $parentSubmissions,
            'completedFamilySubmissions' => $completedFamilySubmissions,
            'payments' => $payments,
            'receipts' => $receipts,
            'confirmedReceipt' => $confirmedReceipt,
            'mpesaSettings' => \App\Models\MpesaSetting::getSettings(),
        ]);
    }

    /**
     * Update child phone carrying & medication declaration.
     */
    public function updateDeclaration(Request $request, Registration $registration)
    {
        $parent = Auth::guard('web')->user();
        if (!$parent->teens()->where('users.id', $registration->teen_id)->exists()) {
            abort(403, 'Unauthorized child access.');
        }

        $request->validate([
            'phone_carried' => ['required', 'boolean'],
            'medication_notes' => ['nullable', 'string', 'max:1000'],
            'medical_conditions' => ['nullable', 'string', 'max:1000'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:50'],
        ]);

        $registration->update([
            'phone_carried' => $request->boolean('phone_carried'),
            'medication_notes' => $request->medication_notes,
            'medical_conditions' => $request->medical_conditions,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
        ]);

        return back()->with('success', "Medical & phone declaration updated for {$registration->teen->name}.");
    }

    /**
     * Withdraw teen from camp.
     */
    public function withdrawTeen(Request $request, Registration $registration)
    {
        $parent = Auth::guard('web')->user();
        if (!$parent->teens()->where('users.id', $registration->teen_id)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $registration->update([
            'status' => 'withdrawn',
            'notes' => ($registration->notes ? $registration->notes . "\n" : "") . "Withdrawn by parent on " . now()->format('Y-m-d H:i') . ": " . $request->input('reason', 'Parent request'),
        ]);

        return back()->with('info', "{$registration->teen->name} has been withdrawn from this camp season.");
    }

    /**
     * Parent approves teen-filled form.
     */
    public function approveFormSubmission(Request $request, FormSubmission $submission)
    {
        $parent = Auth::guard('web')->user();
        if (!$parent->teens()->where('users.id', $submission->teen_id)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $submission->status = 'submitted';
        $submission->reviewed_by_parent_id = $parent->id;
        $submission->reviewed_at = now();
        $submission->submitted_at = now();
        $submission->parent_feedback = $request->input('notes', 'Approved by parent');
        $submission->save();

        // 1. Notify Teen
        Notification::notifyUser(
            $submission->teen_id,
            "Form Approved by Parent",
            "Your parent approved '{$submission->form->title}'. It is now officially submitted to Camp Administration.",
            'form',
            route('teen.dashboard'),
            'bi-file-earmark-check text-success'
        );

        // 2. Notify Staff / Admin
        Notification::notifyStaff(
            "Parent Signed Consent Form",
            "{$parent->name} approved and signed off on '{$submission->form->title}' for {$submission->teen->name}.",
            'form',
            route('backoffice.admin.dashboard'),
            'bi-file-earmark-check text-success'
        );

        return back()->with('success', 'Form approved and submitted to Camp Administration!');
    }

    /**
     * Parent returns teen-filled form for corrections.
     */
    public function returnFormSubmission(Request $request, FormSubmission $submission)
    {
        $parent = Auth::guard('web')->user();
        if (!$parent->teens()->where('users.id', $submission->teen_id)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'parent_feedback' => ['required', 'string'],
        ]);

        $submission->status = 'returned';
        $submission->reviewed_by_parent_id = $parent->id;
        $submission->reviewed_at = now();
        $submission->parent_feedback = $request->parent_feedback;
        $submission->save();

        // Notify Teen of Return
        Notification::notifyUser(
            $submission->teen_id,
            "Form Returned for Corrections",
            "Your parent returned '{$submission->form->title}' with feedback: \"{$request->parent_feedback}\"",
            'form',
            route('teen.dashboard'),
            'bi-arrow-repeat text-warning'
        );

        return back()->with('info', "Form returned to {$submission->teen->name} with your feedback.");
    }

    /**
     * Show form for parent to fill.
     */
    public function showForm(Form $form)
    {
        $parent = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        if ($form->camp_season_id !== $season?->id || !in_array($form->target_role, ['parent', 'both'])) {
            abort(403, 'Form not available for parent completion.');
        }

        $form->load('fields');

        $teens = $parent->teens()->whereHas('registrations', function ($q) use ($season) {
            $q->where('camp_season_id', $season->id);
        })->get();

        $selectedTeenId = request()->query('teen_id');
        if (!$selectedTeenId && $teens->isNotEmpty()) {
            $selectedTeenId = $teens->first()->id;
        }

        // Check existing submission
        $submissionQuery = FormSubmission::where('form_id', $form->id)
            ->where('user_id', $parent->id);
        if ($selectedTeenId) {
            $submissionQuery->where(function ($q) use ($selectedTeenId) {
                $q->where('teen_id', $selectedTeenId)->orWhereNull('teen_id');
            });
        }
        $submission = $submissionQuery->with('values')->first();

        return view('parent.form-fill', [
            'form' => $form,
            'submission' => $submission,
            'season' => $season,
            'parent' => $parent,
            'teens' => $teens,
            'selectedTeenId' => $selectedTeenId,
        ]);
    }

    /**
     * Submit parent responses for a form.
     */
    public function submitForm(Request $request, Form $form)
    {
        $parent = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        $form->load('fields');

        $teenId = $request->filled('teen_id') ? $request->teen_id : null;
        if ($teenId && !$parent->teens()->where('users.id', $teenId)->exists()) {
            abort(403, 'Unauthorized child selection.');
        }

        // Check if existing submission
        $submission = FormSubmission::firstOrNew([
            'form_id' => $form->id,
            'camp_season_id' => $season->id,
            'user_id' => $parent->id,
            'teen_id' => $teenId,
        ]);

        $submission->status = 'submitted';
        $submission->submitted_at = now();
        $submission->save();

        // Save fields
        foreach ($form->fields as $field) {
            $valRecord = FormSubmissionValue::firstOrNew([
                'form_submission_id' => $submission->id,
                'form_field_id' => $field->id,
            ]);

            if ($field->field_type === 'file_upload') {
                if ($request->hasFile("field_{$field->id}")) {
                    $path = $request->file("field_{$field->id}")->store('form_uploads', 'public');
                    $valRecord->file_path = $path;
                    $valRecord->value = $request->file("field_{$field->id}")->getClientOriginalName();
                }
            } elseif ($field->field_type === 'checkbox') {
                $values = $request->input("field_{$field->id}", []);
                $valRecord->value = is_array($values) ? json_encode($values) : $values;
            } else {
                $valRecord->value = $request->input("field_{$field->id}");
            }

            $valRecord->save();
        }

        $teen = $teenId ? User::find($teenId) : null;

        Notification::notifyStaff(
            "Parent Form Submitted",
            "{$parent->name} submitted '{$form->title}'" . ($teen ? " for {$teen->name}." : "."),
            'form',
            route('backoffice.forms.show', $form->id),
            'bi-file-earmark-check-fill text-success'
        );

        Notification::notifyUser(
            $parent->id,
            "Form Submitted Successfully",
            "Your responses for '{$form->title}' have been submitted to Camp Administration.",
            'form',
            route('parent.dashboard'),
            'bi-check2-circle text-success'
        );

        return redirect()->route('parent.dashboard')->with('success', "Form '{$form->title}' submitted successfully!");
    }

    /**
     * View completed form submission details.
     */
    public function showSubmission(FormSubmission $submission)
    {
        $parent = Auth::guard('web')->user();

        // Check authorization
        $isMySubmission = $submission->user_id === $parent->id;
        $isMyTeensSubmission = $submission->teen_id && $parent->teens()->where('users.id', $submission->teen_id)->exists();

        if (!$isMySubmission && !$isMyTeensSubmission) {
            abort(403, 'Unauthorized access to submission.');
        }

        $submission->load(['form.fields', 'values.field', 'teen', 'user', 'reviewedByParent']);

        return view('forms.submission-show', [
            'submission' => $submission,
            'form' => $submission->form,
            'isParent' => true,
            'backRoute' => route('parent.dashboard'),
        ]);
    }

    /**
     * Parent applies for Adopt-a-Teen financial aid.
     */
    public function applyAdoptATeen(Request $request)
    {
        $parent = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        if (!$season) {
            return back()->withErrors(['error' => 'No active camp season found.']);
        }

        $request->validate([
            'teen_id' => ['required', 'exists:users,id'],
            'amount_requested' => ['required', 'numeric', 'min:1'],
            'reason' => ['required', 'string', 'max:1500'],
        ]);

        if (!$parent->teens()->where('users.id', $request->teen_id)->exists()) {
            abort(403, 'Unauthorized child.');
        }

        $registration = Registration::where('camp_season_id', $season->id)
            ->where('teen_id', $request->teen_id)
            ->first();

        if (!$registration) {
            return back()->withErrors(['teen_id' => 'Child must be registered in the active season first.']);
        }

        // Validate: cannot exceed full camp price minus paid
        $maxAllowed = max(0, $season->price - $registration->total_paid);
        if ($request->amount_requested > $maxAllowed) {
            return back()->withErrors(['amount_requested' => "Maximum financial aid requested cannot exceed {$maxAllowed} (Camp fee is {$season->price} and {$registration->total_paid} is already paid)."]);
        }

        AdoptATeenRequest::create([
            'camp_season_id' => $season->id,
            'parent_id' => $parent->id,
            'teen_id' => $request->teen_id,
            'amount_requested' => $request->amount_requested,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $teen = User::find($request->teen_id);

        // Notify Staff and Pastors
        Notification::notifyStaff(
            "Adopt-a-Teen Aid Request",
            "{$parent->name} submitted a financial assistance request of KES " . number_format($request->amount_requested, 2) . " for {$teen?->name}.",
            'adopt_a_teen',
            route('backoffice.admin.dashboard'),
            'bi-heart-pulse-fill text-danger'
        );

        return back()->with('success', 'Adopt-a-Teen financial aid application submitted! Church leadership will review it shortly.');
    }

    /**
     * Parent makes a direct payment.
     */
    public function makePayment(Request $request)
    {
        $parent = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'string'],
            'mpesa_code' => ['nullable', 'string', 'max:20'],
            'mpesa_phone' => ['nullable', 'string', 'max:20'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        $registration = Registration::with('teen')->findOrFail($request->registration_id);
        if (!$parent->teens()->where('users.id', $registration->teen_id)->exists()) {
            abort(403, 'Unauthorized.');
        }

        $receiptNumber = Receipt::generateReceiptNumber('MPESA');
        $reference = $request->mpesa_code 
            ? strtoupper(trim($request->mpesa_code))
            : ($request->reference ?: 'MP-' . strtoupper(substr(uniqid(), -6)));

        $paymentMethod = $request->payment_method ?: 'M-Pesa Paybill';

        $payment = Payment::create([
            'camp_season_id' => $season->id,
            'registration_id' => $registration->id,
            'parent_id' => $parent->id,
            'amount' => $request->amount,
            'source' => 'direct_payment',
            'reference' => $reference,
            'receipt_number' => $receiptNumber,
            'payment_method' => $paymentMethod,
            'status' => 'completed',
            'created_by' => $parent->id,
        ]);

        Receipt::create([
            'camp_season_id' => $season->id,
            'receipt_number' => $receiptNumber,
            'type' => 'direct_payment',
            'user_id' => $parent->id,
            'amount' => $request->amount,
            'description' => "Payment for camp tuition for {$registration->teen->name} ({$season->name})",
            'meta_data' => [
                'registration_id' => $registration->id,
                'teen_id' => $registration->teen_id,
                'parent_id' => $parent->id,
                'teen_name' => $registration->teen->name,
                'payment_method' => $paymentMethod,
                'reference' => $reference,
                'mpesa_phone' => $request->mpesa_phone ?: $parent->phone,
                'mpesa_code' => $reference,
            ],
        ]);

        // 1. Notify Parent Account with receipt confirmation
        Notification::notifyUser(
            $parent->id,
            "Receipt Confirmation: Camp Tuition Paid",
            "Payment of KES " . number_format($request->amount, 2) . " for {$registration->teen->name} confirmed. Official Receipt #{$receiptNumber} (Ref: {$reference}).",
            'payment',
            route('receipts.show', $receiptNumber),
            'bi-receipt-cutoff text-success'
        );

        // 2. Notify Teen Account
        Notification::notifyUser(
            $registration->teen_id,
            "Tuition Payment Credited",
            "KES " . number_format($request->amount, 2) . " was credited toward your camp fees. Official Receipt #{$receiptNumber}.",
            'payment',
            route('receipts.show', $receiptNumber),
            'bi-cash-stack text-success'
        );

        // 3. Notify Staff / Admins (Backend Team receipt confirmation)
        Notification::notifyStaff(
            "Payment Received: KES " . number_format($request->amount, 2),
            "Parent {$parent->name} paid for camper {$registration->teen->name} via {$paymentMethod}. Official Receipt #{$receiptNumber} issued (Ref: {$reference}).",
            'payment',
            route('receipts.show', $receiptNumber),
            'bi-receipt text-success'
        );

        return back()
            ->with('success', "Payment for camp completed successfully! Official Receipt #{$receiptNumber} issued.")
            ->with('confirmed_receipt_number', $receiptNumber);
    }

    /**
     * Parent registers an additional teen directly from the portal.
     */
    public function registerTeen(Request $request)
    {
        $parent = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        if (!$season) {
            return back()->with('warning', 'No active camp season found for registration.');
        }

        if (!$season->isRegistrationOpen()) {
            return back()->with('warning', 'Registration for ' . $season->name . ' is currently closed.');
        }

        $request->validate([
            'teen_name' => ['required', 'string', 'max:255'],
            'teen_email' => ['nullable', 'email', 'max:255'],
            'teen_gender' => ['required', 'in:male,female'],
            'teen_dob' => ['nullable', 'date'],
            'teen_phone' => ['nullable', 'string', 'max:30'],
            'relationship' => ['nullable', 'string', 'max:50'],
            'phone_carried' => ['nullable'],
            'medical_conditions' => ['nullable', 'string', 'max:1000'],
            'medication_notes' => ['nullable', 'string', 'max:1000'],
            'initial_payment' => ['nullable', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:50'],
        ]);

        // Generate email if omitted
        $teenEmail = $request->teen_email;
        if (empty($teenEmail)) {
            $slug = \Illuminate\Support\Str::slug($request->teen_name, '.');
            $teenEmail = $slug . '.' . rand(100, 999) . '@camper.church.org';
        }

        // Find or create teen User
        $teen = User::where('email', $teenEmail)->first();
        if (!$teen) {
            $teen = User::create([
                'name' => $request->teen_name,
                'email' => $teenEmail,
                'pin' => \Illuminate\Support\Facades\Hash::make('0000'),
                'role' => 'teen',
                'phone' => $request->teen_phone,
                'gender' => $request->teen_gender,
                'date_of_birth' => $request->teen_dob,
                'pin_reset_required' => true,
            ]);
        }

        // Attach teen to parent if not attached
        $relationship = $request->relationship ?: 'Parent';
        if (!$parent->teens()->where('teen_id', $teen->id)->exists()) {
            $parent->teens()->attach($teen->id, ['relationship' => $relationship]);
        }

        // Check if already registered for active season
        $reg = Registration::where('camp_season_id', $season->id)
            ->where('teen_id', $teen->id)
            ->first();

        if ($reg) {
            return back()->with('info', "{$teen->name} is already registered for {$season->name}.");
        }

        // Create registration
        $reg = Registration::create([
            'camp_season_id' => $season->id,
            'teen_id' => $teen->id,
            'registered_by' => $parent->id,
            'status' => 'registered',
            'phone_carried' => (bool) $request->phone_carried,
            'medical_conditions' => $request->medical_conditions ?: 'None',
            'medication_notes' => $request->medication_notes ?: 'None',
            'emergency_contact_name' => $parent->name,
            'emergency_contact_phone' => $parent->phone ?: '+254 700 000 000',
        ]);

        // If initial payment provided
        if ($request->initial_payment && $request->initial_payment > 0) {
            $receiptNum = 'REC-' . date('Ymd') . '-' . rand(10000, 99999);
            $paymentRef = $request->payment_reference ?: ('PAY-' . strtoupper(\Illuminate\Support\Str::random(8)));

            Payment::create([
                'camp_season_id' => $season->id,
                'registration_id' => $reg->id,
                'parent_id' => $parent->id,
                'amount' => $request->initial_payment,
                'source' => 'direct_payment',
                'reference' => $paymentRef,
                'receipt_number' => $receiptNum,
                'payment_method' => 'M-Pesa / Parent Portal',
                'status' => 'completed',
                'created_by' => $parent->id,
            ]);

            Receipt::create([
                'camp_season_id' => $season->id,
                'receipt_number' => $receiptNum,
                'type' => 'direct_payment',
                'user_id' => $parent->id,
                'amount' => $request->initial_payment,
                'description' => "Initial camp payment for {$teen->name} ({$season->name})",
                'meta_data' => [
                    'registration_id' => $reg->id,
                    'teen_name' => $teen->name,
                    'reference' => $paymentRef,
                    'payment_method' => 'M-Pesa / Parent Portal',
                ],
            ]);
        }

        // Notifications
        Notification::notifyUser(
            $parent->id,
            "Camper Registered: {$teen->name}",
            "You have successfully registered {$teen->name} for {$season->name}. Default PIN is 0000.",
            'registration',
            route('parent.dashboard', ['child' => $teen->id]),
            'bi-person-check-fill text-success'
        );

        Notification::notifyStaff(
            "New Camper Added by Parent: {$teen->name}",
            "Parent {$parent->name} registered {$teen->name} ({$teen->gender}) for {$season->name}.",
            'registration',
            route('backoffice.admin.dashboard'),
            'bi-person-plus-fill text-danger'
        );

        return redirect()->route('parent.dashboard', ['child' => $teen->id])
            ->with('success', "{$teen->name} successfully registered for {$season->name}!");
    }
}
