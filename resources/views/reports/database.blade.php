<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Camper Database Search Export Report — Church Teen Camp</title>
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
        <a href="{{ route('backoffice.admin.database') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Return to Database Search
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
                    <div class="text-danger fw-bold text-uppercase small" style="letter-spacing: 1px;">Camper Database Query &amp; Search Export</div>
                    <div class="text-muted small">
                        Search query: <strong>"{{ $searchQuery ?: 'All Records' }}"</strong> &bull; 
                        Status: <strong>{{ ucfirst($filterStatus ?: 'All') }}</strong> &bull; 
                        Gender: <strong>{{ ucfirst($filterGender ?: 'All') }}</strong>
                    </div>
                </div>
            </div>
            <div class="text-end small text-muted">
                <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                <div><strong>Audited By:</strong> {{ $generatedBy }}</div>
                <div><strong>Results:</strong> <span class="badge bg-danger text-white">{{ $registrations->count() }} Matches</span></div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-report mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Camper Details</th>
                        <th>Camp Season</th>
                        <th>Gender</th>
                        <th>Parent / Guardian</th>
                        <th>Phone Carried</th>
                        <th>Medical Alerts</th>
                        <th>Fee Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $idx => $reg)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <strong>{{ $reg->teen?->name ?? 'Camper' }}</strong><br>
                                <small class="text-muted">{{ $reg->teen?->email }}</small>
                            </td>
                            <td><span class="badge bg-secondary">{{ $reg->campSeason?->name ?? 'Season' }}</span></td>
                            <td>{{ ucfirst($reg->teen?->gender ?? '-') }}</td>
                            <td>
                                <div>{{ $reg->emergency_contact_name ?? ($reg->teen?->parents->first()?->name ?? 'Parent') }}</div>
                                <small class="text-muted">{{ $reg->emergency_contact_phone ?? ($reg->teen?->parents->first()?->phone ?? '') }}</small>
                            </td>
                            <td>
                                @if($reg->phone_carried)
                                    <span class="badge bg-warning text-dark">Yes</span>
                                @else
                                    <span class="badge bg-light text-muted border">No</span>
                                @endif
                            </td>
                            <td style="max-width: 150px;">
                                <small class="{{ $reg->medical_conditions && $reg->medical_conditions !== 'None' ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ $reg->medical_conditions ?: 'None' }}
                                </small>
                            </td>
                            <td class="text-end fw-bold text-success">KES {{ number_format($reg->total_paid, 2) }}</td>
                            <td class="text-end fw-bold {{ $reg->balance_remaining > 0 ? 'text-danger' : 'text-success' }}">
                                KES {{ number_format($reg->balance_remaining, 2) }}
                            </td>
                            <td>
                                <span class="badge {{ $reg->status === 'signed_in' ? 'bg-success' : 'bg-primary' }}">
                                    {{ ucfirst(str_replace('_', ' ', $reg->status)) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">No records match the selected database search criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center text-muted small">
            <div>Church Teen Camp Management System &bull; Database Query Export Record</div>
            <div>Admin Verification: __________________________</div>
        </div>
    </div>

</body>
</html>
