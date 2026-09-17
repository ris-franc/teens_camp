@extends('layouts.app')

@section('title', 'Parent Portal Dashboard — Teen Camp')

@push('styles')
<style>
/* ── Parent Dashboard Modern Design System ── */
.parent-kpi-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 18px;
    padding: 1.5rem;
    transition: transform .2s, border-color .2s;
    height: 100%;
}

.parent-kpi-card:hover {
    border-color: rgba(239, 68, 68, 0.3);
    transform: translateY(-2px);
}

.kpi-icon-pill {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.35rem;
    flex-shrink: 0;
}

.camper-card-modern {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 20px;
    padding: 2rem;
    position: relative;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all .25s ease;
}

.camper-card-modern:hover {
    border-color: rgba(239, 68, 68, 0.35);
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.35);
}

.camper-avatar-badge {
    width: 54px;
    height: 54px;
    border-radius: 16px;
    background: #D32F2F;
    color: #fff;
    font-size: 1.4rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(211, 47, 47, 0.35);
    flex-shrink: 0;
}

.panel-well {
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 14px;
    padding: 1.25rem;
}

.info-chip-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .75rem;
}

@media (max-width: 576px) {
    .info-chip-grid { grid-template-columns: 1fr; }
}

.info-chip {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 10px;
    padding: .65rem .9rem;
    font-size: .82rem;
}

.info-chip strong {
    color: rgba(255, 255, 255, 0.85);
}

.btn-pay-camp-primary {
    background: #16A34A;
    border: none;
    color: #fff !important;
    font-weight: 700;
    font-size: .95rem;
    border-radius: 12px;
    padding: .75rem 1.4rem;
    transition: all .2s;
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.btn-pay-camp-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 22px rgba(34, 197, 94, 0.45);
    color: #fff !important;
}

.nav-child-pill {
    padding: .45rem 1.25rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: .85rem;
    text-decoration: none;
    transition: all .2s;
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: rgba(255, 255, 255, 0.7);
}

.nav-child-pill.active {
    background: #EF4444;
    border-color: #EF4444;
    color: #fff;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.35);
}

.nav-child-pill:hover:not(.active) {
    background: rgba(255, 255, 255, 0.06);
    color: #fff;
}
</style>
@endpush

