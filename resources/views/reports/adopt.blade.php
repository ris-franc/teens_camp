<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Adopt-a-Teen Sponsorship &amp; Kitty Ledger Report — {{ $season ? $season->name : 'Teen Camp' }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f4f6f9; color: #1a1a1a; padding: 20px 0; font-size: 13px; }
        .report-sheet { max-width: 1050px; margin: 0 auto; background: #ffffff; padding: 35px 40px; box-shadow: 0 4px 20px rgba(0,0,0,.08); border-radius: 8px; }
        .report-title { font-family: 'Cinzel', Georgia, serif; letter-spacing: .5px; color: #111827; }
        .table-report th { background-color: #1f2937 !important; color: #ffffff !important; font-size: 11px; text-transform: uppercase; letter-spacing: .6px; padding: 8px 10px; }
        .table-report td { padding: 8px 10px; vertical-align: middle; border-color: #e5e7eb; }
        .report-toolbar { max-width: 1050px; margin: 0 auto 15px; }
        @media print {
            body { background: #ffffff !important; padding: 0 !important; font-size: 11px !important; }
            .report-sheet { box-shadow: none !important; padding: 0 !important; max-width: 100% !important; border-radius: 0 !important; }
            .report-toolbar { display: none !important; }
            @page { size: A4 landscape; margin: 10mm; }
        }
    </style>
</head>
<body>

    <div class="report-toolbar d-flex justify-content-between align-items-center">
        <a href="{{ route('backoffice.adopt.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Return to Adopt Portal
        </a>
        <button onclick="window.print()" class="btn btn-danger btn-sm px-4 fw-bold shadow-sm">
            <i class="bi bi-printer-fill me-1"></i> Print / Download PDF Report
        </button>
    </div>

    <div class="report-sheet">
        <!-- Letterhead -->
        <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ asset('images/church-logo.jpg') }}" alt="Church Logo" style="height: 64px; width: auto; object-fit: contain;" class="border p-1 rounded">
                <div>
                    <h3 class="report-title fw-bold mb-0">CHURCH TEEN CAMP MANAGEMENT</h3>
                    <div class="text-danger fw-bold text-uppercase small" style="letter-spacing: 1px;">Adopt-a-Teen Sponsorship &amp; Kitty Ledger Report</div>
                    <div class="text-muted small">{{ $season ? $season->name : 'Active Season' }} &bull; Church Scholarship Pool</div>
                </div>
            </div>
            <div class="text-end small text-muted">
                <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                <div><strong>Audited By:</strong> {{ $generatedBy }}</div>
                <div><strong>Status:</strong> <span class="badge bg-success text-white">PASTORAL AUDIT</span></div>
            </div>
        </div>

        <!-- Summary KPIs -->
        <div class="row g-2 mb-4 text-center">
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small text-uppercase fw-bold">Available Kitty Balance</div>
                    <div class="fs-3 fw-bold text-success">KES {{ number_format($kittyBalance, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small text-uppercase fw-bold">Total Donations In</div>
                    <div class="fs-3 fw-bold text-primary">KES {{ number_format($totalDonationsIn, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small text-uppercase fw-bold">Disbursed for Campers</div>
                    <div class="fs-3 fw-bold text-danger">KES {{ number_format($totalDisbursedOut, 2) }}</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small text-uppercase fw-bold">Camper Requests</div>
                    <div class="fs-3 fw-bold text-dark">{{ $requests->count() }} applicants</div>
                </div>
            </div>
        </div>

        <!-- Sponsorship Applications Section -->
        <h5 class="fw-bold mb-2 text-dark"><i class="bi bi-people-fill text-danger me-1"></i> Camper Assistance Applications</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-report mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Camper Name</th>
                        <th>Parent / Applicant</th>
                        <th>Amount Requested</th>
                        <th>Application Reason / Narrative</th>
                        <th>Status</th>
                        <th>Submitted</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $idx => $req)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $req->teen?->name ?? 'Camper' }}</strong></td>
                            <td>{{ $req->parent?->name ?? 'Parent' }} ({{ $req->parent?->phone }})</td>
                            <td class="text-end fw-bold text-danger">KES {{ number_format($req->amount_requested, 2) }}</td>
                            <td style="max-width: 300px;"><small class="text-muted">{{ $req->reason }}</small></td>
                            <td>
                                @if($req->status === 'approved')
                                    <span class="badge bg-success">Approved &amp; Disbursed</span>
                                @elseif($req->status === 'approved-awaiting-funds')
                                    <span class="badge bg-warning text-dark">Awaiting Kitty Funds</span>
                                @elseif($req->status === 'denied')
                                    <span class="badge bg-secondary">Denied</span>
                                @else
                                    <span class="badge bg-primary">Pending Review</span>
                                @endif
                            </td>
                            <td>{{ $req->created_at ? $req->created_at->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">No sponsorship requests submitted for this season.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Kitty Transactions Ledger Section -->
        <h5 class="fw-bold mb-2 text-dark"><i class="bi bi-journal-text text-danger me-1"></i> Kitty Fund Transactions Ledger</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-report mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description / Donor</th>
                        <th>Reference Code</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ledger as $idx => $item)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $item->created_at ? $item->created_at->format('M d, Y • h:i A') : 'N/A' }}</td>
                            <td>
                                @if($item->type === 'donation_in')
                                    <span class="badge bg-success">Donation In</span>
                                @elseif($item->type === 'campaign_profit_in')
                                    <span class="badge bg-primary">Campaign Profit In</span>
                                @else
                                    <span class="badge bg-danger">Disbursement Out</span>
                                @endif
                            </td>
                            <td>{{ $item->description }}</td>
                            <td><code>{{ $item->reference ?: 'N/A' }}</code></td>
                            <td class="text-end fw-bold {{ in_array($item->type, ['donation_in', 'campaign_profit_in']) ? 'text-success' : 'text-danger' }}">
                                {{ in_array($item->type, ['donation_in', 'campaign_profit_in']) ? '+' : '-' }} KES {{ number_format($item->amount, 2) }}
                            </td>
                            <td class="text-end fw-bold">KES {{ number_format($item->balance_after, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3 text-muted">No transactions found in kitty ledger.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center text-muted small">
            <div>Church Teen Camp Management System &bull; Adopt-a-Teen Benevolence Audit</div>
            <div>Pastoral Committee Signature: __________________________</div>
        </div>
    </div>

</body>
</html>
