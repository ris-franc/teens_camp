@extends('layouts.backoffice')

@section('title', 'Registration Desk Operations')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0">Registration Desk Operations</h3>
            <p class="text-muted small mb-0">Season: <strong>{{ $season ? $season->name : 'None' }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-camp-red btn-sm">
                <i class="bi bi-person-plus-fill me-1"></i> New 2-Min Desk Intake
            </a>
            <a href="{{ route('backoffice.registration.signin') }}" class="btn btn-camp-dark btn-sm">
                <i class="bi bi-check2-circle me-1"></i> Camp-Day Sign-In
            </a>
        </div>
    </div>

    <!-- Quick Action Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('backoffice.registration.desk') }}" class="text-decoration-none">
                <div class="camp-card p-4 text-center h-100 border-danger">
                    <div class="d-inline-flex p-3 rounded-circle bg-danger text-white mb-2 shadow-sm">
                        <i class="bi bi-person-plus fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Desk Intake</h5>
                    <p class="text-muted small mb-0">Quick 2-minute registration creating parent & teen accounts.</p>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('backoffice.registration.signin') }}" class="text-decoration-none">
                <div class="camp-card p-4 text-center h-100">
                    <div class="d-inline-flex p-3 rounded-circle bg-success text-white mb-2 shadow-sm">
                        <i class="bi bi-check2-square fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Camp Sign-In</h5>
                    <p class="text-muted small mb-0">1-click check-in for arriving teens at the campsite.</p>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('backoffice.forms.index') }}" class="text-decoration-none">
                <div class="camp-card p-4 text-center h-100">
                    <div class="d-inline-flex p-3 rounded-circle bg-dark text-danger border border-danger mb-2 shadow-sm">
                        <i class="bi bi-file-earmark-diff fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Form Builder</h5>
                    <p class="text-muted small mb-0">Build dynamic forms with parent sign-off oversight.</p>
                </div>
            </a>
        </div>

        <div class="col-md-3">
            <a href="{{ route('backoffice.packing.index') }}" class="text-decoration-none">
                <div class="camp-card p-4 text-center h-100">
                    <div class="d-inline-flex p-3 rounded-circle bg-dark text-white border mb-2 shadow-sm">
                        <i class="bi bi-backpack fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Packing Lists</h5>
                    <p class="text-muted small mb-0">Manage seasonal checklist & release status.</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Recent Registrations Table -->
    <div class="camp-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h5 class="fw-bold mb-0"><i class="bi bi-clock-history text-danger me-2"></i> Recent Registrations (Current Season)</h5>
            <span class="badge bg-danger">{{ $totalRegistered }} Total Campers</span>
        </div>

        <div class="table-responsive">
            <table class="table table-camp align-middle">
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

