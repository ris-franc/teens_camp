@extends('layouts.backoffice')

@section('title', 'Adopt-a-Teen Portal')

@section('content')
<div class="container-fluid px-0">
    <!-- Header with Quick Action Bar -->
    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h3 class="fw-black text-uppercase mb-0 tracking-wide" style="font-size: 1.5rem;">
                    <i class="bi bi-heart-pulse-fill text-danger me-2"></i> Adopt-a-Teen Portal
                </h3>
                @if($season)
                    <span class="badge bg-danger px-2 py-1 text-uppercase">{{ $season->name }}</span>
                    <span class="badge bg-dark border border-secondary text-white-50">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $season->venue }}
                    </span>
                @endif
            </div>
            <p class="text-muted small mb-0">
                Central command for camper financial aid sponsorship, church Kitty funds, and donor allocations.
            </p>
        </div>

        <!-- Portal Actions Toolbar (Responsive on Mobile) -->
        <div class="d-grid d-sm-flex flex-wrap gap-2 align-items-center w-100 w-xl-auto" style="grid-template-columns: repeat(2, 1fr);">
            <!-- M-Pesa Paybill Quick Copy Pill -->
            @php $mpSettings = \App\Models\MpesaSetting::getSettings(); @endphp
            <div class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center gap-2 py-2" style="grid-column: span 2;">
                <i class="bi bi-phone-vibrate-fill"></i>
                <span>Paybill: <strong>{{ $mpSettings->paybill_number }}</strong></span>
                <span class="badge bg-danger text-white">Acc: {{ $mpSettings->adopt_account }}</span>
            </div>

            <!-- Record Kitty Donation Modal Trigger -->
            <button type="button" class="btn btn-camp-red btn-sm d-flex align-items-center justify-content-center gap-1 py-2" data-bs-toggle="modal" data-bs-target="#adoptDonationModal">
                <i class="bi bi-plus-circle me-1"></i>
                <span>Add Donation</span>
            </button>

            <!-- Matchmaker Quick Link -->
            <button type="button" class="btn btn-camp-dark btn-sm d-flex align-items-center justify-content-center gap-1 py-2 border border-secondary border-opacity-25" onclick="switchAdoptTab('adopt-tab-matchmaker')">
                <i class="bi bi-people-fill me-1"></i>
                <span>Sponsor Camper</span>
            </button>
        </div>
    </div>

    @if($season)
        <!-- 4 TOP FINANCIAL & APPLICATION METRICS (Responsive 2x2 on mobile) -->
        <div class="row g-2 g-md-3 mb-4">
            <!-- 1. Available Kitty Pool -->
            <div class="col-6 col-xl-3">
                <div class="camp-metric-card accent-red h-100 p-3" onclick="switchAdoptTab('adopt-tab-ledger')" style="cursor: pointer;" title="Click to view Kitty Audit Ledger">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="overflow-hidden">
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1" style="font-size: 10px;">Active Kitty Pool</span>
                            <div class="fs-5 fs-md-4 fw-black lh-1 mt-1 text-danger text-break">
                                KES {{ number_format($metrics['kittyBalance'], 0) }}
                            </div>
                        </div>
                        <div class="metric-icon-bubble d-none d-sm-flex" style="background: rgba(220, 38, 38, 0.15); color: #EF4444; border: 1px solid rgba(220, 38, 38, 0.3); width: 34px; height: 34px; font-size: 1rem;">
                            <i class="bi bi-heart-pulse-fill"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-2 flex-wrap gap-1" style="font-size: 10px;">
                        <span class="text-success"><i class="bi bi-arrow-down-left"></i> +{{ number_format($metrics['totalKittyIn'], 0) }}</span>
                        <span class="text-danger"><i class="bi bi-arrow-up-right"></i> -{{ number_format($metrics['totalKittyOut'], 0) }}</span>
                    </div>

                    <div class="mini-meter-track mt-2">
                        @php
                            $kittyFlowTotal = max(1, $metrics['totalKittyIn'] + $metrics['totalKittyOut']);
                            $kittyNetPct = min(100, round(($metrics['kittyBalance'] / max(1, $metrics['totalKittyIn'])) * 100));
                        @endphp
                        <div class="mini-meter-fill bg-danger" style="width: {{ $kittyNetPct }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 small text-muted border-top border-secondary border-opacity-15" style="font-size: 10px;">
                        <span class="badge bg-danger-subtle text-danger px-1 py-0">Available</span>
                        <span class="text-danger fw-semibold d-none d-sm-inline">View Ledger &rarr;</span>
                    </div>
                </div>
            </div>

            <!-- 2. Aid Disbursed -->
            <div class="col-6 col-xl-3">
                <div class="camp-metric-card accent-emerald h-100 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="overflow-hidden">
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1" style="font-size: 10px;">Aid Granted</span>
                            <div class="fs-5 fs-md-4 fw-black lh-1 mt-1 text-success text-break">
                                KES {{ number_format($metrics['totalKittyOut'], 0) }}
                            </div>
                        </div>
                        <div class="metric-icon-bubble d-none d-sm-flex" style="background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); width: 34px; height: 34px; font-size: 1rem;">
                            <i class="bi bi-patch-check-fill"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-2" style="font-size: 10px;">
                        <span>{{ $metrics['approvedCount'] }} Teens</span>
                        <span class="fw-bold text-success">100% Disbursed</span>
                    </div>

                    <div class="mini-meter-track mt-2">
                        <div class="mini-meter-fill" style="width: 100%; background: linear-gradient(90deg, #059669 0%, #10B981 100%);"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 small text-muted border-top border-secondary border-opacity-15" style="font-size: 10px;">
                        <span class="badge bg-success-subtle text-success px-1 py-0">Mission</span>
                        <span class="text-success fw-semibold d-none d-sm-inline">Audited</span>
                    </div>
                </div>
            </div>

            <!-- 3. Pending Application Need -->
            <div class="col-6 col-xl-3">
                <div class="camp-metric-card accent-crimson h-100 p-3" onclick="switchAdoptTab('adopt-tab-queue')" style="cursor: pointer;" title="Click to view Applications Queue">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="overflow-hidden">
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1" style="font-size: 10px;">Pending Need</span>
                            <div class="fs-5 fs-md-4 fw-black lh-1 mt-1 text-white text-break">
                                KES {{ number_format($metrics['pendingAmount'], 0) }}
                            </div>
                        </div>
                        <div class="metric-icon-bubble d-none d-sm-flex" style="background: rgba(220, 38, 38, 0.12); color: #EF4444; border: 1px solid rgba(220, 38, 38, 0.25); width: 34px; height: 34px; font-size: 1rem;">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-2" style="font-size: 10px;">
                        <span>{{ $metrics['pendingCount'] }} In Queue</span>
                        @php
                            $poolCoverage = $metrics['pendingAmount'] > 0 ? min(100, round(($metrics['kittyBalance'] / $metrics['pendingAmount']) * 100)) : 100;
                        @endphp
                        <span class="fw-bold text-danger">{{ $poolCoverage }}% Covered</span>
                    </div>

                    <div class="mini-meter-track mt-2">
                        <div class="mini-meter-fill bg-danger" style="width: {{ $poolCoverage }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 small text-muted border-top border-secondary border-opacity-15" style="font-size: 10px;">
                        <span class="badge bg-dark border border-secondary text-white-50 px-1 py-0">Action</span>
                        <span class="text-danger fw-semibold d-none d-sm-inline">Review &rarr;</span>
                    </div>
                </div>
            </div>

            <!-- 4. Revenue Streams Into Kitty -->
            <div class="col-6 col-xl-3">
                <div class="camp-metric-card accent-dark h-100 p-3" onclick="switchAdoptTab('adopt-tab-ledger')" style="cursor: pointer;" title="Click to view Audit Ledger">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="overflow-hidden">
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1" style="font-size: 10px;">Inflows Count</span>
                            <div class="fs-5 fs-md-4 fw-black lh-1 mt-1 text-white text-break">
                                {{ $metrics['donationsCount'] + $metrics['campaignProfitsCount'] }} Inflows
                            </div>
                        </div>
                        <div class="metric-icon-bubble d-none d-sm-flex" style="background: rgba(255, 255, 255, 0.08); color: #EF4444; border: 1px solid rgba(255, 255, 255, 0.12); width: 34px; height: 34px; font-size: 1rem;">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-2 flex-wrap gap-1" style="font-size: 10px;">
                        <span><i class="bi bi-gift-fill text-danger"></i> {{ $metrics['donationsCount'] }} Don</span>
                        <span><i class="bi bi-shop text-white-50"></i> {{ $metrics['campaignProfitsCount'] }} Batches</span>
                    </div>

                    <div class="mini-meter-track mt-2">
                        <div class="mini-meter-fill" style="width: 100%; background: linear-gradient(90deg, #DC2626 0%, #991B1B 100%);"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 small text-muted border-top border-secondary border-opacity-15" style="font-size: 10px;">
                        <span class="badge bg-danger-subtle text-danger px-1 py-0">Paybill</span>
                        <span class="text-white-50 fw-semibold d-none d-sm-inline">Audit &rarr;</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PORTAL INTERACTIVE TABS (Horizontally scrollable swipe strip on mobile) -->
        <div class="camp-tabs-nav camp-chip-scroll flex-nowrap pb-2 mb-3">
            <button type="button" class="camp-tab-link active text-nowrap" id="tabbtn-adopt-queue" onclick="switchAdoptTab('adopt-tab-queue')">
                <i class="bi bi-inbox-fill"></i> Aid Applications Queue
                @if($metrics['pendingCount'] > 0)
                    <span class="badge bg-danger rounded-pill ms-1">{{ $metrics['pendingCount'] }}</span>
                @else
                    <span class="badge bg-secondary rounded-pill ms-1">0</span>
                @endif
            </button>

            <button type="button" class="camp-tab-link text-nowrap" id="tabbtn-adopt-matchmaker" onclick="switchAdoptTab('adopt-tab-matchmaker')">
                <i class="bi bi-person-heart"></i> Sponsor a Camper (Matchmaker)
                <span class="badge bg-dark border rounded-pill ms-1">{{ $unsponsoredCampers->count() }}</span>
            </button>

            <button type="button" class="camp-tab-link text-nowrap" id="tabbtn-adopt-ledger" onclick="switchAdoptTab('adopt-tab-ledger')">
                <i class="bi bi-journal-text"></i> Kitty Financial Audit Ledger
            </button>
        </div>

        <!-- TAB CONTENT PANELS -->
        <div class="tab-content mb-4">
            
            <!-- PANEL 1: AID APPLICATIONS QUEUE -->
            <div id="adopt-tab-queue" class="adopt-tab-pane">
                <div class="camp-card p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
                        <div>
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-clipboard-check text-danger me-2"></i> Sponsorship Applications & Decisions
                            </h5>
                            <p class="text-muted small mb-0">Review parental aid statements, approve full or partial grants, or place awaiting funds.</p>
                        </div>

                        <!-- Status Filter Form -->
                        <form action="{{ route('backoffice.adopt.index') }}" method="GET" class="d-flex flex-wrap gap-2 align-items-center m-0">
                            <input type="hidden" name="tab" value="adopt-tab-queue">
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <span class="input-group-text bg-body border-secondary"><i class="bi bi-search text-danger"></i></span>
                                <input type="text" class="form-control border-secondary" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search camper or parent...">
                            </div>

                            <select class="form-select form-select-sm border-secondary" name="status" onchange="this.form.submit()" style="width: 140px;">
                                <option value="all" {{ ($filters['status'] ?? '') === 'all' ? 'selected' : '' }}>All Statuses</option>
                                <option value="pending" {{ ($filters['status'] ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending ({{ $metrics['pendingCount'] }})</option>
                                <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="approved-awaiting-funds" {{ ($filters['status'] ?? '') === 'approved-awaiting-funds' ? 'selected' : '' }}>Awaiting Funds</option>
                                <option value="denied" {{ ($filters['status'] ?? '') === 'denied' ? 'selected' : '' }}>Denied</option>
                            </select>

                            @if(!empty($filters['search']) || (isset($filters['status']) && $filters['status'] !== 'pending'))
                                <a href="{{ route('backoffice.adopt.index') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                            @endif
                        </form>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-camp align-middle">
                            <thead>
                                <tr>
                                    <th>Camper & Parent</th>
                                    <th>Hardship / Need Statement</th>
                                    <th>Amount Requested</th>
                                    <th>Status</th>
                                    <th>Reviewer & Notes</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requestsList as $req)
                                    <tr class="stream-row">
                                        <td>
                                            <div class="fw-bold fs-6">{{ $req->teen ? $req->teen->name : 'Unknown Camper' }}</div>
                                            <small class="text-muted d-block">
                                                Parent: <strong>{{ $req->parent ? $req->parent->name : 'Unknown Parent' }}</strong>
                                            </small>
                                            <small class="text-muted">{{ $req->parent?->phone ?: $req->parent?->email }}</small>
                                        </td>
                                        <td style="max-width: 320px;">
                                            <div class="small p-2 rounded bg-dark border border-secondary border-opacity-50 text-white-50" style="line-height: 1.35;">
                                                "{{ $req->reason }}"
                                            </div>
                                            <small class="text-muted d-block mt-1">Submitted {{ $req->created_at ? $req->created_at->diffForHumans() : '' }}</small>
                                        </td>
                                        <td>
                                            <div class="fs-5 fw-bold text-danger">KES {{ number_format($req->amount_requested, 2) }}</div>
                                            <small class="text-muted">Camp Fee: KES {{ number_format($season->price, 2) }}</small>
                                        </td>
                                        <td>
                                            @if($req->status === 'approved')
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Approved & Disbursed</span>
                                            @elseif($req->status === 'approved-awaiting-funds')
                                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Awaiting Kitty Funds</span>
                                            @elseif($req->status === 'denied')
                                                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Denied</span>
                                            @else
                                                <span class="badge bg-danger"><i class="bi bi-clock me-1"></i> Pending Review</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            @if($req->reviewer)
                                                <div class="fw-semibold text-white">{{ $req->reviewer->name }}</div>
                                                <small class="text-muted">{{ $req->reviewed_at ? $req->reviewed_at->format('M d, Y H:i') : '' }}</small>
                                                @if($req->decision_notes)
                                                    <div class="text-white-50 fst-italic">"{{ $req->decision_notes }}"</div>
                                                @endif
                                            @else
                                                <span class="text-muted">Awaiting decision</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            @if($req->status === 'pending' || $req->status === 'approved-awaiting-funds')
                                                <button type="button" class="btn btn-camp-red btn-sm text-nowrap" 
                                                        onclick="openReviewModal({{ $req->id }}, '{{ addslashes($req->teen?->name ?? 'Camper') }}', {{ $req->amount_requested }}, '{{ addslashes($req->reason) }}')">
                                                    <i class="bi bi-shield-check me-1"></i> Review Decision
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                        onclick="openReviewModal({{ $req->id }}, '{{ addslashes($req->teen?->name ?? 'Camper') }}', {{ $req->amount_requested }}, '{{ addslashes($req->reason) }}')">
                                                    <i class="bi bi-eye me-1"></i> View Details
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-2 text-muted opacity-50"></i>
                                            No sponsorship applications match the selected criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card Feed View -->
                    <div class="d-md-none d-flex flex-column gap-3">
                        @forelse($requestsList as $req)
                            <div class="camp-card p-3 border border-secondary border-opacity-25 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold text-white fs-6">{{ $req->teen ? $req->teen->name : 'Unknown Camper' }}</div>
                                        <div class="small text-muted">Parent: <span class="text-white-50">{{ $req->parent ? $req->parent->name : 'Unknown' }}</span></div>
                                    </div>
                                    <div>
                                        @if($req->status === 'approved')
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Approved</span>
                                        @elseif($req->status === 'approved-awaiting-funds')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Awaiting Funds</span>
                                        @elseif($req->status === 'denied')
                                            <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Denied</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-clock me-1"></i> Pending</span>
                                        @endif
                                    </div>
                                </div>

                                @if($req->parent?->phone)
                                    <div class="mb-2 small">
                                        <a href="tel:{{ $req->parent->phone }}" class="text-decoration-none text-white-50 d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-telephone-fill text-danger small"></i> {{ $req->parent->phone }}
                                        </a>
                                    </div>
                                @endif

                                <div class="row g-2 py-2 my-2 border-top border-bottom border-secondary border-opacity-25 bg-black bg-opacity-25 rounded px-2">
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: 10px;">REQUESTED AID</span>
                                        <span class="fw-bold text-danger fs-6">KES {{ number_format($req->amount_requested, 0) }}</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="text-muted d-block" style="font-size: 10px;">CAMP FEE</span>
                                        <span class="text-white-50 small">KES {{ number_format($season->price, 0) }}</span>
                                    </div>
                                </div>

                                <div class="p-2 rounded bg-dark border border-secondary border-opacity-50 text-white-50 small mb-2" style="font-size: 11px; line-height: 1.4;">
                                    <strong class="text-white d-block mb-1"><i class="bi bi-chat-quote-fill text-danger me-1"></i> Reason / Hardship:</strong>
                                    "{{ $req->reason }}"
                                </div>

                                @if($req->reviewer)
                                    <div class="small text-muted mb-2" style="font-size: 11px;">
                                        <i class="bi bi-person-check text-success me-1"></i> Reviewed by {{ $req->reviewer->name }} 
                                        @if($req->reviewed_at) &bull; {{ $req->reviewed_at->format('M d, Y') }} @endif
                                        @if($req->decision_notes)
                                            <div class="text-white-50 fst-italic mt-1">"{{ $req->decision_notes }}"</div>
                                        @endif
                                    </div>
                                @endif

                                <div class="pt-2 border-top border-secondary border-opacity-25">
                                    @if($req->status === 'pending' || $req->status === 'approved-awaiting-funds')
                                        <button type="button" class="btn btn-camp-red btn-sm w-100 py-2 fw-semibold" 
                                                onclick="openReviewModal({{ $req->id }}, '{{ addslashes($req->teen?->name ?? 'Camper') }}', {{ $req->amount_requested }}, '{{ addslashes($req->reason) }}')"
                                                style="min-height: 44px;">
                                            <i class="bi bi-shield-check me-1"></i> Review Decision
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-2" 
                                                onclick="openReviewModal({{ $req->id }}, '{{ addslashes($req->teen?->name ?? 'Camper') }}', {{ $req->amount_requested }}, '{{ addslashes($req->reason) }}')"
                                                style="min-height: 44px;">
                                            <i class="bi bi-eye me-1"></i> View Details
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted camp-card">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-muted opacity-50"></i>
                                No sponsorship applications match the criteria.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-3">
                        {{ $requestsList->links() }}
                    </div>
                </div>
            </div>

            <!-- PANEL 2: SPONSOR A CAMPER (MATCHMAKER) -->
            <div id="adopt-tab-matchmaker" class="adopt-tab-pane" style="display: none;">
                <div class="camp-card p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
                        <div>
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-person-heart text-danger me-2"></i> Direct Camper Sponsorship Matchmaker
                            </h5>
                            <p class="text-muted small mb-0">Campers with remaining camp fees. Allocate funds directly from the Kitty pool or an external church sponsor.</p>
                        </div>

                        <div class="badge bg-danger-subtle text-danger border border-danger px-3 py-2 text-center text-md-start">
                            Kitty Balance Available: <strong>KES {{ number_format($metrics['kittyBalance'], 2) }}</strong>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-camp align-middle">
                            <thead>
                                <tr>
                                    <th>Camper Name</th>
                                    <th>Gender</th>
                                    <th>Parent / Contact</th>
                                    <th>Paid So Far</th>
                                    <th>Balance Due</th>
                                    <th>Payment Progress</th>
                                    <th class="text-end">Sponsor Allocation</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($unsponsoredCampers as $campReg)
                                    <tr class="stream-row">
                                        <td>
                                            <div class="fw-bold">{{ $campReg->teen->name }}</div>
                                            <small class="text-muted">{{ $campReg->teen->email }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $campReg->teen->gender === 'male' ? 'info text-dark' : 'danger' }} text-capitalize">
                                                {{ $campReg->teen->gender ?: 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($parent = $campReg->teen->parents->first())
                                                <div>{{ $parent->name }}</div>
                                                <small class="text-muted">{{ $parent->phone ?: 'No phone' }}</small>
                                            @else
                                                <span class="text-muted small">Desk Intake Walk-up</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-success">
                                            KES {{ number_format($campReg->total_paid, 2) }}
                                        </td>
                                        <td class="fw-bold text-danger">
                                            KES {{ number_format($campReg->balance_remaining, 2) }}
                                        </td>
                                        <td style="min-width: 140px;">
                                            <div class="d-flex justify-content-between small text-muted mb-1">
                                                <span>{{ $campReg->payment_percent }}%</span>
                                                <span>{{ $campReg->balance_remaining <= 0 ? 'Fully Paid' : 'Due' }}</span>
                                            </div>
                                            <div class="camp-progress" style="height: 6px;">
                                                <div class="camp-progress-bar" style="width: {{ $campReg->payment_percent }}%;"></div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-camp-red btn-sm text-nowrap" 
                                                    onclick="openDirectSponsorModal({{ $campReg->id }}, '{{ addslashes($campReg->teen->name) }}', {{ $campReg->balance_remaining }})">
                                                <i class="bi bi-heart-fill me-1"></i> Sponsor Camper
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                                            All registered campers for this season have their fees fully paid!
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card Feed View -->
                    <div class="d-md-none d-flex flex-column gap-3">
                        @forelse($unsponsoredCampers as $campReg)
                            <div class="camp-card p-3 border border-secondary border-opacity-25 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold text-white fs-6">{{ $campReg->teen->name }}</div>
                                        @if($campReg->teen->email)
                                            <div class="small text-muted">{{ $campReg->teen->email }}</div>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $campReg->teen->gender === 'male' ? 'info text-dark' : 'danger' }} text-capitalize">
                                        {{ $campReg->teen->gender ?: 'N/A' }}
                                    </span>
                                </div>

                                @if($parent = $campReg->teen->parents->first())
                                    <div class="small text-muted mb-2">
                                        <span>Parent: <strong class="text-white-50">{{ $parent->name }}</strong></span>
                                        @if($parent->phone)
                                            <span class="ms-1">&bull; <a href="tel:{{ $parent->phone }}" class="text-decoration-none text-white-50"><i class="bi bi-telephone-fill text-danger small"></i> {{ $parent->phone }}</a></span>
                                        @endif
                                    </div>
                                @else
                                    <div class="small text-muted mb-2">Desk Intake Walk-up</div>
                                @endif

                                <div class="row g-2 py-2 my-2 border-top border-bottom border-secondary border-opacity-25 bg-black bg-opacity-25 rounded px-2">
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: 10px;">BALANCE DUE</span>
                                        <span class="fw-bold text-danger fs-6">KES {{ number_format($campReg->balance_remaining, 0) }}</span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="text-muted d-block" style="font-size: 10px;">PAID SO FAR</span>
                                        <span class="fw-bold text-success fs-6">KES {{ number_format($campReg->total_paid, 0) }}</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 11px;">
                                        <span>Progress: {{ $campReg->payment_percent }}%</span>
                                        <span class="fw-semibold {{ $campReg->balance_remaining <= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $campReg->balance_remaining <= 0 ? 'Fully Paid' : 'Fee Pending' }}
                                        </span>
                                    </div>
                                    <div class="camp-progress" style="height: 6px;">
                                        <div class="camp-progress-bar" style="width: {{ $campReg->payment_percent }}%;"></div>
                                    </div>
                                </div>

                                <div>
                                    <button type="button" class="btn btn-camp-red btn-sm w-100 py-2 fw-semibold" 
                                            onclick="openDirectSponsorModal({{ $campReg->id }}, '{{ addslashes($campReg->teen->name) }}', {{ $campReg->balance_remaining }})"
                                            style="min-height: 44px;">
                                        <i class="bi bi-heart-fill me-1"></i> Sponsor Camper
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted camp-card">
                                <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-2"></i>
                                All registered campers for this season have their fees fully paid!
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- PANEL 3: IMMUTABLE KITTY AUDIT LEDGER -->
            <div id="adopt-tab-ledger" class="adopt-tab-pane" style="display: none;">
                <div class="camp-card p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
                        <div>
                            <h5 class="fw-bold mb-0">
                                <i class="bi bi-journal-text text-danger me-2"></i> Adopt-a-Teen Kitty Audit Ledger
                            </h5>
                            <p class="text-muted small mb-0">Immutable, audit-ready financial trail of all donations, campaign profits, and disbursements.</p>
                        </div>

                        <div class="d-flex gap-2 align-items-center w-100 w-md-auto justify-content-between justify-content-md-end">
                            <button type="button" class="btn btn-camp-red btn-sm flex-fill flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#adoptDonationModal" style="min-height: 38px;">
                                <i class="bi bi-plus-lg me-1"></i> Add Donation
                            </button>
                        </div>
                    </div>

                    <!-- Swipeable Type Filter Chips -->
                    <div class="camp-chip-scroll flex-nowrap pb-2 mb-3">
                        <a href="{{ route('backoffice.adopt.index', ['tab' => 'adopt-tab-ledger', 'ledger_type' => 'all']) }}" 
                           class="camp-filter-chip text-nowrap {{ ($filters['ledger_type'] ?? 'all') === 'all' ? 'active' : '' }}">
                            All ({{ $metrics['totalKittyIn'] + $metrics['totalKittyOut'] > 0 ? $ledger->total() : 0 }})
                        </a>
                        <a href="{{ route('backoffice.adopt.index', ['tab' => 'adopt-tab-ledger', 'ledger_type' => 'donation_in']) }}" 
                           class="camp-filter-chip text-nowrap {{ ($filters['ledger_type'] ?? '') === 'donation_in' ? 'active' : '' }}">
                            Church Donations In
                        </a>
                        <a href="{{ route('backoffice.adopt.index', ['tab' => 'adopt-tab-ledger', 'ledger_type' => 'campaign_profit_in']) }}" 
                           class="camp-filter-chip text-nowrap {{ ($filters['ledger_type'] ?? '') === 'campaign_profit_in' ? 'active' : '' }}">
                            Campaign Profits In
                        </a>
                        <a href="{{ route('backoffice.adopt.index', ['tab' => 'adopt-tab-ledger', 'ledger_type' => 'adopt_out']) }}" 
                           class="camp-filter-chip text-nowrap {{ ($filters['ledger_type'] ?? '') === 'adopt_out' ? 'active' : '' }}">
                            Sponsorships Out
                        </a>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-camp align-middle">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Type</th>
                                    <th>Description & Beneficiary</th>
                                    <th>Reference / Till</th>
                                    <th>Amount (KES)</th>
                                    <th>Balance After</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ledger as $item)
                                    <tr class="stream-row">
                                        <td>
                                            <div class="fw-semibold small">{{ $item->created_at ? $item->created_at->format('M d, Y H:i') : '' }}</div>
                                            <small class="text-muted">{{ $item->created_at ? $item->created_at->diffForHumans() : '' }}</small>
                                        </td>
                                        <td>
                                            @if($item->type === 'donation_in')
                                                <span class="badge bg-success"><i class="bi bi-arrow-down-left me-1"></i> Donation In</span>
                                            @elseif($item->type === 'campaign_profit_in')
                                                <span class="badge bg-primary"><i class="bi bi-bag-check me-1"></i> Campaign Profit</span>
                                            @else
                                                <span class="badge bg-danger"><i class="bi bi-arrow-up-right me-1"></i> Adopt Out</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold text-white">{{ $item->description }}</div>
                                            @if($item->createdByUser)
                                                <small class="text-muted">Recorded by: {{ $item->createdByUser->name }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->reference)
                                                <code class="fw-bold copy-badge" onclick="navigator.clipboard.writeText('{{ $item->reference }}'); alert('Copied reference: {{ $item->reference }}')" title="Click to copy">
                                                    {{ $item->reference }}
                                                </code>
                                            @else
                                                <span class="text-muted small">Auto-Generated</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold {{ $item->type === 'adopt_out' ? 'text-danger' : 'text-success' }}">
                                            {{ $item->type === 'adopt_out' ? '-' : '+' }}KES {{ number_format($item->amount, 2) }}
                                        </td>
                                        <td class="fw-bold">
                                            KES {{ number_format($item->balance_after, 2) }}
                                        </td>
                                        <td>
                                            @if($item->receipt)
                                                <a href="{{ route('receipts.show', $item->receipt->receipt_number) }}" target="_blank" class="badge bg-dark border text-decoration-none">
                                                    <i class="bi bi-receipt me-1"></i> #{{ $item->receipt->receipt_number }}
                                                </a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No Kitty ledger entries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Card Feed View -->
                    <div class="d-md-none d-flex flex-column gap-3">
                        @forelse($ledger as $item)
                            <div class="camp-card p-3 border border-secondary border-opacity-25 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        @if($item->type === 'donation_in')
                                            <span class="badge bg-success"><i class="bi bi-arrow-down-left me-1"></i> Donation In</span>
                                        @elseif($item->type === 'campaign_profit_in')
                                            <span class="badge bg-primary"><i class="bi bi-bag-check me-1"></i> Campaign Profit</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-arrow-up-right me-1"></i> Adopt Out</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted" style="font-size: 11px;">
                                        {{ $item->created_at ? $item->created_at->format('M d, H:i') : '' }}
                                    </div>
                                </div>

                                <div class="row g-2 py-2 my-2 border-top border-bottom border-secondary border-opacity-25 bg-black bg-opacity-25 rounded px-2">
                                    <div class="col-6">
                                        <span class="text-muted d-block" style="font-size: 10px;">AMOUNT</span>
                                        <span class="fw-bold {{ $item->type === 'adopt_out' ? 'text-danger' : 'text-success' }} fs-6">
                                            {{ $item->type === 'adopt_out' ? '-' : '+' }}KES {{ number_format($item->amount, 0) }}
                                        </span>
                                    </div>
                                    <div class="col-6 text-end">
                                        <span class="text-muted d-block" style="font-size: 10px;">BALANCE AFTER</span>
                                        <span class="fw-bold text-white small">KES {{ number_format($item->balance_after, 0) }}</span>
                                    </div>
                                </div>

                                <div class="small text-white mb-2">
                                    {{ $item->description }}
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pt-2 border-top border-secondary border-opacity-25 small" style="font-size: 11px;">
                                    <div>
                                        @if($item->reference)
                                            <code class="fw-bold copy-badge px-2 py-1 rounded bg-black border border-secondary" onclick="navigator.clipboard.writeText('{{ $item->reference }}'); alert('Copied reference: {{ $item->reference }}')" title="Click to copy">
                                                {{ $item->reference }}
                                            </code>
                                        @else
                                            <span class="text-muted">Auto-Ref</span>
                                        @endif
                                    </div>

                                    <div>
                                        @if($item->receipt)
                                            <a href="{{ route('receipts.show', $item->receipt->receipt_number) }}" target="_blank" class="badge bg-dark border text-decoration-none py-1 px-2">
                                                <i class="bi bi-receipt me-1"></i> #{{ $item->receipt->receipt_number }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted camp-card">
                                No Kitty ledger entries found.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-3">
                        {{ $ledger->links() }}
                    </div>
                </div>
            </div>

        </div>
    @else
        <div class="alert alert-warning">
            No active camp season found. Please set up a camp season first.
        </div>
    @endif