@section('content')
<div class="container py-3">

    {{-- ══════════════════════════════════════════════════════════
         TOP HEADER: PARENT GREETING & CAMPER SWITCHER
    ══════════════════════════════════════════════════════════ --}}
    <div class="camp-card p-4 mb-4 border-danger">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-danger text-uppercase px-2 py-1">Parent Portal</span>
                    <span class="text-muted small">| Season: <strong>{{ $season ? $season->name : 'Teen Camp 2026' }}</strong></span>
                </div>
                <h2 class="fw-black text-white mb-1" style="letter-spacing: -.5px;">Welcome, {{ $parent->name }}</h2>
                <p class="text-muted small mb-0">Manage camp fee payments, view official receipts, and review declarations for your family.</p>
            </div>

            {{-- Child Switcher Segmented Pills & Register New Teen Action --}}
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="small text-muted fw-bold text-uppercase me-1">
                    <i class="bi bi-people-fill text-danger me-1"></i> Campers:
                </span>
                <a href="{{ route('parent.dashboard', ['child' => 'all']) }}" 
                   class="nav-child-pill {{ $selectedChildId === 'all' ? 'active' : '' }}">
                    All Campers ({{ $teens->count() }})
                </a>
                @foreach($teens as $t)
                    <a href="{{ route('parent.dashboard', ['child' => $t->id]) }}" 
                       class="nav-child-pill {{ $selectedChildId == $t->id ? 'active' : '' }}">
                        {{ $t->name }}
                    </a>
                @endforeach

                <button type="button" class="btn btn-camp-red btn-sm px-3 py-1 ms-lg-2 d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#registerNewTeenModal">
                    <i class="bi bi-person-plus-fill"></i>
                    <span>+ Register New Teen</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         QUICK KPI OVERVIEW ROW (ORGANIZED FINANCIALS)
    ══════════════════════════════════════════════════════════ --}}
    @php
        $totalRegistered = $activeRegistrations->count();
        $totalPaidSum = $activeRegistrations->sum('total_paid');
        $totalSeasonFee = $season ? ($totalRegistered * $season->price) : 0;
        $totalBalanceSum = $activeRegistrations->sum('balance_remaining');
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="parent-kpi-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: .5px;">Registered Campers</span>
                    <div class="kpi-icon-pill" style="background: rgba(239, 68, 68, 0.12); color: #EF4444;">
                        <i class="bi bi-people"></i>
                    </div>
                </div>
                <div class="fs-3 fw-black text-white">{{ $totalRegistered }}</div>
                <div class="small text-muted mt-1">Linked to your parent account</div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="parent-kpi-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: .5px;">Total Paid</span>
                    <div class="kpi-icon-pill" style="background: rgba(34, 197, 94, 0.12); color: #22C55E;">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                </div>
                <div class="fs-3 fw-black text-success">KES {{ number_format($totalPaidSum, 2) }}</div>
                <div class="small text-muted mt-1">Verified via M-Pesa &amp; Desk</div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="parent-kpi-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: .5px;">Remaining Balance</span>
                    <div class="kpi-icon-pill" style="background: rgba(234, 179, 8, 0.12); color: #EAB308;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                </div>
                <div class="fs-3 fw-black {{ $totalBalanceSum > 0 ? 'text-danger' : 'text-success' }}">
                    KES {{ number_format($totalBalanceSum, 2) }}
                </div>
                <div class="small text-muted mt-1">{{ $totalBalanceSum <= 0 ? 'All camp fees fully cleared!' : 'Pay anytime via M-Pesa' }}</div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="parent-kpi-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-uppercase fw-bold text-muted" style="letter-spacing: .5px;">Pending Sign-Offs</span>
                    <div class="kpi-icon-pill" style="background: rgba(59, 130, 246, 0.12); color: #60A5FA;">
                        <i class="bi bi-card-checklist"></i>
                    </div>
                </div>
                <div class="fs-3 fw-black {{ $pendingFormReviews->count() > 0 ? 'text-warning' : 'text-white' }}">
                    {{ $pendingFormReviews->count() }}
                </div>
                <div class="small text-muted mt-1">{{ $pendingFormReviews->count() > 0 ? 'Forms awaiting review' : 'All forms up to date' }}</div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         OFFICIAL RECEIPT CONFIRMATION MODAL
    ══════════════════════════════════════════════════════════ --}}
    @if($confirmedReceipt)
        <div class="modal fade" id="receiptConfirmationModal" tabindex="-1" aria-labelledby="receiptConfirmationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger shadow-lg" style="background: #141418; color: #fff;">
                    <div class="modal-header border-secondary border-opacity-25">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success p-2 rounded-circle">
                                <i class="bi bi-check2 fs-5"></i>
                            </span>
                            <div>
                                <h5 class="modal-title fw-bold text-white mb-0" id="receiptConfirmationModalLabel">Payment &amp; Receipt Confirmed!</h5>
                                <small class="text-white-50">Teen Camp Accounting &amp; Treasury</small>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="text-center mb-4">
                            <div class="d-inline-flex p-3 rounded-circle bg-success-subtle text-success mb-2">
                                <i class="bi bi-receipt fs-1"></i>
                            </div>
                            <h3 class="fw-black text-danger mb-1">KES {{ number_format($confirmedReceipt->amount, 2) }}</h3>
                            <p class="text-muted small mb-0">{{ $confirmedReceipt->description }}</p>
                        </div>

                        <div class="panel-well mb-3">
                            <div class="d-flex justify-content-between py-1 border-bottom border-secondary border-opacity-25 small">
                                <span class="text-muted">Receipt Number:</span>
                                <strong class="text-danger">#{{ $confirmedReceipt->receipt_number }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-secondary border-opacity-25 small">
                                <span class="text-muted">M-Pesa Reference:</span>
                                <strong>{{ $confirmedReceipt->meta_data['reference'] ?? ($confirmedReceipt->meta_data['mpesa_code'] ?? 'CONFIRMED') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom border-secondary border-opacity-25 small">
                                <span class="text-muted">Camper Beneficiary:</span>
                                <strong>{{ $confirmedReceipt->meta_data['teen_name'] ?? 'Camper' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 small">
                                <span class="text-muted">Date &amp; Time:</span>
                                <span>{{ $confirmedReceipt->created_at->format('M d, Y • h:i A') }}</span>
                            </div>
                        </div>

                        <div class="alert alert-success d-flex align-items-center gap-2 small py-2 mb-0 border-0" style="background: rgba(34, 197, 94, 0.15); color: #4ADE80;">
                            <i class="bi bi-shield-check fs-5 flex-shrink-0"></i>
                            <div>Payment successfully logged. An official PDF-ready receipt has been appended to your dashboard records.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary border-opacity-25">
                        <a href="{{ route('receipts.show', $confirmedReceipt->receipt_number) }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-printer-fill me-1"></i> Full Printable Receipt
                        </a>
                        <button type="button" class="btn btn-camp-red btn-sm" data-bs-dismiss="modal">Done</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
         PENDING FORM APPROVALS BANNER (ACTION REQUIRED)
    ══════════════════════════════════════════════════════════ --}}
    @if($pendingFormReviews->count() > 0)
        <div class="camp-card p-4 mb-4 border-warning" style="background: rgba(234, 179, 8, 0.04);">
            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark px-2 py-1 fw-bold">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Action Required
                    </span>
                    <h5 class="fw-bold mb-0 text-white">Form Submissions Awaiting Sign-Off ({{ $pendingFormReviews->count() }})</h5>
                </div>
                <span class="small text-muted">Forms completed by your teens that require parental sign-off</span>
            </div>

            <div class="row g-3">
                @foreach($pendingFormReviews as $rev)
                    <div class="col-md-6">
                        <div class="panel-well h-100 d-flex flex-column justify-content-between border-warning border-opacity-50">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="fw-bold text-white mb-0">{{ $rev->form->title }}</h6>
                                        <div class="small text-muted">Camper: <strong class="text-danger">{{ $rev->teen->name }}</strong></div>
                                    </div>
                                    <span class="badge bg-warning text-dark">Pending Sign-Off</span>
                                </div>

                                <div class="p-2 my-2 rounded small" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                    @foreach($rev->values->take(3) as $val)
                                        <div class="text-white-50"><strong class="text-white">{{ $val->field->label }}:</strong> {{ $val->value ?: ($val->file_path ? 'Attached Document' : 'N/A') }}</div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="d-flex gap-2 mt-3 pt-2 border-top border-secondary border-opacity-25">
                                <form action="{{ route('parent.forms.approve', $rev) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Approve &amp; Sign-Off
                                    </button>
                                </form>

                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal-{{ $rev->id }}">
                                    <i class="bi bi-arrow-return-left me-1"></i> Return
                                </button>
                            </div>
                        </div>

                        {{-- Return Feedback Modal --}}
                        <div class="modal fade" id="returnModal-{{ $rev->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content" style="background: #141418; color: #fff; border: 1px solid rgba(255,255,255,.1);">
                                    <form action="{{ route('parent.forms.return', $rev) }}" method="POST">
                                        @csrf
                                        <div class="modal-header border-secondary border-opacity-25">
                                            <h5 class="modal-title fw-bold">Return Form to {{ $rev->teen->name }}</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted">Provide instructions for the adjustments needed before you can sign off.</p>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-white">Corrections Required</label>
                                                <textarea class="form-control" name="parent_feedback" rows="3" required placeholder="e.g. Please update your dietary preferences or emergency contact details."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-secondary border-opacity-25">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-danger btn-sm">Return Form</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
         CHILDREN OVERVIEW CARDS (MODERN & STRUCTURED)
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        @forelse($activeRegistrations as $reg)
            @php
                $childReceipts = $receipts->filter(function($r) use ($reg) {
                    $metaTeen = $r->meta_data['teen_id'] ?? null;
                    $metaReg = $r->meta_data['registration_id'] ?? null;
                    return $metaTeen == $reg->teen_id || $metaReg == $reg->id || ($r->user_id == $reg->teen_id);
                });
                $latestReceipt = $childReceipts->first();
            @endphp
            <div class="col-lg-6">
                <div class="camper-card-modern">
                    <div>
                        {{-- Camper Card Header --}}
                        <div class="d-flex justify-content-between align-items-start border-bottom border-secondary border-opacity-25 pb-3 mb-3 gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="camper-avatar-badge">
                                    {{ strtoupper(substr($reg->teen->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="fw-black mb-1 text-white tracking-tight">{{ $reg->teen->name }}</h4>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-dark border border-secondary text-white-50 px-2 py-1">
                                            <i class="bi bi-gender-{{ strtolower($reg->teen->gender) === 'female' ? 'female text-danger' : 'male text-info' }} me-1"></i>
                                            {{ ucfirst($reg->teen->gender ?: 'Camper') }}
                                        </span>
                                        <span class="badge bg-dark border border-secondary text-white-50 px-2 py-1">
                                            Age {{ $reg->teen->date_of_birth ? $reg->teen->date_of_birth->age : 'N/A' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                @if($reg->status === 'signed_in')
                                    <span class="badge bg-success text-white px-3 py-2 rounded-pill">
                                        <i class="bi bi-patch-check-fill me-1"></i> Checked In
                                    </span>
                                @elseif($reg->status === 'withdrawn')
                                    <span class="badge bg-secondary px-3 py-2 rounded-pill">
                                        <i class="bi bi-slash-circle me-1"></i> Withdrawn
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                                        <i class="bi bi-shield-check me-1"></i> Officially Registered
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Tuition & Fees Panel --}}
                        <div class="panel-well mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="small fw-bold text-uppercase text-muted letter-spacing-1">Camp Fees</span>
                                <span class="fw-black text-white">
                                    KES {{ number_format($reg->total_paid, 2) }} 
                                    <span class="text-white-50 fw-normal">/ KES {{ number_format($season->price, 2) }}</span>
                                </span>
                            </div>

                            <div class="camp-progress my-2" style="height: 8px;">
                                <div class="camp-progress-bar bg-danger" style="width: {{ $reg->payment_percent }}%;"></div>
                            </div>

                            <div class="d-flex justify-content-between small text-muted">
                                <span class="fw-semibold text-white-50">{{ $reg->payment_percent }}% Cleared</span>
                                <span>Balance: <strong class="{{ $reg->balance_remaining > 0 ? 'text-danger' : 'text-success' }} fs-6">KES {{ number_format($reg->balance_remaining, 2) }}</strong></span>
                            </div>

                            @if($reg->balance_remaining <= 0)
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-2 border-top border-secondary border-opacity-15">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="bi bi-patch-check-fill me-1"></i> Camp Fee Fully Paid in KES
                                    </span>
                                    @if($latestReceipt)
                                        <a href="{{ route('receipts.show', $latestReceipt->receipt_number) }}" class="btn btn-outline-danger btn-sm py-1 px-3 d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-receipt-cutoff"></i> Receipt Confirmation
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Primary Actions: Pay for Camp, Receipt, Apply for Aid --}}
                        @if($reg->status !== 'withdrawn')
                            <div class="d-flex gap-2 flex-wrap mb-3">
                                @if($reg->balance_remaining > 0)
                                    <button type="button" class="btn btn-pay-camp-primary btn-sm flex-grow-1 d-inline-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#payModal-{{ $reg->id }}">
                                        <i class="bi bi-credit-card-2-front-fill"></i>
                                        <span>Pay for Camp</span>
                                    </button>

                                    @if($latestReceipt)
                                        <a href="{{ route('receipts.show', $latestReceipt->receipt_number) }}" class="btn btn-outline-secondary btn-sm py-2 px-3 d-inline-flex align-items-center gap-1" title="View latest payment receipt">
                                            <i class="bi bi-receipt-cutoff text-danger"></i>
                                            <span>Receipt (#{{ $latestReceipt->receipt_number }})</span>
                                        </a>
                                    @endif

                                    <button type="button" class="btn btn-camp-outline-red btn-sm py-2 px-3" data-bs-toggle="modal" data-bs-target="#adoptModal-{{ $reg->id }}">
                                        <i class="bi bi-heart me-1"></i> Apply for Adopt-a-Teen
                                    </button>
                                @else
                                    @if($latestReceipt)
                                        <a href="{{ route('receipts.show', $latestReceipt->receipt_number) }}" class="btn btn-outline-danger btn-sm py-2 px-3 flex-grow-1 text-center d-inline-flex align-items-center justify-content-center gap-2">
                                            <i class="bi bi-receipt-cutoff fs-6"></i>
                                            <span>View Official Receipt Confirmation (#{{ $latestReceipt->receipt_number }})</span>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        @endif

                        {{-- Declarations & Health Hub --}}
                        <div class="panel-well mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold small text-uppercase text-muted letter-spacing-1">
                                    <i class="bi bi-shield-plus text-danger me-1"></i> Parent Declarations &amp; Medical
                                </span>
                                <button type="button" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" data-bs-toggle="modal" data-bs-target="#declarationModal-{{ $reg->id }}">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </button>
                            </div>

                            <div class="info-chip-grid">
                                <div class="info-chip">
                                    <div class="text-white-50 small mb-1"><i class="bi bi-phone text-danger me-1"></i> Mobile Phone:</div>
                                    <strong>{{ $reg->phone_carried ? 'Yes (Carrying to camp)' : 'No (No device)' }}</strong>
                                </div>
                                <div class="info-chip">
                                    <div class="text-white-50 small mb-1"><i class="bi bi-capsule text-danger me-1"></i> Medication:</div>
                                    <strong>{{ $reg->medication_notes ?: 'None declared' }}</strong>
                                </div>
                                <div class="info-chip">
                                    <div class="text-white-50 small mb-1"><i class="bi bi-heart-pulse text-danger me-1"></i> Medical/Allergies:</div>
                                    <strong>{{ $reg->medical_conditions ?: 'None reported' }}</strong>
                                </div>
                                <div class="info-chip">
                                    <div class="text-white-50 small mb-1"><i class="bi bi-telephone text-danger me-1"></i> Emergency Contact:</div>
                                    <strong>{{ $reg->emergency_contact_name }} ({{ $reg->emergency_contact_phone }})</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Footer Actions: PIN Reset & Withdraw --}}
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top border-secondary border-opacity-25 mt-2">
                        <form action="{{ route('parent.teen.reset-pin', $reg->teen) }}" method="POST" onsubmit="return confirm('Reset PIN for {{ $reg->teen->name }} back to 0000? They will be required to set a new PIN on next sign in.')">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Reset child PIN to 0000">
                                <i class="bi bi-arrow-counterclockwise text-danger me-1"></i> Reset Child PIN (0000)
                            </button>
                        </form>

                        @if($reg->status !== 'withdrawn')
                            <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#withdrawModal-{{ $reg->id }}">
                                <i class="bi bi-x-circle me-1"></i> Withdraw Teen
                            </button>
                        @endif
                    </div>

                    {{-- ════ M-Pesa Payment Modal ════ --}}
                    <div class="modal fade" id="payModal-{{ $reg->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content" style="background: #141418; color: #fff; border: 1px solid rgba(255,255,255,.1);">
                                <form action="{{ route('parent.pay') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="registration_id" value="{{ $reg->id }}">
                                    <div class="modal-header border-secondary border-opacity-25">
                                        <h5 class="modal-title fw-bold">
                                            <i class="bi bi-credit-card-2-front text-danger me-2"></i> Pay Camp Fee — {{ $reg->teen->name }}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Daraja Paybill Dual Account Indicator -->
                                        <div class="panel-well mb-3" style="border-color: rgba(239, 68, 68, 0.4);">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <span class="fw-bold text-danger"><i class="bi bi-phone-vibrate me-1"></i> Safaricom Daraja STK Push</span>
                                                <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-clock-history me-1"></i> Coming Soon</span>
                                            </div>
                                            <div class="row g-2 small mb-2 text-white">
                                                <div class="col-6">
                                                    <div class="p-2 rounded bg-black border border-secondary">
                                                        <span class="text-white-50 d-block text-uppercase" style="font-size: 10px;">Paybill Number</span>
                                                        <strong class="fs-6">{{ $mpesaSettings->paybill_number ?? '880100' }}</strong>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="p-2 rounded bg-black border border-secondary">
                                                        <span class="text-white-50 d-block text-uppercase" style="font-size: 10px;">Camp Fees Account</span>
                                                        <strong class="text-danger fs-6">{{ $mpesaSettings->camp_fee_account ?? 'CAMP-FEES' }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="small text-white-50" style="line-height: 1.3;">
                                                <i class="bi bi-info-circle text-warning me-1"></i>
                                                Automated phone prompts are <strong>Coming Soon</strong>. In the meantime, please send payment via M-Pesa to Paybill <strong>{{ $mpesaSettings->paybill_number ?? '880100' }}</strong> (Account: <strong>{{ $mpesaSettings->camp_fee_account ?? 'CAMP-FEES' }}</strong>) and enter your confirmation code below.
                                            </div>
                                        </div>

                                        <p class="small text-white-50 mb-3">Balance Remaining: <strong class="text-danger">KES {{ number_format($reg->balance_remaining, 2) }}</strong></p>
                                        
                                        <!-- Instant STK Push Box (Marked Coming Soon) -->
                                        <div class="p-3 mb-3 rounded bg-dark border border-secondary" style="border-style: dashed !important; background: rgba(255,255,255,0.03) !important;">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <div class="fw-semibold text-white small"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Instant STK Push to Phone</div>
                                                <span class="badge bg-warning text-dark fw-bold" style="font-size: 10px;">COMING SOON</span>
                                            </div>
                                            <div class="input-group mb-2">
                                                <span class="input-group-text bg-body-tertiary border-secondary text-secondary"><i class="bi bi-phone"></i></span>
                                                <input type="text" class="form-control text-white border-secondary bg-dark opacity-75" id="stkPhone-{{ $reg->id }}" value="{{ Auth::user()->phone }}" placeholder="07XXXXXXXX" disabled>
                                                <button type="button" class="btn btn-secondary fw-bold" disabled>
                                                    <i class="bi bi-clock-history me-1"></i> Coming Soon
                                                </button>
                                            </div>
                                            <div class="small text-warning" id="stkStatusMsg-{{ $reg->id }}">
                                                <i class="bi bi-info-circle me-1"></i> Automated phone prompts are currently in integration (Coming Soon). Please make payment to Paybill above and enter code below.
                                            </div>
                                        </div>

                                        <div class="text-center my-2 text-muted small">— OR CONFIRM EXISTING M-PESA CODE —</div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">Payment Amount (KES)</label>
                                            <input type="number" step="1" max="{{ $reg->balance_remaining }}" class="form-control" id="formAmount-{{ $reg->id }}" name="amount" value="{{ $reg->balance_remaining }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">Payment Method</label>
                                            <select class="form-select" name="payment_method" required>
                                                <option value="M-Pesa (Paybill {{ $mpesaSettings->paybill_number ?? '880100' }})" selected>M-Pesa Paybill ({{ $mpesaSettings->paybill_number ?? '880100' }})</option>
                                                <option value="Cash at Church Desk">Cash at Church Desk</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">M-Pesa Phone Number</label>
                                            <input type="text" class="form-control" name="mpesa_phone" value="{{ Auth::user()->phone }}" placeholder="e.g. 0712345678">
                                            <div class="form-text small text-muted">Phone used for M-Pesa transaction</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">M-Pesa Transaction Code</label>
                                            <input type="text" class="form-control text-uppercase" name="mpesa_code" placeholder="e.g. QA94XD8712" required maxlength="20">
                                            <div class="form-text small text-muted">10-character alphanumeric Safaricom confirmation code</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-secondary border-opacity-25">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-pay-camp-primary btn-sm py-2 px-3">
                                            <i class="bi bi-patch-check-fill me-1"></i> Submit Payment &amp; Issue Receipt
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ════ Adopt-a-Teen Aid Modal ════ --}}
                    <div class="modal fade" id="adoptModal-{{ $reg->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content" style="background: #141418; color: #fff; border: 1px solid rgba(255,255,255,.1);">
                                <form action="{{ route('parent.adopt.apply') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="teen_id" value="{{ $reg->teen->id }}">
                                    <div class="modal-header border-secondary border-opacity-25">
                                        <h5 class="modal-title fw-bold">Apply for Adopt-a-Teen Sponsorship</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="small text-muted mb-3">
                                            The Adopt-a-Teen program is supported by church donations. Max request: <strong>KES {{ number_format($reg->balance_remaining, 2) }}</strong>.
                                        </p>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">Amount Requested (KES)</label>
                                            <input type="number" step="1" max="{{ $reg->balance_remaining }}" class="form-control" name="amount_requested" value="{{ $reg->balance_remaining }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">Reason for Assistance</label>
                                            <textarea class="form-control" name="reason" rows="3" required placeholder="Briefly describe your financial circumstance for confidential pastoral review."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-secondary border-opacity-25">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-camp-red btn-sm">Submit Application</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ════ Declarations Modal ════ --}}
                    <div class="modal fade" id="declarationModal-{{ $reg->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content" style="background: #141418; color: #fff; border: 1px solid rgba(255,255,255,.1);">
                                <form action="{{ route('parent.declarations.update', $reg) }}" method="POST">
                                    @csrf
                                    <div class="modal-header border-secondary border-opacity-25">
                                        <h5 class="modal-title fw-bold">Update Declarations for {{ $reg->teen->name }}</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">Phone Policy</label>
                                            <select class="form-select" name="phone_carried" required>
                                                <option value="0" {{ !$reg->phone_carried ? 'selected' : '' }}>No Phone (Teen will not bring a phone)</option>
                                                <option value="1" {{ $reg->phone_carried ? 'selected' : '' }}>Yes (Teen will carry a mobile phone)</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">Medication Instructions</label>
                                            <textarea class="form-control" name="medication_notes" rows="2" placeholder="List medications, dosage, and times...">{{ $reg->medication_notes }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">Medical Conditions &amp; Allergies</label>
                                            <textarea class="form-control" name="medical_conditions" rows="2" placeholder="Dietary restrictions, allergies, asthma...">{{ $reg->medical_conditions }}</textarea>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold small text-white">Emergency Contact Name</label>
                                                <input type="text" class="form-control" name="emergency_contact_name" value="{{ $reg->emergency_contact_name }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold small text-white">Emergency Contact Phone</label>
                                                <input type="text" class="form-control" name="emergency_contact_phone" value="{{ $reg->emergency_contact_phone }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-secondary border-opacity-25">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-camp-red btn-sm">Save Declarations</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- ════ Withdraw Modal ════ --}}
                    <div class="modal fade" id="withdrawModal-{{ $reg->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content" style="background: #141418; color: #fff; border: 1px solid rgba(255,255,255,.1);">
                                <form action="{{ route('parent.withdraw', $reg) }}" method="POST">
                                    @csrf
                                    <div class="modal-header border-secondary border-opacity-25">
                                        <h5 class="modal-title fw-bold text-danger">Withdraw {{ $reg->teen->name }} from Camp</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <p class="small text-muted">
                                            Are you sure you wish to withdraw <strong>{{ $reg->teen->name }}</strong>? This releases their spot back to the camp capacity.
                                        </p>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-white">Reason for Withdrawal</label>
                                            <textarea class="form-control" name="reason" rows="2" required placeholder="e.g. Family emergency, travel conflict..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer border-secondary border-opacity-25">
                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-danger btn-sm">Confirm Withdrawal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="p-5 text-center text-muted border rounded camp-card">
                    <i class="bi bi-people text-danger fs-1 mb-2 d-block"></i>
                    <h5>No Camp Registrations Found</h5>
                    <p class="small mb-3">You don't have any campers registered for the active season yet.</p>
                    <a href="{{ route('public.register') }}" class="btn btn-camp-red btn-sm">
                        <i class="bi bi-stopwatch-fill me-1"></i> Register Your Teen in 2 Minutes
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- ══════════════════════════════════════════════════════════
         PAYMENT TRANSACTIONS & OFFICIAL RECEIPTS TABLE
    ══════════════════════════════════════════════════════════ --}}
    <div class="camp-card p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-secondary border-opacity-25 pb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-white">
                    <i class="bi bi-receipt-cutoff text-danger me-2"></i> Payment Transactions &amp; Official Receipts
                </h5>
                <p class="text-muted small mb-0">Verified camp payment ledger with M-Pesa references and downloadable receipts.</p>
            </div>
            @php
                $totalFamilyPaid = $payments->sum('amount');
            @endphp
            <div class="text-end">
                <span class="text-muted small text-uppercase fw-bold d-block" style="font-size: 11px;">Total Family Payments:</span>
                <span class="fs-5 fw-black text-danger">KES {{ number_format($totalFamilyPaid, 2) }}</span>
            </div>
        </div>

        @if($payments->count() > 0)
            <div class="table-responsive">
                <table class="table table-camp align-middle">
                    <thead>
                        <tr>
                            <th>Receipt #</th>
                            <th>Camper Beneficiary</th>
                            <th>Amount (KES)</th>
                            <th>Method &amp; Reference</th>
                            <th>Date &amp; Time</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('receipts.show', $p->receipt_number) }}" class="badge bg-dark border border-danger text-danger text-decoration-none py-2 px-3 fw-bold" style="font-size: 12px;">
                                        <i class="bi bi-receipt me-1"></i> #{{ $p->receipt_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle bg-danger-subtle text-danger fw-bold d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 11px;">
                                            {{ strtoupper(substr($p->registration?->teen?->name ?? 'C', 0, 1)) }}
                                        </span>
                                        <span class="fw-bold text-white">{{ $p->registration?->teen?->name ?? 'Registered Camper' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-danger fs-6">KES {{ number_format($p->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <div class="small">
                                        <span class="fw-semibold text-white">{{ $p->payment_method }}</span>
                                        @if($p->reference)
                                            <span class="badge bg-secondary-subtle text-white-50 ms-1" style="font-size: 10px;">Ref: {{ $p->reference }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="small text-muted">{{ $p->created_at->format('M d, Y • h:i A') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2">
                                        <i class="bi bi-patch-check-fill me-1"></i> Confirmed
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('receipts.show', $p->receipt_number) }}" class="btn btn-outline-danger btn-sm py-1 px-3 d-inline-flex align-items-center gap-1">
                                        <i class="bi bi-printer-fill"></i> View Receipt
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-4 text-center text-muted border rounded panel-well">
                <i class="bi bi-credit-card text-danger fs-3 d-block mb-2"></i>
                <h6 class="fw-bold mb-1 text-white">No Camp Fee Payments Recorded Yet</h6>
                <p class="small text-muted mb-0">Payments made via M-Pesa or at the church desk will automatically generate downloadable receipts here.</p>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════
         ADOPT-A-TEEN STATUS TRACKER (IF ANY)
    ══════════════════════════════════════════════════════════ --}}
    @if($adoptRequests->count() > 0)
        <div class="camp-card p-4 mb-4">
            <h5 class="fw-bold mb-3 text-white"><i class="bi bi-heart-pulse text-danger me-2"></i> Adopt-a-Teen Financial Aid History</h5>
            <div class="table-responsive">
                <table class="table table-camp align-middle">
                    <thead>
                        <tr>
                            <th>Child</th>
                            <th>Amount Requested</th>
                            <th>Status</th>
                            <th>Date Applied</th>
                            <th>Decision Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adoptRequests as $req)
                            <tr>
                                <td class="fw-bold text-white">{{ $req->teen->name }}</td>
                                <td>KES {{ number_format($req->amount_requested, 2) }}</td>
                                <td>
                                    @if($req->status === 'approved')
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Approved</span>
                                    @elseif($req->status === 'approved-awaiting-funds')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Approved — Awaiting Funds</span>
                                    @elseif($req->status === 'denied')
                                        <span class="badge bg-danger"><i class="bi bi-x-circle-fill me-1"></i> Denied</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-clock me-1"></i> Pending Review</span>
                                    @endif
                                </td>
                                <td>{{ $req->created_at->format('M d, Y') }}</td>
                                <td class="small text-muted">{{ $req->decision_notes ?: 'Under confidential pastoral review' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
         CAMP PACKING LIST FOR PARENTS
    ══════════════════════════════════════════════════════════ --}}
    <div class="camp-card p-4">
        <div class="d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25 pb-3 mb-3">
            <div>
                <h5 class="fw-bold mb-1 text-white"><i class="bi bi-backpack text-danger me-2"></i> Camp Packing Checklist</h5>
                <p class="text-muted small mb-0">Official packing guidelines for your campers.</p>
            </div>
            @if($packingList->count() > 0)
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('packing-list.pdf') }}" target="_blank" class="btn btn-outline-danger btn-sm shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-printer-fill"></i>
                        <span>Download & Print PDF</span>
                    </a>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Released</span>
                </div>
            @else
                <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i> Release Pending</span>
            @endif
        </div>

        @if($packingList->count() > 0)
            <div class="row g-3">
                @foreach($packingList as $category => $items)
                    <div class="col-md-6 col-lg-4">
                        <div class="panel-well h-100">
                            <h6 class="fw-bold text-uppercase border-bottom border-secondary border-opacity-25 pb-2 text-danger">
                                <i class="bi bi-check2-square me-1"></i> {{ $category }}
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($items as $item)
                                    <li class="py-1 d-flex align-items-start gap-2">
                                        <i class="bi bi-circle text-danger mt-1" style="font-size: 7px;"></i>
                                        <div>
                                            <span class="fw-semibold text-white">{{ $item->item_name }}</span>
                                            @if($item->is_essential)
                                                <span class="badge bg-danger ms-1" style="font-size: 9px;">Essential</span>
                                            @endif
                                            @if($item->notes)
                                                <div class="text-muted" style="font-size: 11px;">{{ $item->notes }}</div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-4 text-center text-muted border rounded panel-well">
                <p class="small mb-0">The packing list for this season will be released closer to the camp date.</p>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════
         REGISTER NEW TEEN MODAL (PARENT PORTAL DIRECT INTAKE)
    ══════════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="registerNewTeenModal" tabindex="-1" aria-labelledby="registerNewTeenModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-secondary shadow-lg bg-dark text-white">
                <div class="modal-header border-secondary">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded p-2 bg-danger-subtle text-danger">
                            <i class="bi bi-person-plus-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0" id="registerNewTeenModalLabel">Register New Teen Camper</h5>
                            <span class="small text-muted">Direct intake for {{ $season ? $season->name : 'Active Season' }} &bull; KES {{ number_format($season?->price ?? 0, 0) }}</span>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('parent.register-teen') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-dark small mb-3 border border-secondary">
                            <i class="bi bi-info-circle text-danger me-1"></i>
                            This camper will be linked directly to your parent account. A teen portal account will be automatically generated with initial PIN <code>0000</code>.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Teen's Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-black text-white border-secondary" name="teen_name" required placeholder="e.g. Grace Wanjiku Kiprono">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Gender <span class="text-danger">*</span></label>
                                <select class="form-select bg-black text-white border-secondary" name="teen_gender" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Date of Birth</label>
                                <input type="date" class="form-control bg-black text-white border-secondary" name="teen_dob">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Relationship to Teen</label>
                                <select class="form-select bg-black text-white border-secondary" name="relationship">
                                    <option value="Mother">Mother</option>
                                    <option value="Father">Father</option>
                                    <option value="Guardian">Guardian</option>
                                    <option value="Aunt">Aunt</option>
                                    <option value="Uncle">Uncle</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Teen Email (Optional)</label>
                                <input type="email" class="form-control bg-black text-white border-secondary" name="teen_email" placeholder="Leave blank to auto-generate">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Teen Phone (Optional)</label>
                                <input type="text" class="form-control bg-black text-white border-secondary" name="teen_phone" placeholder="e.g. +254 712 345 678">
                            </div>

                            <div class="col-12">
                                <div class="form-check p-3 rounded border border-secondary" style="background: rgba(255,255,255,.03);">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="phone_carried" value="1" id="parentRegPhone" style="width:20px;height:20px;accent-color:#D32F2F;">
                                    <label class="form-check-label fw-semibold text-white small" for="parentRegPhone">
                                        Teen is carrying a smartphone/phone to camp (Subject to church digital wellness policy)
                                    </label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Medical Alerts / Allergies</label>
                                <textarea class="form-control bg-black text-white border-secondary" name="medical_conditions" rows="2" placeholder="e.g. Asthma, peanut allergy, lactose intolerant (or None)"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Medication Notes</label>
                                <textarea class="form-control bg-black text-white border-secondary" name="medication_notes" rows="2" placeholder="e.g. Carries Ventolin inhaler in backpack (or None)"></textarea>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">Initial Payment Amount (KES - Optional)</label>
                                <input type="number" step="1" min="0" class="form-control bg-black text-white border-secondary" name="initial_payment" placeholder="e.g. 5000 (Pay now or later)">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted">M-Pesa Reference / Confirmation Code</label>
                                <input type="text" class="form-control bg-black text-white border-secondary text-uppercase" name="payment_reference" placeholder="e.g. RJG82K9102">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-camp-red btn-sm px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Register Camper &amp; Sync Portal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function triggerParentStkPush(regId, maxBalance) {
    const phoneInput = document.getElementById('stkPhone-' + regId);
    const amountInput = document.getElementById('formAmount-' + regId);
    const statusMsg = document.getElementById('stkStatusMsg-' + regId);
    const btn = document.getElementById('btnStkTrigger-' + regId);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const phone = phoneInput ? phoneInput.value.trim() : '';
    const amount = amountInput ? parseFloat(amountInput.value) : maxBalance;

    if (!phone) {
        alert('Please enter a valid Safaricom phone number (e.g. 0712345678).');
        return;
    }
    if (!amount || amount <= 0) {
        alert('Please enter a valid payment amount.');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Prompting...';
    statusMsg.className = 'small text-warning mt-1';
    statusMsg.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Sending STK prompt to ' + phone + '... Check your phone screen.';

    fetch("{{ route('mpesa.stk-push') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            phone: phone,
            amount: amount,
            account_type: 'camp_fee',
            registration_id: regId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            statusMsg.className = 'small text-success mt-1 fw-bold';
            statusMsg.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + (data.CustomerMessage || 'Payment processed successfully!');
            setTimeout(() => {
                window.location.reload();
            }, 1800);
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Retry Prompt';
            statusMsg.className = 'small text-danger mt-1';
            statusMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> ' + (data.message || 'STK Push failed. Please enter M-Pesa code below.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send-fill me-1"></i> Prompt Phone';
        statusMsg.className = 'small text-danger mt-1';
        statusMsg.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> Connection error. Please check network or enter code below.';
    });
}
</script>
@endpush

@if($confirmedReceipt)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalEl = document.getElementById('receiptConfirmationModal');
            if (modalEl) {
                var receiptModal = new bootstrap.Modal(modalEl);
                receiptModal.show();
            }
        });
    </script>
    @endpush
@endif
@endsection
