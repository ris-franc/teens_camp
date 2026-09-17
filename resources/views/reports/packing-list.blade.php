<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Camp Packing Checklist — {{ $season ? $season->name : 'Teen Camp 2026' }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f4f6f9;
            color: #1a1a1a;
            padding: 24px 0;
            font-size: 13px;
        }
        .report-toolbar {
            max-width: 900px;
            margin: 0 auto 16px;
        }
        .packing-sheet {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px 48px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            border-radius: 8px;
        }
        .report-header-title {
            font-family: 'Cinzel', Georgia, serif;
            letter-spacing: .5px;
            color: #111827;
        }
        .category-header {
            background-color: #f8fafc;
            border-left: 4px solid #dc2626;
            padding: 8px 14px;
            font-weight: 700;
            font-size: 13px;
            color: #1f2937;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 18px;
            margin-bottom: 10px;
        }
        .checklist-item {
            padding: 7px 10px;
            border-bottom: 1px dashed #e5e7eb;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .checklist-box {
            width: 17px;
            height: 17px;
            border: 2px solid #9ca3af;
            border-radius: 3px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .essential-badge {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .guidelines-box {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 6px;
            padding: 14px 18px;
            margin-top: 24px;
            font-size: 12px;
        }
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                font-size: 11px !important;
                color: #000000 !important;
            }
            .report-toolbar {
                display: none !important;
            }
            .packing-sheet {
                box-shadow: none !important;
                padding: 0 !important;
                max-width: 100% !important;
                border-radius: 0 !important;
            }
            .category-header {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .essential-badge {
                background-color: #fee2e2 !important;
                color: #b91c1c !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .guidelines-box {
                background-color: #fef3c7 !important;
                border-color: #f59e0b !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .page-break-avoid {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Controls Toolbar (Hidden in Print) -->
    <div class="report-toolbar d-flex align-items-center justify-content-between">
        <div>
            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Portal
            </a>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-danger btn-sm shadow-sm px-3">
                <i class="bi bi-printer-fill me-1"></i> Print / Download PDF Checklist
            </button>
        </div>
    </div>

    <!-- Printable Sheet -->
    <div class="packing-sheet">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
            <div class="d-flex align-items-center gap-3">
                @if(file_exists(public_path('images/church-logo.jpg')))
                    <img src="{{ asset('images/church-logo.jpg') }}" alt="Church Logo" style="height: 52px; object-fit: contain;">
                @endif
                <div>
                    <h3 class="report-header-title fw-black mb-0 text-uppercase" style="font-size: 1.4rem;">
                        {{ $season ? $season->name : 'Teen Camp 2026' }}
                    </h3>
                    <div class="text-muted small">
                        Official Camper Packing & Preparation Checklist
                    </div>
                </div>
            </div>
            <div class="text-end">
                <span class="badge bg-danger text-uppercase px-2 py-1 mb-1">Official Checklist</span>
                <div class="small text-muted">
                    @if($season && $season->start_date)
                        Camp: {{ \Carbon\Carbon::parse($season->start_date)->format('M d') }} - {{ \Carbon\Carbon::parse($season->end_date)->format('M d, Y') }}
                    @else
                        Season {{ date('Y') }}
                    @endif
                </div>
                @if($season && $season->venue)
                    <div class="small text-muted"><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $season->venue }}</div>
                @endif
            </div>
        </div>

        <!-- Camper Identity Strip -->
        <div class="row g-2 p-3 bg-light rounded border mb-4">
            <div class="col-md-6">
                <strong>Camper Name:</strong> 
                <span class="text-decoration-underline ms-1">
                    {{ $camperName ?? '________________________________________' }}
                </span>
            </div>
            <div class="col-md-3">
                <strong>Cabin / Group:</strong> 
                <span class="text-decoration-underline ms-1">_________________</span>
            </div>
            <div class="col-md-3 text-md-end text-muted small">
                Printed: {{ now()->format('M d, Y') }}
            </div>
        </div>

        <!-- Packing Items by Category -->
        @if($categories->count() > 0)
            @foreach($categories as $category => $items)
                <div class="page-break-avoid mb-3">
                    <div class="category-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-tag-fill text-danger me-2"></i>{{ $category }}</span>
                        <span class="badge bg-white text-dark border small fw-normal">{{ $items->count() }} items</span>
                    </div>

                    <div class="row g-0">
                        @foreach($items as $item)
                            <div class="col-md-6">
                                <div class="checklist-item me-md-2">
                                    <div class="checklist-box"></div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                            <span class="fw-semibold text-dark">{{ $item->item_name }}</span>
                                            @if($item->is_essential)
                                                <span class="essential-badge">Mandatory</span>
                                            @endif
                                        </div>
                                        @if($item->notes)
                                            <div class="text-muted" style="font-size: 11px; margin-top: 1px;">
                                                {{ $item->notes }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @else
            <div class="text-center p-5 border rounded text-muted">
                <p class="mb-0">No packing items currently listed for this season.</p>
            </div>
        @endif

        <!-- Important Guidelines & Prohibited Items Box -->
        <div class="guidelines-box page-break-avoid">
            <div class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill text-warning fs-6"></i>
                Important Camp Preparation & Packing Rules:
            </div>
            <ul class="mb-0 ps-3 text-muted">
                <li><strong>Clear Labeling:</strong> Please label all bags, clothing items, Bible, and bedding with the camper's full name using permanent marker.</li>
                <li><strong>Prescription Medication:</strong> Must be in original pharmacy packaging with dosage instructions and handed directly to the Camp Nurse at the Intake Gate Desk.</li>
                <li><strong>Prohibited Items:</strong> Weapons, matches/lighters, vape pens, electronic gaming devices, and non-prescribed drugs are strictly prohibited on camp grounds.</li>
                <li><strong>Valuables:</strong> Camp administration is not liable for lost jewelry, expensive electronics, or high-denomination cash. Keep belongings minimal and focused on fellowship.</li>
            </ul>
        </div>

        <!-- Footer Signature -->
        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top text-muted small">
            <div>
                Parent / Guardian Signature: _________________________________
            </div>
            <div>
                Date Packed: ____________________
            </div>
        </div>
    </div>

</body>
</html>