</div>

<!-- MODAL 1: ADD DONATION TO KITTY POOL -->
<div class="modal fade" id="adoptDonationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border border-danger-subtle text-white">
            <form action="{{ route('backoffice.adopt.donation') }}" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-heart-fill text-danger me-2"></i> Record Donation to Adopt-a-Teen Kitty
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 rounded bg-dark border border-danger-subtle mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-danger"><i class="bi bi-phone-fill me-1"></i> M-Pesa Daraja Paybill</span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Adopt-a-Teen Kitty Pool</span>
                        </div>
                        <div class="row g-2 small text-white">
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 10px;">PAYBILL NUMBER</span>
                                <strong>{{ $mpSettings->paybill_number ?? '880100' }}</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-white-50 d-block" style="font-size: 10px;">ADOPT-A-TEEN ACCOUNT</span>
                                <strong class="text-danger">{{ $mpSettings->adopt_account ?? 'ADOPT-A-TEEN' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Donation Amount (KES) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="1" class="form-control" name="amount" required placeholder="e.g. 5000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Donor Name / Ministry Department <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="donor_name" required placeholder="e.g. Men's Fellowship, Elder John, Anonymous">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">M-Pesa Transaction Code / Cheque Ref</label>
                        <input type="text" class="form-control text-uppercase" name="reference" placeholder="e.g. QA94XD8712">
                        <div class="form-text text-muted small">10-character Safaricom code or cheque reference.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Notes / Special Pastoral Intention</label>
                        <textarea class="form-control" name="notes" rows="2" placeholder="e.g. Designated for high school teens"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-camp-red btn-sm">Credit Kitty & Generate Official Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: REVIEW AID APPLICATION -->
<div class="modal fade" id="reviewAdoptModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border border-secondary text-white">
            <form id="reviewAdoptForm" action="" method="POST">
                @csrf
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-shield-check text-danger me-2"></i> Review Sponsorship Application
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 rounded mb-3 bg-body border border-secondary">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <small class="text-muted text-uppercase fw-bold">Camper Beneficiary</small>
                                <h5 class="fw-bold mb-0 text-white" id="modalCamperName">—</h5>
                            </div>
                            <div class="text-end">
                                <small class="text-muted text-uppercase fw-bold">Requested Aid</small>
                                <div class="fs-5 fw-bold text-danger" id="modalAmountRequested">—</div>
                            </div>
                        </div>
                        <div class="small text-muted mt-2 border-top border-secondary pt-2">
                            <strong>Parent Reason:</strong>
                            <p class="mb-0 text-white-50" id="modalReasonText">—</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Action Decision <span class="text-danger">*</span></label>
                        <select class="form-select" name="action" required id="modalActionSelect">
                            <option value="approve">✓ Approve & Disburse Funds from Kitty Pool</option>
                            <option value="approve_awaiting_funds">⌛ Approve - Awaiting Funds (When Kitty is Low)</option>
                            <option value="deny">✗ Deny Application</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pastoral / Decision Notes</label>
                        <textarea class="form-control" name="decision_notes" rows="3" placeholder="Add decision explanation (sent to parent in notification email/portal)..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-camp-red btn-sm">Confirm Decision & Save to DB</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: DIRECT SPONSOR CAMPER -->
<div class="modal fade" id="directSponsorModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark border border-danger-subtle text-white">
            <form action="{{ route('backoffice.adopt.direct-sponsor') }}" method="POST">
                @csrf
                <input type="hidden" name="registration_id" id="directSponsorRegId">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-heart-fill text-danger me-2"></i> Direct Camper Sponsorship
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 rounded mb-3 bg-body border border-secondary">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted text-uppercase fw-bold">Sponsoring Camper</small>
                                <h5 class="fw-bold mb-0 text-white" id="directCamperName">—</h5>
                            </div>
                            <div class="text-end">
                                <small class="text-muted text-uppercase fw-bold">Remaining Balance</small>
                                <div class="fs-5 fw-bold text-danger" id="directCamperBalance">—</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sponsorship Source <span class="text-danger">*</span></label>
                        <select class="form-select" name="sponsor_type" required>
                            <option value="kitty_pool">Adopt-a-Teen Kitty Pool (Current Balance: KES {{ number_format($metrics['kittyBalance'] ?? 0, 2) }})</option>
                            <option value="direct_donor">External Individual / Family Church Sponsor</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sponsorship Amount (KES) <span class="text-danger">*</span></label>
                        <input type="number" step="1" min="1" class="form-control" name="amount" id="directSponsorAmount" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sponsor Name / Pledging Family</label>
                        <input type="text" class="form-control" name="sponsor_name" placeholder="e.g. Deaconess Mary, Sunday School Fund">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reference Code (Optional)</label>
                        <input type="text" class="form-control text-uppercase" name="reference" placeholder="e.g. SPONSOR-2026">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-camp-red btn-sm">Credit Sponsorship & Issue Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Tab switching for Adopt-a-Teen deck with persistence
    function switchAdoptTab(tabId) {
        document.querySelectorAll('.adopt-tab-pane').forEach(el => {
            el.style.display = 'none';
        });
        document.querySelectorAll('.camp-tab-link').forEach(btn => {
            btn.classList.remove('active');
        });

        const targetPane = document.getElementById(tabId);
        if (targetPane) {
            targetPane.style.display = 'block';
        }

        const buttonMap = {
            'adopt-tab-queue': 'tabbtn-adopt-queue',
            'adopt-tab-matchmaker': 'tabbtn-adopt-matchmaker',
            'adopt-tab-ledger': 'tabbtn-adopt-ledger'
        };
        const activeBtn = document.getElementById(buttonMap[tabId]);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        try {
            localStorage.setItem('camp_adopt_tab', tabId);
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, null, '#' + tabId);
            }
        } catch(e) {}
    }

    // Auto-restore tab
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const queryTab = urlParams.get('tab');
        const hashTab = window.location.hash ? window.location.hash.substring(1) : null;
        const initialTab = queryTab || hashTab || localStorage.getItem('camp_adopt_tab') || 'adopt-tab-queue';
        if (document.getElementById(initialTab)) {
            switchAdoptTab(initialTab);
        }
    });

    // Open Application Review Modal
    function openReviewModal(reqId, camperName, amount, reason) {
        document.getElementById('modalCamperName').textContent = camperName;
        document.getElementById('modalAmountRequested').textContent = 'KES ' + Number(amount).toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('modalReasonText').textContent = reason || 'No specific statement provided.';
        
        const form = document.getElementById('reviewAdoptForm');
        form.action = "{{ url('/backoffice/adopt-a-teen/requests') }}/" + reqId + "/review";

        const modal = new bootstrap.Modal(document.getElementById('reviewAdoptModal'));
        modal.show();
    }

    // Open Direct Camper Sponsorship Modal
    function openDirectSponsorModal(regId, camperName, balance) {
        document.getElementById('directSponsorRegId').value = regId;
        document.getElementById('directCamperName').textContent = camperName;
        document.getElementById('directCamperBalance').textContent = 'KES ' + Number(balance).toLocaleString('en-US', {minimumFractionDigits: 2});
        document.getElementById('directSponsorAmount').value = Math.round(balance);
        document.getElementById('directSponsorAmount').max = balance;

        const modal = new bootstrap.Modal(document.getElementById('directSponsorModal'));
        modal.show();
    }
</script>
@endpush
