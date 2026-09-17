<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Campaign Sales &amp; Merchandise Profit Report — {{ $season ? $season->name : 'Teen Camp' }}</title>
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
        <a href="{{ route('backoffice.campaign.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Return to Campaign Dashboard
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
                    <div class="text-danger fw-bold text-uppercase small" style="letter-spacing: 1px;">Campaign Merchandise Sales &amp; Profit Report</div>
                    <div class="text-muted small">{{ $season ? $season->name : 'Active Season' }} &bull; Adopt-a-Teen Funding Stream</div>
                </div>
            </div>
            <div class="text-end small text-muted">
                <div><strong>Generated:</strong> {{ $generatedAt }}</div>
                <div><strong>Audited By:</strong> {{ $generatedBy }}</div>
                <div><strong>Status:</strong> <span class="badge bg-success text-white">AUDITED RECORD</span></div>
            </div>
        </div>

        <!-- Summary KPIs -->
        <div class="row g-2 mb-4 text-center">
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small text-uppercase fw-bold">Total Units Sold</div>
                    <div class="fs-3 fw-bold text-dark">{{ number_format($totalUnits) }} items</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small text-uppercase fw-bold">Gross Sales Revenue</div>
                    <div class="fs-3 fw-bold text-primary">KES {{ number_format($totalRevenue, 2) }}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 border rounded bg-light">
                    <div class="text-muted small text-uppercase fw-bold">Net Profit Transferred to Kitty</div>
                    <div class="fs-3 fw-bold text-success">KES {{ number_format($totalProfit, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Sales Ledger Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-report mb-0">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Sale Date</th>
                        <th>Product / Item</th>
                        <th>Weekly Batch</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Gross Revenue</th>
                        <th>Net Profit</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $idx => $sale)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>{{ $sale->created_at ? $sale->created_at->format('M d, Y • h:i A') : 'N/A' }}</td>
                            <td><strong>{{ $sale->product?->name ?? 'Merchandise' }}</strong></td>
                            <td><span class="badge bg-secondary">{{ $sale->batch?->week_label ?? 'General' }}</span></td>
                            <td class="text-center fw-bold">{{ $sale->quantity }}</td>
                            <td class="text-end">KES {{ number_format($sale->product?->unit_price ?? 0, 2) }}</td>
                            <td class="text-end fw-bold text-primary">KES {{ number_format($sale->total_amount, 2) }}</td>
                            <td class="text-end fw-bold text-success">KES {{ number_format($sale->total_profit, 2) }}</td>
                            <td>{{ $sale->seller?->name ?? 'Staff' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No merchandise sales records found for this season.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="fw-bold table-light">
                        <td colspan="4" class="text-end text-uppercase">Totals:</td>
                        <td class="text-center">{{ number_format($totalUnits) }}</td>
                        <td></td>
                        <td class="text-end text-primary">KES {{ number_format($totalRevenue, 2) }}</td>
                        <td class="text-end text-success">KES {{ number_format($totalProfit, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center text-muted small">
            <div>Church Teen Camp Management System &bull; Campaign Fundraising Audit Trail</div>
            <div>Campaign Head Signature: __________________________</div>
        </div>
    </div>

</body>
</html>
