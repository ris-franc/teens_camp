@extends($isStaff ? 'layouts.backoffice' : 'layouts.app')

@section('title', 'Official Receipt #' . $receipt->receipt_number)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <!-- Navigation Back Bar -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                @if($isStaff)
                    <a href="{{ route('backoffice.admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Return to Admin Dashboard
                    </a>
                @else
                    <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Return to Parent Dashboard
                    </a>
                @endif
                <button type="button" class="btn btn-camp-red btn-sm" onclick="window.print()">
                    <i class="bi bi-printer-fill me-1"></i> Print / Save Receipt
                </button>
            </div>

            <div class="camp-card p-4 p-md-5 border-danger shadow position-relative">
                <!-- Receipt Top Accent Header -->
                <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4 flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ asset('images/church-logo.jpg') }}" alt="Church Logo" class="rounded bg-white p-1" style="height: 52px; width: auto; object-fit: contain; box-shadow: 0 0 10px rgba(220,53,69,0.25);">
                        <div>
                            <div class="fw-bold fs-4 text-white">TEEN<span class="text-danger">CAMP</span></div>
                            <div class="text-muted small mb-0">{{ $receipt->campSeason ? $receipt->campSeason->name : 'Teen Camp Management' }}</div>
                            <div class="text-muted small">{{ $receipt->campSeason ? $receipt->campSeason->venue : 'Church Campsite' }}</div>
                        </div>
                    </div>

                    <div class="text-end">
                        <span class="badge bg-danger text-uppercase px-3 py-2 letter-spacing-1">Official Camp Receipt</span>
                        <div class="fs-5 fw-bold text-danger mt-2">#{{ $receipt->receipt_number }}</div>
                        <div class="small text-muted">{{ $receipt->created_at->format('M d, Y • h:i A') }}</div>
                    </div>
                </div>

                <!-- Receipt Body & Amount -->
                <div class="mb-4">
                    <div class="camp-fee-panel-card mb-4 p-3 rounded border" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="text-muted small text-uppercase fw-bold">Amount Paid (Confirmed)</span>
                                <h2 class="fw-black text-danger mt-1 mb-0">KES {{ number_format($receipt->amount, 2) }}</h2>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success text-white fs-6 px-3 py-2">
                                    <i class="bi bi-patch-check-fill me-1"></i> Confirmed & Audited
                                </span>
                            </div>
                        </div>
                        <div class="small text-muted mt-2 border-top pt-2">{{ $receipt->description }}</div>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-sm-6">
                            <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Payment Method:</span>
                            <strong>{{ $receipt->meta_data['payment_method'] ?? 'M-Pesa' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">M-Pesa Reference Code:</span>
                            <strong class="text-danger fs-6">{{ $receipt->meta_data['reference'] ?? ($receipt->meta_data['mpesa_code'] ?? 'CONFIRMED') }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Issued To / Payer:</span>
                            <strong>{{ $receipt->user ? $receipt->user->name : 'Church Camp Accounting' }}</strong>
                            @if($receipt->user && $receipt->user->phone)
                                <div class="text-muted" style="font-size: 11px;">Phone: {{ $receipt->user->phone }}</div>
                            @endif
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted d-block text-uppercase fw-bold" style="font-size: 11px;">Camper Beneficiary:</span>
                            <strong class="text-white">{{ $receipt->meta_data['teen_name'] ?? 'Registered Camper' }}</strong>
                        </div>

                        @if($receipt->meta_data)
                            <div class="col-12 mt-3 pt-3 border-top">
                                <span class="text-muted d-block mb-1 text-uppercase fw-bold" style="font-size: 11px;">Audit & Transaction Breakdown:</span>
                                <div class="p-2 rounded bg-body-tertiary small border">
                                    @foreach($receipt->meta_data as $k => $v)
                                        @if(!in_array($k, ['registration_id', 'teen_id', 'parent_id']))
                                            <div><strong>{{ ucfirst(str_replace('_', ' ', $k)) }}:</strong> {{ is_array($v) ? json_encode($v) : $v }}</div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Footer & Official Verification -->
                <div class="pt-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="small text-muted">
                        <i class="bi bi-shield-lock-fill text-danger me-1"></i> Cryptographically logged in Church Teens Camp 2026 database.
                    </div>
                    <button type="button" class="btn btn-camp-red btn-sm" onclick="window.print()">
                        <i class="bi bi-printer-fill me-1"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
