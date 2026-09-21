@extends('layouts.backoffice')

@section('title', 'Admin Command Center')

@section('content')
<div class="container-fluid px-0">
    <!-- Executive Command Header -->
    <div class="d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3 mb-4 pb-2 border-bottom">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <h3 class="fw-black text-uppercase mb-0 tracking-wide" style="font-size: 1.5rem;">System Admin Command Deck</h3>
                @if($season)
                    <span class="badge bg-danger px-2 py-1 text-uppercase">{{ $season->name }}</span>
                    <span class="badge bg-dark border border-secondary text-white-50 text-truncate" style="max-width: 380px;">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $season->venue }}
                    </span>
                @endif
            </div>
            <p class="text-muted small mb-0">
                Active Camp Season Overview, M-Pesa Financial Ledger, Live Intake Velocity, and Pastoral Approvals.
            </p>
        </div>

        <!-- Command Deck Quick Actions Toolbar (Desktop >= 768px) -->
        <div class="d-none d-md-flex flex-wrap gap-2 align-items-center">
            <!-- Quick Camper Intake Link -->
            <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-person-plus-fill text-danger"></i>
                <span>Intake Camper</span>
            </a>

            @if($season)
                <!-- Registration Open/Close Toggle Button -->
                <form action="{{ route('backoffice.admin.seasons.toggle-registration', $season->id) }}" method="POST" class="d-inline m-0">
                    @csrf
                    @if($season->isRegistrationOpen())
                        <button type="submit" class="btn btn-outline-warning btn-sm d-flex align-items-center gap-1" title="Click to close public registrations for this season">
                            <i class="bi bi-door-closed-fill text-warning"></i>
                            <span>Close Registration</span>
                        </button>
                    @else
                        <button type="submit" class="btn btn-success btn-sm d-flex align-items-center gap-1" title="Click to re-open public registrations for this season">
                            <i class="bi bi-door-open-fill text-white"></i>
                            <span>Re-open Registration</span>
                        </button>
                    @endif
                </form>
            @endif

            <!-- Downloadable PDF Reports Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                    <span>PDF Reports</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-dark shadow border-secondary">
                    <li><h6 class="dropdown-header text-uppercase" style="font-size: 10px;">Download Printable PDFs</h6></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('backoffice.reports.registrations.pdf', ['season_id' => $season?->id]) }}" target="_blank">
                            <i class="bi bi-people text-danger"></i> Registrations Roster PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('backoffice.reports.payments.pdf', ['season_id' => $season?->id]) }}" target="_blank">
                            <i class="bi bi-cash-stack text-success"></i> Payments &amp; Receipts PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('backoffice.reports.adopt.pdf', ['season_id' => $season?->id]) }}" target="_blank">
                            <i class="bi bi-heart-pulse text-danger"></i> Adopt-a-Teen Kitty PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('backoffice.reports.sales.pdf', ['season_id' => $season?->id]) }}" target="_blank">
                            <i class="bi bi-cart-check text-primary"></i> Merchandise Sales PDF
                        </a>
                    </li>
                    <li><hr class="dropdown-divider border-secondary"></li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('backoffice.reports.database.pdf') }}" target="_blank">
                            <i class="bi bi-search text-warning"></i> Database Query PDF
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Adopt-a-Teen Portal Shortcut -->
            <a href="{{ route('backoffice.adopt.index') }}" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-heart-pulse-fill"></i>
                <span>Adopt Portal</span>
                @if($pendingAdoptRequests->count() > 0)
                    <span class="badge bg-danger text-white rounded-pill ms-1" style="font-size: 10px;">{{ $pendingAdoptRequests->count() }}</span>
                @endif
            </a>

            <!-- Record Kitty Donation Modal Trigger -->
            <button type="button" class="btn btn-camp-red btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#donationModal">
                <i class="bi bi-heart-fill"></i>
                <span>Add Kitty Donation</span>
            </button>

            <!-- M-Pesa Daraja Settings Modal Trigger -->
            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#mpesaSettingsModal" title="Manage Paybill & 2 Account Numbers (Camp Fees & Adopt-a-Teen)">
                <i class="bi bi-phone-fill text-success"></i>
                <span>M-Pesa Paybill</span>
            </button>

            <!-- Search Database Shortcut -->
            <a href="{{ route('backoffice.admin.database') }}" class="btn btn-camp-dark btn-sm d-flex align-items-center gap-1">
                <i class="bi bi-search"></i>
                <span>Search DB</span>
            </a>

            <!-- Clean / Reset Database Modal Trigger (Admin PIN protected) -->
            <button type="button" class="btn btn-outline-danger btn-sm d-flex align-items-center gap-1 ms-lg-1" data-bs-toggle="modal" data-bs-target="#resetDbModal" title="Clean and reset database to fresh start with PIN">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Reset DB</span>
            </button>
        </div>
    </div>

    <!-- Command Deck Quick Actions Grid (Mobile < 768px) -->
    <div class="admin-mobile-action-grid d-md-none w-100 mb-4">
        <!-- 1. Intake Camper -->
        <a href="{{ route('backoffice.registration.desk') }}" class="admin-mobile-action-btn">
            <i class="bi bi-person-plus-fill text-danger"></i>
            <span>Intake Camper</span>
        </a>

        <!-- 2. Registration Toggle -->
        @if($season)
            <form action="{{ route('backoffice.admin.seasons.toggle-registration', $season->id) }}" method="POST" class="m-0 p-0 w-100">
                @csrf
                @if($season->isRegistrationOpen())
                    <button type="submit" class="admin-mobile-action-btn w-100" style="background: rgba(234, 179, 8, 0.12); border-color: rgba(234, 179, 8, 0.35); color: #FACC15;">
                        <i class="bi bi-door-closed-fill text-warning"></i>
                        <span>Close Reg</span>
                    </button>
                @else
                    <button type="submit" class="admin-mobile-action-btn w-100" style="background: rgba(34, 197, 94, 0.12); border-color: rgba(34, 197, 94, 0.35); color: #4ADE80;">
                        <i class="bi bi-door-open-fill text-success"></i>
                        <span>Open Reg</span>
                    </button>
                @endif
            </form>
        @endif

        <!-- 3. Add Kitty Donation -->
        <button type="button" class="admin-mobile-action-btn" data-bs-toggle="modal" data-bs-target="#donationModal" style="background: rgba(220, 38, 38, 0.15); border-color: rgba(220, 38, 38, 0.4);">
            <i class="bi bi-heart-fill text-danger"></i>
            <span>Add Kitty</span>
        </button>

        <!-- 4. Adopt Portal -->
        <a href="{{ route('backoffice.adopt.index') }}" class="admin-mobile-action-btn position-relative">
            <i class="bi bi-heart-pulse-fill text-danger"></i>
            <span>Adopt Portal</span>
            @if($pendingAdoptRequests->count() > 0)
                <span class="badge bg-danger text-white rounded-pill position-absolute top-0 end-0 m-1" style="font-size: 9px;">{{ $pendingAdoptRequests->count() }}</span>
            @endif
        </a>

        <!-- 5. PDF Reports Dropdown -->
        <div class="dropdown">
            <button class="admin-mobile-action-btn w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                <span>PDF Reports <i class="bi bi-chevron-down ms-1" style="font-size: 9px;"></i></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark shadow border-secondary">
                <li><h6 class="dropdown-header text-uppercase" style="font-size: 10px;">Download Printable PDFs</h6></li>
                <li><a class="dropdown-item py-2" href="{{ route('backoffice.reports.registrations.pdf', ['season_id' => $season?->id]) }}" target="_blank"><i class="bi bi-people text-danger me-2"></i>Registrations Roster</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('backoffice.reports.payments.pdf', ['season_id' => $season?->id]) }}" target="_blank"><i class="bi bi-cash-stack text-success me-2"></i>Payments &amp; Receipts</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('backoffice.reports.adopt.pdf', ['season_id' => $season?->id]) }}" target="_blank"><i class="bi bi-heart-pulse text-danger me-2"></i>Adopt-a-Teen Kitty</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('backoffice.reports.sales.pdf', ['season_id' => $season?->id]) }}" target="_blank"><i class="bi bi-cart-check text-primary me-2"></i>Merchandise Sales</a></li>
                <li><hr class="dropdown-divider border-secondary"></li>
                <li><a class="dropdown-item py-2" href="{{ route('backoffice.reports.database.pdf') }}" target="_blank"><i class="bi bi-search text-warning me-2"></i>Database Query PDF</a></li>
            </ul>
        </div>

        <!-- 6. M-Pesa Paybill Setup -->
        <button type="button" class="admin-mobile-action-btn" data-bs-toggle="modal" data-bs-target="#mpesaSettingsModal">
            <i class="bi bi-phone-fill text-success"></i>
            <span>M-Pesa Setup</span>
        </button>

        <!-- 7. Database Search -->
        <a href="{{ route('backoffice.admin.database') }}" class="admin-mobile-action-btn">
            <i class="bi bi-search text-info"></i>
            <span>Search DB</span>
        </a>

        <!-- 8. Reset Database -->
        <button type="button" class="admin-mobile-action-btn text-danger" data-bs-toggle="modal" data-bs-target="#resetDbModal">
            <i class="bi bi-arrow-clockwise text-danger"></i>
            <span>Reset DB</span>
        </button>
    </div>

    @if($season)
        <!-- 4 TOP INTERACTIVE METRIC CARDS -->
        <div class="row g-3 mb-4">
            <!-- 1. Camp Registrations & Intake Velocity -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="camp-metric-card accent-red h-100" onclick="switchDashboardTab('tab-campers')" style="cursor: pointer;" title="Click to view Campers Roster">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="overflow-hidden">
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1">Camp Registrations</span>
                            <div class="fs-3 fw-black lh-1 mt-1 text-danger">{{ $stats['totalRegistered'] }} <span class="fs-6 text-muted fw-normal">/ {{ $season->capacity }}</span></div>
                        </div>
                        <div class="metric-icon-bubble" style="background: rgba(220, 38, 38, 0.15); color: #EF4444; border: 1px solid rgba(220, 38, 38, 0.3);">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between small text-muted mt-2">
                        <span>{{ $stats['spotsRemaining'] }} spots available</span>
                        <span class="fw-bold text-danger">{{ $stats['capacityPercent'] }}% Full</span>
                    </div>
                    
                    <div class="mini-meter-track">
                        <div class="mini-meter-fill bg-danger" style="width: {{ $stats['capacityPercent'] }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 small text-muted border-top border-secondary border-opacity-15">
                        <span><i class="bi bi-gender-male text-white me-1"></i>{{ $stats['maleCount'] }} Boys</span>
                        <span><i class="bi bi-gender-female text-danger me-1"></i>{{ $stats['femaleCount'] }} Girls</span>
                        <span class="text-danger fw-semibold">View <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </div>

            <!-- 2. Camp Day Headcount & Attendance -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="camp-metric-card accent-emerald h-100" onclick="window.location.href='{{ route('backoffice.registration.signin') }}'" style="cursor: pointer;" title="Click to open Live Sign-In Desk">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="overflow-hidden">
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1">Camp Attendance</span>
                            <div class="fs-3 fw-black lh-1 mt-1 text-success">{{ $stats['totalSignedIn'] }} <span class="fs-6 text-muted fw-normal">Present</span></div>
                        </div>
                        <div class="metric-icon-bubble" style="background: rgba(16, 185, 129, 0.15); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3);">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-2">
                        <span>{{ max(0, $stats['totalRegistered'] - $stats['totalSignedIn']) }} arriving soon</span>
                        <span class="fw-bold text-success">{{ $stats['attendancePercent'] }}% Checked In</span>
                    </div>

                    <div class="mini-meter-track">
                        <div class="mini-meter-fill" style="width: {{ $stats['attendancePercent'] }}%; background: linear-gradient(90deg, #059669 0%, #10B981 100%);"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 small text-muted border-top border-secondary border-opacity-15">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Gate Desk</span>
                        <span class="text-success fw-semibold">Launch Sign-In <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </div>

            <!-- 3. Tuition Revenue in KES -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="camp-metric-card accent-crimson h-100" onclick="switchDashboardTab('tab-financials')" style="cursor: pointer;" title="Click to view Financial Insights">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="overflow-hidden">
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1">Camp Fees Collected</span><span class="visually-hidden">Tuition Collected</span>
                            <div class="fs-4 fw-black lh-1 mt-1 text-white text-break">KES {{ number_format($stats['totalRevenue'], 2) }}</div>
                        </div>
                        <div class="metric-icon-bubble" style="background: rgba(220, 38, 38, 0.15); color: #EF4444; border: 1px solid rgba(220, 38, 38, 0.3);">
                            <i class="bi bi-wallet2"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-2">
                        <span>Goal: KES {{ number_format($stats['targetRevenue'], 0) }}</span>
                        <span class="fw-bold text-danger">{{ $stats['revenuePercent'] }}% Target</span>
                    </div>

                    <div class="mini-meter-track">
                        <div class="mini-meter-fill bg-danger" style="width: {{ $stats['revenuePercent'] }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 small text-muted border-top border-secondary border-opacity-15 flex-wrap gap-1">
                        <span><span class="badge bg-success-subtle text-success p-1">{{ $stats['fullyPaidCount'] }} Paid</span></span>
                        <span><span class="badge bg-dark border border-secondary text-white-50 p-1">{{ $stats['partialPaidCount'] }} Partial</span></span>
                        <span><span class="badge bg-danger-subtle text-danger p-1">{{ $stats['unpaidCount'] }} Due</span></span>
                    </div>
                </div>
            </div>

            <!-- 4. Adopt-a-Teen Kitty Balance -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="camp-metric-card accent-red h-100" onclick="window.location.href='{{ route('backoffice.adopt.index') }}'" style="cursor: pointer;" title="Click to open Adopt-a-Teen Portal">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="overflow-hidden">
                            <span class="text-muted small text-uppercase fw-bold letter-spacing-1">Adopt-a-Teen Kitty</span>
                            <div class="fs-4 fw-black lh-1 mt-1 text-danger text-break">KES {{ number_format($stats['kittyBalance'], 2) }}</div>
                        </div>
                        <div class="metric-icon-bubble" style="background: rgba(220, 38, 38, 0.15); color: #EF4444; border: 1px solid rgba(220, 38, 38, 0.3);">
                            <i class="bi bi-heart-pulse-fill"></i>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between small text-muted mt-2 flex-wrap gap-1">
                        <span class="text-success"><i class="bi bi-arrow-down-left me-1"></i>+KES {{ number_format($stats['totalKittyIn'], 0) }} In</span>
                        <span class="text-danger"><i class="bi bi-arrow-up-right me-1"></i>-KES {{ number_format($stats['totalKittyOut'], 0) }} Out</span>
                    </div>

                    <div class="mini-meter-track">
                        @php
                            $kittyFlowTotal = max(1, $stats['totalKittyIn'] + $stats['totalKittyOut']);
                            $kittyNetPct = min(100, round(($stats['kittyBalance'] / max(1, $stats['totalKittyIn'])) * 100));
                        @endphp
                        <div class="mini-meter-fill bg-danger" style="width: {{ $kittyNetPct }}%;"></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 small text-muted border-top border-secondary border-opacity-15">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Dedicated Portal</span>
                        <span class="text-danger fw-semibold">Open Portal <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- LIVE ACTIVITY & AUDIT TICKER BAR -->
        <div class="alert alert-dark border border-secondary border-opacity-50 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between p-2 px-3 mb-4 rounded-3 shadow-sm" style="background: rgba(18, 18, 20, 0.95); min-width: 0; max-width: 100%;">
            <div class="d-flex align-items-center gap-2 overflow-hidden text-truncate mb-2 mb-lg-0" style="min-width: 0;">
                <span class="badge bg-danger text-white text-uppercase d-inline-flex align-items-center gap-1 py-1 px-2 flex-shrink-0" style="font-size: 10px; letter-spacing: 0.5px;">
                    <span class="spinner-grow text-white" style="width: 7px; height: 7px;" role="status"></span>
                    Live DB Sync
                </span>
                <span class="text-white-50 small text-truncate" style="min-width: 0;">
                    @if(isset($recentNotifications) && $recentNotifications->isNotEmpty())
                        <strong class="text-white">{{ $recentNotifications->first()->title }}:</strong> {{ $recentNotifications->first()->message }} 
                        <span class="opacity-50 ms-1">({{ $recentNotifications->first()->created_at ? $recentNotifications->first()->created_at->diffForHumans() : 'Just now' }})</span>
                    @else
                        System audit active. All payments, sales, registrations, and form submissions append directly to MySQL.
                    @endif
                </span>
            </div>
            <button type="button" class="btn btn-outline-danger btn-sm text-nowrap py-1 px-3 fw-semibold flex-shrink-0" style="font-size: 11px;" onclick="switchDashboardTab('tab-activity')">
                <i class="bi bi-clock-history me-1"></i> View Live Notification Feed &rarr;
            </button>
        </div>

        <!-- INTERACTIVE DASHBOARD NAV TABS (No dead space) -->
        <div class="camp-tabs-nav">
            <button type="button" class="camp-tab-link active" id="tabbtn-queues" onclick="switchDashboardTab('tab-queues')">
                <i class="bi bi-inbox-fill"></i> Action Items & Queues
                @php $totalPending = $pendingAdoptRequests->count() + $pendingBatches->count(); @endphp
                <span class="badge bg-{{ $totalPending > 0 ? 'danger' : 'secondary' }} rounded-pill ms-1">{{ $totalPending }}</span>
            </button>

            <button type="button" class="camp-tab-link" id="tabbtn-campers" onclick="switchDashboardTab('tab-campers')">
                <i class="bi bi-person-lines-fill"></i> Live Camper Roster
                <span class="badge bg-dark border rounded-pill ms-1">{{ $stats['totalRegistered'] }}</span>
            </button>

            <button type="button" class="camp-tab-link" id="tabbtn-financials" onclick="switchDashboardTab('tab-financials')">
                <i class="bi bi-cash-coin"></i> Financials & M-Pesa Streams
            </button>

            <button type="button" class="camp-tab-link" id="tabbtn-demographics" onclick="switchDashboardTab('tab-demographics')">
                <i class="bi bi-shield-heart-fill"></i> Medical & Logistics Watch
                @if($stats['medicalFlagsCount'] > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $stats['medicalFlagsCount'] }} Alerts</span>
                @endif
            </button>

            <button type="button" class="camp-tab-link" id="tabbtn-activity" onclick="switchDashboardTab('tab-activity')">
                <i class="bi bi-broadcast"></i> Live Activity & Notifications
                <span class="badge bg-danger rounded-pill ms-1">{{ isset($recentNotifications) ? $recentNotifications->count() : 0 }}</span>
            </button>
        </div>

        <!-- TAB CONTENT PANELS -->
        <div class="tab-content mb-4">
            
            <!-- PANEL 1: Action Items & Queues -->
            <div id="tab-queues" class="dashboard-tab-pane">
                <div class="row g-4">
                    <!-- Adopt-a-Teen Approval Queue -->
                    <div class="col-lg-6">
                        <div class="camp-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <div>
                                    <h5 class="fw-bold mb-0">
                                        <i class="bi bi-heart-pulse text-danger me-2"></i> Adopt-a-Teen Approval Queue
                                    </h5>
                                    <p class="text-muted small mb-0">Financial sponsorship applications from parents needing aid.</p>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="{{ route('backoffice.adopt.index') }}" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 11px;">
                                        Open Full Portal &rarr;
                                    </a>
                                    <span class="badge bg-{{ $pendingAdoptRequests->count() > 0 ? 'danger' : 'success' }} px-2 py-1">
                                        {{ $pendingAdoptRequests->count() }} Pending
                                    </span>
                                </div>
                            </div>

                            @forelse($pendingAdoptRequests as $req)
                                <div class="border rounded p-3 mb-3" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-0">{{ $req->teen->name }}</h6>
                                            <span class="small text-muted">Parent: {{ $req->parent->name }} ({{ $req->parent->phone ?: $req->parent->email }})</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="fs-5 fw-bold text-danger">KES {{ number_format($req->amount_requested, 2) }}</span>
                                            <div class="badge bg-{{ $req->status === 'approved-awaiting-funds' ? 'warning text-dark' : 'secondary' }} d-block mt-1">
                                                {{ ucfirst(str_replace('-', ' ', $req->status)) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="p-2 rounded bg-body small mb-3 border">
                                        <strong>Reason:</strong> {{ $req->reason }}
                                    </div>

                                    <form action="{{ route('backoffice.admin.adopt.review', $req) }}" method="POST">
                                        @csrf
                                        <div class="mb-2">
                                            <input type="text" class="form-control form-control-sm" name="decision_notes" placeholder="Reviewer notes (optional)">
                                        </div>
                                        <div class="d-flex gap-2">
                                            <button type="submit" name="action" value="approve" class="btn btn-success btn-sm flex-grow-1" 
                                                    {{ $stats['kittyBalance'] < $req->amount_requested ? 'disabled' : '' }}>
                                                <i class="bi bi-check-lg me-1"></i> Approve & Disburse
                                            </button>
                                            <button type="submit" name="action" value="approve_awaiting_funds" class="btn btn-warning btn-sm flex-grow-1">
                                                <i class="bi bi-hourglass-split me-1"></i> Await Funds
                                            </button>
                                            <button type="submit" name="action" value="deny" class="btn btn-outline-danger btn-sm">
                                                Deny
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <div class="d-inline-flex p-3 rounded-circle bg-success-subtle text-success mb-3">
                                        <i class="bi bi-check-circle-fill fs-2"></i>
                                    </div>
                                    <h6 class="fw-bold">All Sponsorship Requests Processed</h6>
                                    <p class="small text-muted mb-3" style="max-width: 350px; margin: 0 auto;">
                                        There are no outstanding Adopt-a-Teen financial aid requests requiring your attention right now.
                                    </p>
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#donationModal">
                                        <i class="bi bi-plus-circle me-1"></i> Add Direct Donation to Kitty
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Campaign Weekly Batch Approvals -->
                    <div class="col-lg-6">
                        <div class="camp-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <div>
                                    <h5 class="fw-bold mb-0">
                                        <i class="bi bi-cash-stack text-danger me-2"></i> Campaign Weekly Batches
                                    </h5>
                                    <p class="text-muted small mb-0">Merchandise profits awaiting verification into the Kitty.</p>
                                </div>
                                <span class="badge bg-{{ $pendingBatches->count() > 0 ? 'warning text-dark' : 'success' }} px-2 py-1">
                                    {{ $pendingBatches->count() }} Awaiting Approval
                                </span>
                            </div>

                            @forelse($pendingBatches as $batch)
                                <div class="border rounded p-3 mb-3" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-0">{{ $batch->week_label }}</h6>
                                            <span class="small text-muted">{{ $batch->week_start->format('M d') }} - {{ $batch->week_end->format('M d, Y') }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="small text-muted">Calculated Profit:</span>
                                            <div class="fs-5 fw-bold text-success">KES {{ number_format($batch->total_profit_amount, 2) }}</div>
                                            <span class="small text-muted">(Sales: KES {{ number_format($batch->total_sales_amount, 2) }})</span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mt-3">
                                        <form action="{{ route('backoffice.campaign.batches.approve', $batch) }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm w-100" onclick="return confirm('Approve this batch and transfer KES {{ number_format($batch->total_profit_amount, 2) }} profit to Adopt-a-Teen Kitty?')">
                                                <i class="bi bi-check2-all me-1"></i> Approve & Transfer to Kitty
                                            </button>
                                        </form>
                                        <form action="{{ route('backoffice.campaign.batches.return', $batch) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                Return for Correction
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <div class="d-inline-flex p-3 rounded-circle bg-success-subtle text-success mb-3">
                                        <i class="bi bi-bag-check-fill fs-2"></i>
                                    </div>
                                    <h6 class="fw-bold">No Pending Holding Batches</h6>
                                    <p class="small text-muted mb-3" style="max-width: 350px; margin: 0 auto;">
                                        All youth campaign merchandise holding batches are reconciled and deposited in the Kitty.
                                    </p>
                                    <a href="{{ route('backoffice.campaign.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Open Campaign POS Console
                                    </a>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 2: Live Camper Roster & Intake Stream -->
            <div id="tab-campers" class="dashboard-tab-pane" style="display: none;">
                <div class="camp-card p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
                        <div>
                            <h5 class="fw-bold mb-0"><i class="bi bi-people-fill text-danger me-2"></i> Live Camper Roster</h5>
                            <p class="text-muted small mb-0">Real-time status of registered teens for {{ $season->name }}.</p>
                        </div>

                        <!-- Live Filter Input -->
                        <div class="d-flex gap-2 align-items-center">
                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                <span class="input-group-text bg-body border-secondary"><i class="bi bi-search text-danger"></i></span>
                                <input type="text" class="form-control border-secondary" id="camperSearchInput" placeholder="Quick filter campers..." onkeyup="filterCamperRows(this.value)">
                            </div>
                            <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-camp-red btn-sm text-nowrap">
                                <i class="bi bi-plus-circle me-1"></i> New Camper
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-camp align-middle" id="camperRosterTable">
                            <thead>
                                <tr>
                                    <th>Camper Name</th>
                                    <th>Gender</th>
                                    <th>Parent / Guardian</th>
                                    <th>Phone Carried</th>
                                    <th>Medical Alert</th>
                                    <th>Camp Fee Progress</th>
                                    <th>Camp Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRegistrations as $reg)
                                    <tr class="stream-row camper-data-row" data-search="{{ strtolower($reg->teen->name . ' ' . ($reg->teen->parents->first() ? $reg->teen->parents->first()->name : '') . ' ' . $reg->status) }}">
                                        <td>
                                            <a href="javascript:void(0)" class="fw-bold text-white text-decoration-none text-hover-danger d-inline-flex align-items-center gap-1"
                                               data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}"
                                               title="Click to view parent info and siblings">
                                                {{ $reg->teen->name }}
                                                <i class="bi bi-people-fill text-danger small" style="font-size:11px;"></i>
                                            </a>
                                            <div><small class="text-muted">{{ $reg->teen->email }}</small></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $reg->teen->gender === 'male' ? 'info text-dark' : 'danger' }} text-capitalize">
                                                {{ $reg->teen->gender }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($reg->teen->parents->count() > 0)
                                                <div>{{ $reg->teen->parents->first()->name }}</div>
                                                <small class="text-muted">{{ $reg->teen->parents->first()->phone ?: 'No phone' }}</small>
                                            @else
                                                <span class="small text-muted">Desk Walk-up</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $reg->phone_carried ? 'warning text-dark' : 'secondary' }}">
                                                {{ $reg->phone_carried ? 'Phone Allowed' : 'No Phone' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($reg->medical_conditions || $reg->medication_notes)
                                                <span class="badge bg-danger-subtle text-danger border border-danger" title="{{ $reg->medical_conditions }} | {{ $reg->medication_notes }}">
                                                    <i class="bi bi-heart-pulse-fill me-1"></i> Medical Flag
                                                </span>
                                            @else
                                                <span class="text-muted small"><i class="bi bi-check2 text-success me-1"></i> Clear</span>
                                            @endif
                                        </td>
                                        <td style="min-width: 150px;">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="fw-bold">KES {{ number_format($reg->total_paid, 2) }}</span>
                                                <span class="text-muted">{{ $reg->payment_percent }}%</span>
                                            </div>
                                            <div class="camp-progress" style="height: 6px;">
                                                <div class="camp-progress-bar" style="width: {{ $reg->payment_percent }}%;"></div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($reg->status === 'signed_in')
                                                <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i> Signed In</span>
                                            @elseif($reg->status === 'registered')
                                                <span class="badge bg-primary">Registered</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($reg->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1 align-items-center">
                                                {{-- View Family Profile --}}
                                                <button type="button" class="btn btn-outline-info btn-sm p-1" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;"
                                                        data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}"
                                                        title="View parent info, siblings, and edit profile">
                                                    <i class="bi bi-people-fill text-info" style="font-size: 11px;"></i>
                                                </button>

                                                @if($reg->status === 'signed_in')
                                                    <form action="{{ route('backoffice.registration.signin.undo', $reg) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-warning btn-sm py-0 px-2" style="font-size: 11px;" title="Undo Check-in">
                                                            <i class="bi bi-arrow-counterclockwise"></i> Undo
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('backoffice.registration.signin.checkin', $reg) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success btn-sm py-0 px-2" style="font-size: 11px;" title="Check-in Camper">
                                                            <i class="bi bi-check2-circle"></i> Check In
                                                        </button>
                                                    </form>
                                                @endif

                                                <form action="{{ route('backoffice.admin.users.reset-pin', $reg->teen) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset PIN for {{ $reg->teen->name }} to 0000?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm p-1" title="Reset PIN to 0000" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-key-fill text-warning" style="font-size: 11px;"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('backoffice.admin.users.toggle-suspension', $reg->teen) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $reg->teen->is_suspended ? 'reactivate' : 'suspend' }} account for {{ $reg->teen->name }}?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-{{ $reg->teen->is_suspended ? 'success' : 'danger' }} btn-sm p-1" title="{{ $reg->teen->is_suspended ? 'Reactivate Account' : 'Suspend Account' }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="bi {{ $reg->teen->is_suspended ? 'bi-person-check-fill text-success' : 'bi-person-x-fill text-danger' }}" style="font-size: 11px;"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('backoffice.admin.users.delete', $reg->teen) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete camper {{ $reg->teen->name }} ({{ $reg->teen->email }})? All records will be removed.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm p-1" title="Delete Account" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">
                                                        <i class="bi bi-trash3-fill text-danger" style="font-size: 11px;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No registrations found for this season.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- PANEL 3: Financials & M-Pesa Streams -->
            <div id="tab-financials" class="dashboard-tab-pane" style="display: none;">
                <div class="row g-4">
                    <!-- Lipa na M-Pesa Station Card -->
                    <div class="col-lg-5">
                        <div class="camp-card p-4 h-100">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="p-2 rounded bg-success text-white d-inline-flex"><i class="bi bi-phone-vibrate-fill fs-5"></i></span>
                                <div>
                                    <h5 class="fw-bold mb-0">Lipa na M-Pesa Paybill Channels</h5>
                                    <small class="text-muted">Daraja M-Pesa Paybill & Dual Account Routing</small>
                                </div>
                            </div>

                            <div class="p-3 rounded border mb-3" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-uppercase text-muted">Business Paybill Number</span>
                                    <span class="badge bg-success">Active Paybill</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fs-4 fw-black text-danger tracking-wider">{{ $mpesaSettings->paybill_number ?? '880100' }}</span>
                                    <button class="btn btn-sm btn-outline-secondary copy-badge" onclick="navigator.clipboard.writeText('{{ $mpesaSettings->paybill_number ?? '880100' }}'); alert('Copied Paybill: {{ $mpesaSettings->paybill_number ?? '880100' }}')">
                                        <i class="bi bi-clipboard me-1"></i> Copy
                                    </button>
                                </div>
                                <small class="text-muted d-block mt-1">Official Name: <strong>Teen Camp Kenya</strong></small>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="p-2 rounded border" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                                        <div class="small fw-bold text-muted text-uppercase" style="font-size: 0.72rem;">Account 1: Camp Fees</div>
                                        <div class="fw-bold text-white small mt-1 font-monospace">{{ $mpesaSettings->camp_fee_account ?? 'CAMP-FEES' }}</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-2 rounded border" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                                        <div class="small fw-bold text-muted text-uppercase" style="font-size: 0.72rem;">Account 2: Adopt-a-Teen</div>
                                        <div class="fw-bold text-danger small mt-1 font-monospace">{{ $mpesaSettings->adopt_account ?? 'ADOPT-A-TEEN' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3 rounded bg-dark border border-danger border-opacity-25 small">
                                <div class="fw-bold text-danger mb-1"><i class="bi bi-info-circle me-1"></i> M-Pesa Reconciliation Rule:</div>
                                <p class="text-white-50 mb-0">Every direct or desk payment captures the 10-character alphanumeric Safaricom confirmation code for immutable accounting auditability.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent M-Pesa Transactions Stream -->
                    <div class="col-lg-7">
                        <div class="camp-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <div>
                                    <h5 class="fw-bold mb-0"><i class="bi bi-receipt-cutoff text-danger me-2"></i> Recent Payment Stream</h5>
                                    <p class="text-muted small mb-0">Latest verified camp fee and donation payments.</p>
                                </div>
                                <span class="badge bg-danger">{{ $recentPayments->count() }} Payments</span>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-camp align-middle">
                                    <thead>
                                        <tr>
                                            <th>Camper / Payer</th>
                                            <th>Method</th>
                                            <th>Reference Code</th>
                                            <th>Amount</th>
                                            <th>Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentPayments as $pay)
                                            <tr class="stream-row">
                                                <td>
                                                    <div class="fw-bold">{{ $pay->registration && $pay->registration->teen ? $pay->registration->teen->name : 'Camp Donation' }}</div>
                                                    <small class="text-muted">{{ $pay->parent ? $pay->parent->name : 'Church Desk' }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ str_contains($pay->payment_method, 'M-Pesa') ? 'success' : 'secondary' }}">
                                                        {{ $pay->payment_method }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <code class="fw-bold text-danger">{{ $pay->reference }}</code>
                                                </td>
                                                <td class="fw-bold">
                                                    KES {{ number_format($pay->amount, 2) }}
                                                </td>
                                                <td>
                                                    @if($pay->receipt_number)
                                                        <a href="{{ route('receipts.show', $pay->receipt_number) }}" target="_blank" class="btn btn-outline-danger btn-sm p-1 text-nowrap" title="Print Official Receipt">
                                                            <i class="bi bi-printer me-1"></i> #{{ substr($pay->receipt_number, -6) }}
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">No completed payments recorded yet for this season.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 4: Demographics, Medical & Logistics Watch -->
            <div id="tab-demographics" class="dashboard-tab-pane" style="display: none;">
                <div class="row g-4">
                    <!-- Medical Alerts & Allergy Watch -->
                    <div class="col-lg-6">
                        <div class="camp-card p-4 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <div>
                                    <h5 class="fw-bold mb-0 text-danger"><i class="bi bi-shield-plus me-2"></i> Camp Medical & Allergy Watch</h5>
                                    <p class="text-muted small mb-0">Crucial medical information declared by parents for camp safety.</p>
                                </div>
                                <span class="badge bg-danger">{{ $stats['medicalFlagsCount'] }} Declared</span>
                            </div>

                            @php
                                $flaggedCampers = $recentRegistrations->filter(fn($r) => !empty($r->medical_conditions) || !empty($r->medication_notes));
                            @endphp

                            @if($flaggedCampers->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-camp align-middle">
                                        <thead>
                                            <tr>
                                                <th>Camper</th>
                                                <th>Emergency Contact</th>
                                                <th>Conditions & Medication</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($flaggedCampers as $fc)
                                                <tr>
                                                    <td class="fw-bold">{{ $fc->teen->name }}</td>
                                                    <td class="small">
                                                        <div>{{ $fc->emergency_contact_name }}</div>
                                                        <span class="text-muted">{{ $fc->emergency_contact_phone }}</span>
                                                    </td>
                                                    <td class="small">
                                                        @if($fc->medical_conditions)
                                                            <div class="text-danger fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i>{{ $fc->medical_conditions }}</div>
                                                        @endif
                                                        @if($fc->medication_notes)
                                                            <div class="text-muted"><i class="bi bi-capsule me-1"></i>{{ $fc->medication_notes }}</div>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-shield-check text-success fs-2 mb-2 d-block"></i>
                                    <p class="small mb-0">No active medical flags reported among current campers.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Logistics & Compliance Breakdown -->
                    <div class="col-lg-6">
                        <div class="camp-card p-4 h-100">
                            <h5 class="fw-bold mb-3 border-bottom pb-2"><i class="bi bi-compass-fill text-danger me-2"></i> Camp Logistics Readiness</h5>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Gender Balance</span>
                                    <span>{{ $stats['maleCount'] }} Boys / {{ $stats['femaleCount'] }} Girls</span>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    @php
                                        $totalG = max(1, $stats['maleCount'] + $stats['femaleCount']);
                                        $malePct = round(($stats['maleCount'] / $totalG) * 100);
                                        $femPct = 100 - $malePct;
                                    @endphp
                                    <div class="progress-bar bg-info" style="width: {{ $malePct }}%;" title="Boys ({{ $malePct }}%)"></div>
                                    <div class="progress-bar bg-danger" style="width: {{ $femPct }}%;" title="Girls ({{ $femPct }}%)"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mt-1">
                                    <span class="text-info fw-bold">{{ $malePct }}% Boys</span>
                                    <span class="text-danger fw-bold">{{ $femPct }}% Girls</span>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>Mobile Phone Declarations</span>
                                    <span>{{ $stats['phonesCarriedCount'] }} Phones Declared</span>
                                </div>
                                <div class="progress" style="height: 12px;">
                                    @php
                                        $totReg = max(1, $stats['totalRegistered']);
                                        $phonePct = round(($stats['phonesCarriedCount'] / $totReg) * 100);
                                    @endphp
                                    <div class="progress-bar bg-warning text-dark" style="width: {{ $phonePct }}%;"></div>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mt-1">
                                    <span class="text-warning fw-bold">{{ $phonePct }}% Campers with Phone</span>
                                    <span>{{ 100 - $phonePct }}% Device-Free</span>
                                </div>
                            </div>

                            <div class="p-3 rounded border" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="small fw-bold text-uppercase text-muted">Camp Season Venue</div>
                                        <div class="fw-bold fs-6">{{ $season->venue }}</div>
                                    </div>
                                    <a href="{{ route('backoffice.admin.seasons') }}" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-pencil-square me-1"></i> Edit Season
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PANEL 5: Live Activity & System Notifications -->
            <div id="tab-activity" class="dashboard-tab-pane" style="display: none;">
                <div class="camp-card p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold mb-0"><i class="bi bi-broadcast text-danger me-2"></i> Live Activity & Notification Audit Trail</h5>
                                <span class="badge bg-danger text-uppercase" style="font-size: 10px;">MySQL Real-time</span>
                            </div>
                            <p class="text-muted small mb-0">Every form submitted, payment logged, merchandise sold, and intake check-in automatically appends here.</p>
                        </div>

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <!-- Category Filter Chips -->
                            <span class="camp-filter-chip notif-filter-chip active" onclick="filterActivityRows('all', this)">All ({{ isset($recentNotifications) ? $recentNotifications->count() : 0 }})</span>
                            <span class="camp-filter-chip notif-filter-chip" onclick="filterActivityRows('payment', this)">Payments</span>
                            <span class="camp-filter-chip notif-filter-chip" onclick="filterActivityRows('campaign_sale', this)">Sales</span>
                            <span class="camp-filter-chip notif-filter-chip" onclick="filterActivityRows('form', this)">Forms</span>
                            <span class="camp-filter-chip notif-filter-chip" onclick="filterActivityRows('checkin', this)">Check-in</span>
                            <span class="camp-filter-chip notif-filter-chip" onclick="filterActivityRows('system', this)">System</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-camp align-middle" id="activityStreamTable">
                            <thead>
                                <tr>
                                    <th>Event Timestamp</th>
                                    <th>Category</th>
                                    <th>Title & Action Details</th>
                                    <th>Target Recipient</th>
                                    <th>Status</th>
                                    <th class="text-end">Direct Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentNotifications as $notif)
                                    <tr class="stream-row activity-row" data-type="{{ $notif->type }}">
                                        <td class="text-nowrap" style="width: 140px;">
                                            <div class="fw-bold small">{{ $notif->created_at ? $notif->created_at->format('M d, H:i:s') : 'Just now' }}</div>
                                            <small class="text-muted">{{ $notif->created_at ? $notif->created_at->diffForHumans() : '' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-dark border border-secondary text-capitalize">
                                                <i class="bi {{ $notif->icon_class }} me-1"></i>
                                                {{ str_replace('_', ' ', $notif->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-white mb-0">{{ $notif->title }}</div>
                                            <div class="small text-muted" style="line-height: 1.35;">{{ $notif->message }}</div>
                                        </td>
                                        <td>
                                            @if($notif->user)
                                                <span class="badge bg-secondary text-white">{{ $notif->user->name }} ({{ $notif->user->role }})</span>
                                            @elseif($notif->target_role)
                                                <span class="badge bg-dark border text-muted">Role: {{ $notif->target_role }}</span>
                                            @else
                                                <span class="badge bg-dark border text-muted">All Users</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success border border-success">
                                                <i class="bi bi-database-check me-1"></i> Appended to DB
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if($notif->link)
                                                <a href="{{ $notif->link }}" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 11px;">
                                                    View &rarr;
                                                </a>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No system activity logged yet for this season.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

        <!-- ADOPT-A-TEEN IMMUTABLE KITTY AUDIT LEDGER (With interactive filter chips) -->
        <div class="camp-card p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3 border-bottom pb-2">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-journal-text text-danger me-2"></i> Adopt-a-Teen Kitty Audit Ledger</h5>
                    <p class="text-muted small mb-0">Immutable ledger trail of all church donations, campaign profit transfers, and sponsorships.</p>
                </div>

                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <!-- Interactive Filter Chips -->
                    <span class="camp-filter-chip active" id="chip-all" onclick="filterLedgerRows('all', this)">All ({{ $recentKittyLedger->count() }})</span>
                    <span class="camp-filter-chip" id="chip-donations" onclick="filterLedgerRows('donation_in', this)">Donations</span>
                    <span class="camp-filter-chip" id="chip-campaign" onclick="filterLedgerRows('campaign_profit_in', this)">Campaign Profits</span>
                    <span class="camp-filter-chip" id="chip-aid" onclick="filterLedgerRows('adopt_out', this)">Sponsorships</span>

                    <button type="button" class="btn btn-camp-outline-red btn-sm ms-md-2" data-bs-toggle="modal" data-bs-target="#donationModal">
                        <i class="bi bi-plus-lg me-1"></i> Record Donation
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-camp align-middle" id="kittyLedgerTable">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Transaction Type</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th>Amount (KES)</th>
                            <th>Balance After</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentKittyLedger as $entry)
                            <tr class="stream-row ledger-row" data-type="{{ $entry->type }}">
                                <td>{{ $entry->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($entry->type === 'donation_in')
                                        <span class="badge bg-success"><i class="bi bi-arrow-down-left me-1"></i> Donation In</span>
                                    @elseif($entry->type === 'campaign_profit_in')
                                        <span class="badge bg-primary"><i class="bi bi-bag-check me-1"></i> Campaign Profit</span>
                                    @else
                                        <span class="badge bg-danger"><i class="bi bi-arrow-up-right me-1"></i> Adopt Out</span>
                                    @endif
                                </td>
                                <td>{{ $entry->description }}</td>
                                <td>
                                    @if($entry->reference)
                                        <code class="fw-bold copy-badge" onclick="navigator.clipboard.writeText('{{ $entry->reference }}'); alert('Copied reference: {{ $entry->reference }}')" title="Click to copy">
                                            {{ $entry->reference }}
                                        </code>
                                    @else
                                        <span class="text-muted small">Auto-Generated</span>
                                    @endif
                                </td>
                                <td class="fw-bold {{ $entry->type === 'adopt_out' ? 'text-danger' : 'text-success' }}">
                                    {{ $entry->type === 'adopt_out' ? '-' : '+' }}KES {{ number_format($entry->amount, 2) }}
                                </td>
                                <td class="fw-bold">KES {{ number_format($entry->balance_after, 2) }}</td>
                                <td>
                                    @if($entry->receipt)
                                        <a href="{{ route('receipts.show', $entry->receipt->receipt_number) }}" target="_blank" class="badge bg-dark border text-decoration-none">
                                            <i class="bi bi-receipt me-1"></i> #{{ $entry->receipt->receipt_number }}
                                        </a>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">No kitty transactions recorded for this season yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Manual Kitty Donation Modal -->
        <div class="modal fade" id="donationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('backoffice.admin.kitty.donation') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title fw-bold">
                                <i class="bi bi-heart-fill text-danger me-2"></i> Record Donation to Adopt-a-Teen Kitty
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="p-3 rounded bg-dark border border-danger-subtle mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="small fw-bold text-danger"><i class="bi bi-phone-fill me-1"></i> M-Pesa Daraja Paybill</span>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Adopt-a-Teen Pool</span>
                                </div>
                                <div class="row g-2 small text-white">
                                    <div class="col-6">
                                        <span class="text-white-50 d-block" style="font-size: 10px;">PAYBILL NUMBER</span>
                                        <strong>{{ $mpesaSettings->paybill_number ?? '880100' }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-white-50 d-block" style="font-size: 10px;">ADOPT-A-TEEN ACCOUNT</span>
                                        <strong class="text-danger">{{ $mpesaSettings->adopt_account ?? 'ADOPT-A-TEEN' }}</strong>
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
                                <label class="form-label fw-semibold">M-Pesa Transaction Code / Bank Reference</label>
                                <input type="text" class="form-control text-uppercase" name="reference" placeholder="e.g. QA94XD8712">
                                <div class="form-text small">10-character code from MPESA SMS or cheque number.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Notes / Special Intention</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="e.g. Designated for high-school campers"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-camp-red btn-sm">Record Donation & Issue Receipt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reset Database Modal (PIN Protected) -->
        <div class="modal fade" id="resetDbModal" tabindex="-1" aria-labelledby="resetDbModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-danger shadow-lg bg-dark text-white">
                    <div class="modal-header border-danger">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded p-2 bg-danger-subtle text-danger">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-danger mb-0" id="resetDbModalLabel">Clean Database &amp; Start Afresh</h5>
                                <span class="small text-muted">Admin Account PIN Confirmation Required</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('backoffice.admin.reset-database') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <div class="alert alert-danger small mb-3 border border-danger">
                                <strong><i class="bi bi-shield-slash-fill me-1"></i> Full Database Cleanse:</strong><br>
                                This will permanently remove all registrations, the product catalogue, campaign sales, donations, receipts, and other user accounts. <strong>Only your system administrator account will be preserved</strong> so you can start completely afresh.
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">Confirm Your 4-Digit Admin PIN <span class="text-danger">*</span></label>
                                <input type="password" name="admin_pin" class="form-control bg-black text-white border-danger text-center fs-3 fw-bold" 
                                       maxlength="4" pattern="[0-9]{4}" inputmode="numeric" required placeholder="••••" style="letter-spacing: 8px;">
                                <div class="form-text text-muted small">Enter your administrator PIN (e.g. <code>1234</code>) to authorize this reset.</div>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-sm px-4 fw-bold">
                                <i class="bi bi-radioactive me-1"></i> Clean DB &amp; Start Afresh
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- M-Pesa Daraja Settings Modal (Paybill & Dual Accounts) -->
        <div class="modal fade" id="mpesaSettingsModal" tabindex="-1" aria-labelledby="mpesaSettingsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-success shadow-lg bg-dark text-white">
                    <div class="modal-header border-success">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded p-2 bg-success-subtle text-success">
                                <i class="bi bi-phone-fill fs-5"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold text-success mb-0" id="mpesaSettingsModalLabel">M-Pesa Daraja &amp; Paybill Accounts</h5>
                                <span class="small text-muted">Manage Paybill Shortcode &amp; Dual Destination Accounts</span>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('backoffice.admin.mpesa.settings') }}" method="POST">
                        @csrf
                        <div class="modal-body p-4">
                            <!-- Dual Destination Accounts Section -->
                            <div class="p-3 rounded bg-body-tertiary border border-secondary mb-4">
                                <h6 class="fw-bold text-white mb-2">
                                    <i class="bi bi-diagram-3-fill text-danger me-1"></i> Dual Paybill Destination Accounts
                                </h6>
                                <p class="text-white-50 small mb-3">
                                    Funds sent via M-Pesa STK Push are directed to separate account numbers to distinguish camper fee payments from benevolence sponsorships.
                                </p>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Paybill Shortcode <span class="text-danger">*</span></label>
                                        <input type="text" name="paybill_number" class="form-control bg-dark text-white border-secondary fw-bold" 
                                               value="{{ $mpesaSettings->paybill_number ?? '880100' }}" required placeholder="e.g. 880100">
                                        <div class="form-text text-muted small">Business Paybill Number</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">
                                            <i class="bi bi-mortarboard-fill text-danger me-1"></i> Camp Fees Account <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="camp_fee_account" class="form-control bg-dark text-white border-secondary fw-bold" 
                                               value="{{ $mpesaSettings->camp_fee_account ?? 'CAMP-FEES' }}" required placeholder="e.g. CAMP-FEES">
                                        <div class="form-text text-muted small">Where parent camper payments route</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">
                                            <i class="bi bi-heart-fill text-danger me-1"></i> Adopt-a-Teen Account <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="adopt_account" class="form-control bg-dark text-white border-secondary fw-bold" 
                                               value="{{ $mpesaSettings->adopt_account ?? 'ADOPT-A-TEEN' }}" required placeholder="e.g. ADOPT-A-TEEN">
                                        <div class="form-text text-muted small">Where sponsorships &amp; kitty funds route</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Safaricom Daraja API Credentials -->
                            <div class="p-3 rounded bg-body-tertiary border border-secondary mb-3">
                                <h6 class="fw-bold text-white mb-2 d-flex align-items-center justify-content-between">
                                    <span><i class="bi bi-key-fill text-success me-1"></i> Safaricom Daraja STK Push Credentials</span>
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> Coming Soon</span>
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Consumer Key</label>
                                        <input type="password" name="consumer_key" class="form-control bg-dark text-white border-secondary font-monospace" 
                                               value="{{ $mpesaSettings->consumer_key ?? '' }}" placeholder="Enter Daraja Consumer Key">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Consumer Secret</label>
                                        <input type="password" name="consumer_secret" class="form-control bg-dark text-white border-secondary font-monospace" 
                                               value="{{ $mpesaSettings->consumer_secret ?? '' }}" placeholder="Enter Daraja Consumer Secret">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Online Passkey (Lipa Na M-Pesa)</label>
                                        <input type="password" name="passkey" class="form-control bg-dark text-white border-secondary font-monospace" 
                                               value="{{ $mpesaSettings->passkey ?? '' }}" placeholder="Enter Passkey">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Environment</label>
                                        <select name="environment" class="form-select bg-dark text-white border-secondary">
                                            <option value="sandbox" {{ ($mpesaSettings->environment ?? '') === 'sandbox' ? 'selected' : '' }}>Sandbox (Test)</option>
                                            <option value="live" {{ ($mpesaSettings->environment ?? '') === 'live' ? 'selected' : '' }}>Production (Live)</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="is_mock_enabled" name="is_mock_enabled" value="1" {{ ($mpesaSettings->is_mock_enabled ?? true) ? 'checked' : '' }}>
                                            <label class="form-check-label small text-white" for="is_mock_enabled">
                                                <strong>Enable Development Simulation Mode:</strong> Allows instant STK prompt testing offline with realistic M-Pesa codes if Safaricom live API keys are not yet configured.
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Brevo Email Notification Relay Status & Test -->
                            <div class="p-3 rounded bg-dark border border-secondary mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="fw-bold text-white mb-0">
                                        <i class="bi bi-envelope-at-fill text-info me-1"></i> Brevo Email Notification Relay
                                    </h6>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Configured</span>
                                </div>
                                <p class="text-white-50 small mb-2" style="font-size: 12px;">
                                    Host: <code>smtp-relay.brevo.com:587</code> · Login: <code>b47b4b001@smtp-brevo.com</code>
                                </p>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="document.getElementById('form-test-brevo').submit();">
                                        <i class="bi bi-send-check-fill me-1"></i> Send Test Email
                                    </button>
                                    <a href="https://app.brevo.com/security/authorised_ips" target="_blank" class="text-muted small text-decoration-none" style="font-size: 11.5px;">
                                        <i class="bi bi-shield-lock me-1"></i> Brevo Authorised IPs
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-secondary">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success btn-sm px-4 fw-bold">
                                <i class="bi bi-check2-circle me-1"></i> Save M-Pesa Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <form id="form-test-brevo" action="{{ route('backoffice.admin.test-brevo-email') }}" method="POST" class="d-none">
            @csrf
        </form>

        {{-- ===== Teen Camper & Family Modals ===== --}}
        @if(isset($recentRegistrations))
            @foreach($recentRegistrations as $reg)
                @include('backoffice.partials.teen-family-modal', ['teen' => $reg->teen, 'allTeens' => $allTeens ?? null])
            @endforeach
        @endif
    @else
        <div class="alert alert-warning mt-4 p-4 text-center rounded-3 border-0 shadow-sm" style="background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.25) !important;">
            <div class="mb-2">
                <i class="bi bi-calendar-x text-warning fs-1"></i>
            </div>
            <h5 class="fw-bold text-white mb-2">No Camp Season Currently Set Up</h5>
            <p class="text-muted mb-3">There is no active camp season configured in the system. Create a season to start taking registrations and managing camp operations.</p>
            <a href="{{ route('backoffice.admin.seasons') }}" class="btn btn-danger px-4">
                <i class="bi bi-calendar-plus-fill me-1"></i> Go to Camp Seasons
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Tab switcher logic with persistent state
    function switchDashboardTab(tabId) {
        document.querySelectorAll('.dashboard-tab-pane').forEach(el => {
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
            'tab-queues': 'tabbtn-queues',
            'tab-campers': 'tabbtn-campers',
            'tab-financials': 'tabbtn-financials',
            'tab-demographics': 'tabbtn-demographics',
            'tab-activity': 'tabbtn-activity'
        };
        const activeBtn = document.getElementById(buttonMap[tabId]);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        // Save active tab so it never reverts on form submissions or page refresh
        try {
            localStorage.setItem('camp_admin_tab', tabId);
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, null, '#' + tabId);
            }
        } catch(e) {}
    }

    // Auto-restore active tab on page load
    document.addEventListener('DOMContentLoaded', function() {
        let initialTab = 'tab-queues';
        if (window.location.hash && document.getElementById(window.location.hash.substring(1))) {
            initialTab = window.location.hash.substring(1);
        } else {
            const saved = localStorage.getItem('camp_admin_tab');
            if (saved && document.getElementById(saved)) {
                initialTab = saved;
            }
        }
        switchDashboardTab(initialTab);
    });

    // Live filter for Camper Roster Table
    function filterCamperRows(query) {
        const q = query.toLowerCase().trim();
        document.querySelectorAll('.camper-data-row').forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            if (q === '' || searchData.includes(q)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Filter Activity Stream Rows by Type
    function filterActivityRows(type, chipElement) {
        document.querySelectorAll('.notif-filter-chip').forEach(c => c.classList.remove('active'));
        chipElement.classList.add('active');

        document.querySelectorAll('.activity-row').forEach(row => {
            const rowType = row.getAttribute('data-type');
            if (type === 'all' || rowType === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Filter Kitty Ledger Rows by Type
    function filterLedgerRows(type, chipElement) {
        document.querySelectorAll('.camp-filter-chip:not(.notif-filter-chip)').forEach(c => c.classList.remove('active'));
        chipElement.classList.add('active');

        document.querySelectorAll('.ledger-row').forEach(row => {
            const rowType = row.getAttribute('data-type');
            if (type === 'all' || rowType === type) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endpush
