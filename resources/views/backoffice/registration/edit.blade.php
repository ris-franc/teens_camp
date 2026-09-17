@extends('layouts.backoffice')

@section('title', 'Edit Registration - ' . $teen->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-size: 20px; font-weight: bold;">
                {{ strtoupper(substr($teen->name, 0, 1)) }}
            </span>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h3 class="fw-bold mb-0">Edit Desk Registration: {{ $teen->name }}</h3>
                    @if($registration->status === 'signed_in')
                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Signed In</span>
                    @elseif($registration->status === 'registered')
                        <span class="badge bg-primary"><i class="bi bi-clock me-1"></i> Registered</span>
                    @else
                        <span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i> Withdrawn</span>
                    @endif
                </div>
                <p class="text-muted small mb-0">
                    Registered for season: <strong>{{ $season ? $season->name : 'N/A' }}</strong> &bull; Registration ID: <code>#REG-{{ str_pad($registration->id, 5, '0', STR_PAD_LEFT) }}</code>
                </p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Desk
            </a>
            <a href="{{ route('backoffice.registration.signin') }}" class="btn btn-outline-light btn-sm">
                <i class="bi bi-clipboard-check me-1"></i> Camp Day Sign-in
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <div class="camp-card p-4 p-md-5 border-danger">
                <form action="{{ route('backoffice.registration.update', $registration) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- 1. Parent / Guardian Section -->
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold text-danger mb-0">
                                <i class="bi bi-person-heart me-2"></i> 1. Parent / Guardian Details
                            </h5>
                            @if($parent)
                                <span class="badge bg-secondary-subtle text-secondary small">Linked User #{{ $parent->id }}</span>
                            @endif
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parent Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="parent_email" value="{{ old('parent_email', $parent?->email) }}" placeholder="parent@example.com" required>
                                <div class="form-text small">Used for parent portal login and notifications.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parent Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="parent_name" value="{{ old('parent_name', $parent?->name) }}" placeholder="Grace Wanjiku" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Parent Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="parent_phone" value="{{ old('parent_phone', $parent?->phone) }}" placeholder="+254 712 345 678" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Relationship</label>
                                @php
                                    $rel = old('relationship', $parent ? ($parent->pivot->relationship ?? 'Guardian') : 'Guardian');
                                @endphp
                                <select class="form-select" name="relationship">
                                    <option value="Mother" {{ $rel === 'Mother' ? 'selected' : '' }}>Mother</option>
                                    <option value="Father" {{ $rel === 'Father' ? 'selected' : '' }}>Father</option>
                                    <option value="Guardian" {{ $rel === 'Guardian' ? 'selected' : '' }}>Guardian</option>
                                    <option value="Sponsor" {{ $rel === 'Sponsor' ? 'selected' : '' }}>Sponsor</option>
                                    <option value="Other" {{ !in_array($rel, ['Mother', 'Father', 'Guardian', 'Sponsor']) ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Teen Camper Section -->
                    <div class="mb-4 pb-4 border-bottom">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="fw-bold text-danger mb-0">
                                <i class="bi bi-person-fill me-2"></i> 2. Teen Camper Information
                            </h5>
                            <span class="badge bg-secondary-subtle text-secondary small">Camper User #{{ $teen->id }}</span>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teen Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="teen_name" value="{{ old('teen_name', $teen->name) }}" placeholder="Brian Mwangi" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Teen Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="teen_email" value="{{ old('teen_email', $teen->email) }}" placeholder="brian@example.com" required>
                                <div class="form-text small">Used for teen portal login.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select class="form-select" name="teen_gender" required>
                                    <option value="male" {{ old('teen_gender', $teen->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('teen_gender', $teen->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <input type="date" class="form-control" name="teen_dob" value="{{ old('teen_dob', $teen->date_of_birth ? $teen->date_of_birth->format('Y-m-d') : '') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Camper Phone</label>
                                <input type="text" class="form-control" name="teen_phone" value="{{ old('teen_phone', $teen->phone) }}" placeholder="+254 7...">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Camp Declarations & Medical -->
                    <div class="mb-4 pb-4 border-bottom">
                        <h5 class="fw-bold text-danger mb-3">
                            <i class="bi bi-shield-plus me-2"></i> 3. Declarations & Medical Information
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <div class="p-3 rounded border" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="phone_carried" value="1" id="phone_carried" {{ old('phone_carried', $registration->phone_carried) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-semibold ms-2" for="phone_carried">
                                            Mobile Phone Declaration: Teen is carrying a mobile device to camp
                                        </label>
                                    </div>
                                    <div class="small text-muted mt-1 ps-4">
                                        Devices are monitored according to the camp mobile phone policy.
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Medical Conditions / Allergies</label>
                                <input type="text" class="form-control" name="medical_conditions" value="{{ old('medical_conditions', $registration->medical_conditions) }}" placeholder="e.g. Asthma, Peanut Allergy, None">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Regular Medication & Dosage Notes</label>
                                <input type="text" class="form-control" name="medication_notes" value="{{ old('medication_notes', $registration->medication_notes) }}" placeholder="e.g. Inhaler twice daily, None">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Administrative Status & Emergency Contact -->
                    <div class="mb-4">
                        <h5 class="fw-bold text-danger mb-3">
                            <i class="bi bi-gear-fill me-2"></i> 4. Emergency Contact & Intake Status
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Emergency Contact Person</label>
                                <input type="text" class="form-control" name="emergency_contact_name" value="{{ old('emergency_contact_name', $registration->emergency_contact_name) }}" placeholder="Grace Wanjiku">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Emergency Contact Phone</label>
                                <input type="text" class="form-control" name="emergency_contact_phone" value="{{ old('emergency_contact_phone', $registration->emergency_contact_phone) }}" placeholder="+254 712 345 678">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Registration Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status" required>
                                    <option value="registered" {{ old('status', $registration->status) === 'registered' ? 'selected' : '' }}>Registered (Ready for Camp)</option>
                                    <option value="signed_in" {{ old('status', $registration->status) === 'signed_in' ? 'selected' : '' }}>Signed In (Arrived at Camp)</option>
                                    <option value="withdrawn" {{ old('status', $registration->status) === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Administrative Desk Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Internal desk notes regarding this registration...">{{ old('notes', $registration->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-3 border-top">
                        <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancel Changes
                        </a>
                        <button type="submit" class="btn btn-camp-red btn-lg px-5">
                            <i class="bi bi-check-circle-fill me-2"></i> Save Registration Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar / Financial & Payment History Column -->
        <div class="col-lg-4">
            <!-- Payment & Receipt Summary -->
            <div class="camp-card p-4 mb-4 border-danger">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-cash-coin text-danger me-2"></i> Camp Fee & Receipts
                    </h5>
                    <span class="badge bg-danger px-2 py-1">Season Price: KES {{ number_format($season?->price ?? 0, 2) }}</span>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-3 rounded border text-center" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                            <span class="small text-muted text-uppercase fw-bold">Total Paid</span>
                            <div class="fs-4 fw-bold text-success mt-1">KES {{ number_format($registration->total_paid, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded border text-center" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                            <span class="small text-muted text-uppercase fw-bold">Balance</span>
                            <div class="fs-4 fw-bold {{ $registration->balance_remaining > 0 ? 'text-danger' : 'text-success' }} mt-1">
                                KES {{ number_format($registration->balance_remaining, 2) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Payment Completion</span>
                        <span class="fw-bold">{{ $registration->payment_percent }}%</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: {{ $registration->payment_percent }}%;"></div>
                    </div>
                </div>

                <h6 class="fw-bold small text-uppercase text-muted mt-4 mb-2">Recorded Payments ({{ $registration->payments->count() }})</h6>
                <div class="list-group list-group-flush border rounded overflow-hidden">
                    @forelse($registration->payments as $pay)
                        <div class="list-group-item bg-dark text-white p-3 border-secondary border-opacity-25">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <strong class="text-success fs-6">KES {{ number_format($pay->amount, 2) }}</strong>
                                <span class="badge bg-success-subtle text-success small text-uppercase">{{ $pay->status }}</span>
                            </div>
                            <div class="small text-muted d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-clock me-1"></i> {{ $pay->created_at->format('M d, Y h:i A') }}</span>
                                <span class="badge bg-secondary font-monospace">{{ $pay->reference }}</span>
                            </div>
                            @if($pay->receipt_number)
                                <div class="mt-2 pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-between align-items-center">
                                    <span class="small text-muted font-monospace"><i class="bi bi-receipt text-danger me-1"></i> {{ $pay->receipt_number }}</span>
                                    <a href="{{ route('receipts.show', $pay->receipt_number) }}" target="_blank" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 11px;">
                                        <i class="bi bi-printer me-1"></i> View Receipt
                                    </a>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-3 text-center text-muted small">
                            No payments recorded yet for this camper.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- PIN & Account Status -->
            <div class="camp-card p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">
                    <i class="bi bi-shield-lock text-danger me-2"></i> PIN Credentials
                </h5>
                <p class="small text-muted mb-3">
                    Both parent and teen access portals with their email and 4-digit PIN. Default reset PIN is <code>0000</code>.
                </p>
                <div class="d-flex flex-column gap-2">
                    <div class="p-2 rounded border small d-flex justify-content-between align-items-center" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                        <span><strong>Teen:</strong> {{ $teen->email }}</span>
                        @if($teen->pin_reset_required)
                            <span class="badge bg-warning text-dark">PIN Reset Required (0000)</span>
                        @else
                            <span class="badge bg-success">PIN Active</span>
                        @endif
                    </div>
                    @if($parent)
                        <div class="p-2 rounded border small d-flex justify-content-between align-items-center" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                            <span><strong>Parent:</strong> {{ $parent->email }}</span>
                            @if($parent->pin_reset_required)
                                <span class="badge bg-warning text-dark">PIN Reset Required (0000)</span>
                            @else
                                <span class="badge bg-success">PIN Active</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
