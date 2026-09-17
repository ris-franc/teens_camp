@extends('layouts.backoffice')

@section('title', 'Registration Confirmation')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="camp-card p-4 p-md-5 border-success shadow" id="printable-confirmation">
                <!-- Header -->
                <div class="text-center mb-4 border-bottom pb-4">
                    <div class="d-inline-flex p-3 rounded-circle bg-success text-white mb-2 shadow">
                        <i class="bi bi-check2-circle fs-2"></i>
                    </div>
                    <h3 class="fw-bold text-uppercase text-success mb-1">Registration Successful!</h3>
                    <p class="text-muted small mb-0">{{ $season->name }} | Official Desk Confirmation Slip</p>
                </div>

                <!-- Camper & Parent Details -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="p-3 rounded border" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                            <span class="text-muted small text-uppercase fw-bold"><i class="bi bi-person-fill text-danger me-1"></i> Teen Camper</span>
                            <h5 class="fw-bold mt-1 mb-1">{{ $data['teen']->name }}</h5>
                            <div class="small text-muted">Email: <strong>{{ $data['teen']->email }}</strong></div>
                            <div class="small text-muted">Status: <span class="badge bg-danger">Registered</span></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded border" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                            <span class="text-muted small text-uppercase fw-bold"><i class="bi bi-person-heart text-danger me-1"></i> Parent / Guardian</span>
                            <h5 class="fw-bold mt-1 mb-1">{{ $data['parent']->name }}</h5>
                            <div class="small text-muted">Email: <strong>{{ $data['parent']->email }}</strong></div>
                            <div class="small text-muted">Phone: {{ $data['parent']->phone }}</div>
                        </div>
                    </div>
                </div>

                <!-- Account Credentials & First-Login Instructions -->
                <div class="alert alert-dark border border-danger mb-4">
                    <h6 class="fw-bold text-danger mb-2">
                        <i class="bi bi-key-fill me-1"></i> Participant Account Login Instructions
                    </h6>
                    <p class="small mb-2">Both Teen and Parent accounts have been initialized. To access their dashboards:</p>
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <div class="p-2 border rounded bg-body">
                                <strong>Login Credential:</strong> Email Address<br>
                                <strong>Default PIN:</strong> <span class="badge bg-danger fs-6">0000</span>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-2 border rounded bg-body">
                                <strong>Security Protocol:</strong><br>
                                First login will immediately prompt user to set their confidential 4-digit PIN.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Receipt Section if payment was made -->
                @if($data['paymentReceipt'])
                    <div class="border rounded p-3 mb-4" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-bold text-uppercase small text-muted mb-0"><i class="bi bi-receipt text-danger me-1"></i> Payment Receipt</h6>
                            <span class="badge bg-success">Paid at Desk</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <span class="fs-4 fw-bold text-danger">KES {{ number_format($data['paymentReceipt']->amount, 2) }}</span>
                            <span class="small text-muted">Receipt #<strong>{{ $data['paymentReceipt']->receipt_number }}</strong></span>
                        </div>
                        <div class="mt-2 text-end">
                            <a href="{{ route('receipts.show', $data['paymentReceipt']->receipt_number) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-printer me-1"></i> View & Print Receipt
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                    <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-plus-circle me-1"></i> Register Another Camper
                    </a>
                    <button type="button" class="btn btn-camp-red" onclick="window.print()">
                        <i class="bi bi-printer-fill me-1"></i> Print Confirmation Slip
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
