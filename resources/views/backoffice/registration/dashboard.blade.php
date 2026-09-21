@extends('layouts.backoffice')

@section('title', 'Registration Desk Operations')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Registration Desk Operations</h3>
            <p class="text-muted small mb-0">Season: <strong class="text-white">{{ $season ? $season->name : 'None' }}</strong></p>
        </div>
        <div class="d-grid d-sm-flex gap-2 w-100 w-md-auto">
            <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-camp-red btn-sm d-flex align-items-center justify-content-center gap-1 py-2 px-3">
                <i class="bi bi-person-plus-fill me-1"></i> New 2-Min Desk Intake
            </a>
            <a href="{{ route('backoffice.registration.signin') }}" class="btn btn-camp-dark btn-sm d-flex align-items-center justify-content-center gap-1 py-2 px-3 border border-secondary border-opacity-25">
                <i class="bi bi-check2-circle me-1"></i> Camp-Day Sign-In
            </a>
        </div>
    </div>

    <!-- Quick Action Cards (Responsive 2x2 grid on mobile, 4-col on desktop) -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('backoffice.registration.desk') }}" class="text-decoration-none">
                <div class="camp-card p-3 p-md-4 text-center h-100 border-danger d-flex flex-column justify-content-center transition-transform">
                    <div class="d-inline-flex rounded-circle bg-danger text-white mb-2 mx-auto shadow-sm align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-person-plus fs-5"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-white">Desk Intake</h6>
                    <p class="text-muted small mb-0 d-none d-sm-block">Quick 2-minute registration creating parent &amp; teen accounts.</p>
                    <span class="badge bg-danger bg-opacity-25 text-danger d-sm-none mt-1" style="font-size: 10px;">Express Intake</span>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('backoffice.registration.signin') }}" class="text-decoration-none">
                <div class="camp-card p-3 p-md-4 text-center h-100 d-flex flex-column justify-content-center transition-transform">
                    <div class="d-inline-flex rounded-circle bg-success text-white mb-2 mx-auto shadow-sm align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-check2-square fs-5"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-white">Camp Sign-In</h6>
                    <p class="text-muted small mb-0 d-none d-sm-block">1-click check-in for arriving teens at the campsite.</p>
                    <span class="badge bg-success bg-opacity-25 text-success d-sm-none mt-1" style="font-size: 10px;">Arrival Desk</span>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('backoffice.forms.index') }}" class="text-decoration-none">
                <div class="camp-card p-3 p-md-4 text-center h-100 d-flex flex-column justify-content-center transition-transform">
                    <div class="d-inline-flex rounded-circle bg-dark text-danger border border-danger mb-2 mx-auto shadow-sm align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-file-earmark-diff fs-5"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-white">Form Builder</h6>
                    <p class="text-muted small mb-0 d-none d-sm-block">Build dynamic forms with parent sign-off oversight.</p>
                    <span class="badge bg-secondary bg-opacity-25 text-light d-sm-none mt-1" style="font-size: 10px;">Forms &amp; Waivers</span>
                </div>
            </a>
        </div>

        <div class="col-6 col-md-3">
            <a href="{{ route('backoffice.packing.index') }}" class="text-decoration-none">
                <div class="camp-card p-3 p-md-4 text-center h-100 d-flex flex-column justify-content-center transition-transform">
                    <div class="d-inline-flex rounded-circle bg-dark text-white border mb-2 mx-auto shadow-sm align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-backpack fs-5"></i>
                    </div>
                    <h6 class="fw-bold mb-1 text-white">Packing Lists</h6>
                    <p class="text-muted small mb-0 d-none d-sm-block">Manage seasonal checklist &amp; release status.</p>
                    <span class="badge bg-secondary bg-opacity-25 text-light d-sm-none mt-1" style="font-size: 10px;">Checklists</span>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Registrations Card -->
    <div class="camp-card p-3 p-md-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 border-bottom pb-3">
            <h5 class="fw-bold mb-0 fs-6 fs-md-5">
                <i class="bi bi-clock-history text-danger me-2"></i> Recent Registrations <span class="d-none d-sm-inline">(Current Season)</span>
            </h5>
            <span class="badge bg-danger">{{ $totalRegistered }} Total Campers</span>
        </div>

        {{-- Desktop / Tablet Table View --}}
        <div class="d-none d-md-block table-responsive">
            <table class="table table-camp align-middle mb-0">
                <thead>
                    <tr>
                        <th>Camper</th>
                        <th>Parent</th>
                        <th>Registered Date</th>
                        <th>Payment Status</th>
                        <th>Check-In Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRegistrations as $reg)
                        <tr>
                            <td>
                                <a href="javascript:void(0)" class="fw-bold text-white text-decoration-none text-hover-danger d-inline-flex align-items-center gap-1"
                                   data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}"
                                   title="Click to view parent info and siblings">
                                    {{ $reg->teen->name }}
                                    <i class="bi bi-people-fill text-danger small" style="font-size:11px;"></i>
                                </a>
                                <div class="small text-muted">{{ $reg->teen->email }}</div>
                            </td>
                            <td>
                                @if($reg->teen->parents->count() > 0)
                                    <div>{{ $reg->teen->parents->first()->name }}</div>
                                    <div class="small text-muted">{{ $reg->teen->parents->first()->phone ?: $reg->teen->parents->first()->email }}</div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>{{ $reg->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <div class="small fw-bold">KES {{ number_format($reg->total_paid, 2) }} / KES {{ number_format($season->price, 2) }}</div>
                                <div class="camp-progress mt-1" style="height: 5px; width: 100px;">
                                    <div class="camp-progress-bar" style="width: {{ $reg->payment_percent }}%;"></div>
                                </div>
                            </td>
                            <td>
                                @if($reg->status === 'signed_in')
                                    <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Checked In</span>
                                @elseif($reg->status === 'withdrawn')
                                    <span class="badge bg-secondary">Withdrawn</span>
                                @else
                                    <span class="badge bg-danger">Registered</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-outline-info btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}"
                                            title="View parent info, siblings, and family details">
                                        <i class="bi bi-people-fill me-1"></i> Family
                                    </button>
                                    <a href="{{ route('backoffice.registration.edit', $reg) }}" class="btn btn-camp-red btn-sm px-3">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No registrations recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card Feed (Phones < 768px) --}}
        <div class="d-md-none d-flex flex-column gap-3">
            @forelse($recentRegistrations as $reg)
                <div class="mobile-data-card">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                  style="width: 36px; height: 36px; font-size: 13px;">
                                {{ strtoupper(substr($reg->teen->name, 0, 1)) }}
                            </span>
                            <div>
                                <a href="javascript:void(0)" class="fw-bold text-white text-decoration-none d-inline-flex align-items-center gap-1"
                                   data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}">
                                    {{ $reg->teen->name }}
                                    <i class="bi bi-people-fill text-danger small"></i>
                                </a>
                                <div class="text-muted" style="font-size: 11px;">
                                    <span class="badge bg-dark border border-secondary text-uppercase py-0 px-1">{{ $reg->teen->gender ?: 'Camper' }}</span>
                                    {{ $reg->teen->email }}
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($reg->status === 'signed_in')
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> In</span>
                            @elseif($reg->status === 'withdrawn')
                                <span class="badge bg-secondary">Withdrawn</span>
                            @else
                                <span class="badge bg-danger">Registered</span>
                            @endif
                        </div>
                    </div>

                    {{-- Parent Contact --}}
                    <div class="p-2 rounded mb-2" style="background-color: rgba(255, 255, 255, 0.03); font-size: 12px;">
                        <div class="text-muted small">Parent / Guardian:</div>
                        @if($reg->teen->parents->count() > 0)
                            @php $p = $reg->teen->parents->first(); @endphp
                            <div class="fw-semibold text-white">{{ $p->name }}</div>
                            @if($p->phone)
                                <a href="tel:{{ $p->phone }}" class="text-info text-decoration-none d-inline-flex align-items-center gap-1 mt-1">
                                    <i class="bi bi-telephone-fill"></i> {{ $p->phone }}
                                </a>
                            @endif
                        @else
                            <span class="text-muted">None recorded</span>
                        @endif
                    </div>

                    {{-- Payment Progress --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between text-muted" style="font-size: 11px;">
                            <span>KES {{ number_format($reg->total_paid, 2) }} / {{ number_format($season->price, 2) }}</span>
                            <span class="fw-bold text-white">{{ $reg->payment_percent }}%</span>
                        </div>
                        <div class="camp-progress mt-1" style="height: 6px;">
                            <div class="camp-progress-bar" style="width: {{ $reg->payment_percent }}%;"></div>
                        </div>
                    </div>

                    {{-- Card Actions --}}
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-info btn-sm flex-fill d-flex align-items-center justify-content-center gap-1 py-2"
                                data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}">
                            <i class="bi bi-people-fill"></i> Family
                        </button>
                        <a href="{{ route('backoffice.registration.edit', $reg) }}" class="btn btn-camp-red btn-sm flex-fill d-flex align-items-center justify-content-center gap-1 py-2">
                            <i class="bi bi-pencil-square"></i> Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                    No registrations recorded yet.
                </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ===== Teen Camper & Family Modals ===== --}}
@php
    $modalTeens = collect();
    foreach($recentRegistrations as $reg) {
        if ($reg->teen && !$modalTeens->contains('id', $reg->teen->id)) {
            $modalTeens->push($reg->teen);
            foreach($reg->teen->siblings() as $sib) {
                if (!$modalTeens->contains('id', $sib->id)) {
                    $modalTeens->push($sib);
                }
            }
        }
    }
@endphp
@foreach($modalTeens as $teen)
    @include('backoffice.partials.teen-family-modal', ['teen' => $teen, 'allTeens' => $allTeens ?? null])
@endforeach
@endsection

