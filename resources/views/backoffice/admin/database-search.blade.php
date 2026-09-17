@extends('layouts.backoffice')

@section('title', 'Database Search & Filter')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0">Camper Database & Medical Filter</h3>
            <p class="text-muted small mb-0">Search and filter participants across seasons by medical conditions, medications, gender, phone status, and payments.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('backoffice.reports.database.pdf', request()->all()) }}" target="_blank" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF Report
            </a>
            <a href="{{ route('backoffice.admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="camp-card p-4 mb-4">
        <form action="{{ route('backoffice.admin.database') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Keyword Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-danger"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email, phone...">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Camp Season</label>
                <select class="form-select" name="season_id">
                    <option value="">All Seasons</option>
                    @foreach($allSeasons as $s)
                        <option value="{{ $s->id }}" {{ ($filters['season_id'] ?? ($season ? $season->id : '')) == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ $s->year }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Gender</label>
                <select class="form-select" name="gender">
                    <option value="">All Genders</option>
                    <option value="male" {{ ($filters['gender'] ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ ($filters['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Medical / Allergy</label>
                <select class="form-select" name="has_medical">
                    <option value="">All</option>
                    <option value="yes" {{ ($filters['has_medical'] ?? '') === 'yes' ? 'selected' : '' }}>Has Medical/Medication Notes</option>
                    <option value="no" {{ ($filters['has_medical'] ?? '') === 'no' ? 'selected' : '' }}>No Medical Notes</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Camp Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="registered" {{ ($filters['status'] ?? '') === 'registered' ? 'selected' : '' }}>Registered</option>
                    <option value="signed_in" {{ ($filters['status'] ?? '') === 'signed_in' ? 'selected' : '' }}>Signed In</option>
                    <option value="withdrawn" {{ ($filters['status'] ?? '') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                </select>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-camp-red w-100">
                    <i class="bi bi-funnel-fill"></i>
                </button>
            </div>
        </form>
    </div>

    <!-- Results Table -->
    <div class="camp-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted small">Showing <strong>{{ $registrations->total() }}</strong> records matching filters</span>
            <a href="{{ route('backoffice.admin.database') }}" class="btn btn-link text-danger btn-sm text-decoration-none">Clear Filters</a>
        </div>

        <div class="table-responsive">
            <table class="table table-camp align-middle">
                <thead>
                    <tr>
                        <th>Camper</th>
                        <th>Season</th>
                        <th>Parent / Contact</th>
                        <th>Medical & Medication Notes</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Payment Progress</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        <tr>
                            <td>
                                <a href="javascript:void(0)" class="fw-bold text-white text-decoration-none text-hover-danger d-inline-flex align-items-center gap-1"
                                   data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}"
                                   title="Click to view parent info and siblings">
                                    {{ $reg->teen->name }}
                                    <i class="bi bi-people-fill text-danger small" style="font-size:11px;"></i>
                                </a>
                                <div class="small text-muted">{{ $reg->teen->email }} | {{ ucfirst($reg->teen->gender ?: 'N/A') }}</div>
                            </td>
                            <td>
                                <span class="badge bg-dark border text-muted">{{ $reg->campSeason ? $reg->campSeason->name : 'N/A' }}</span>
                            </td>
                            <td>
                                @if($reg->teen->parents->count() > 0)
                                    <div class="fw-semibold">{{ $reg->teen->parents->first()->name }}</div>
                                    <div class="small text-muted">{{ $reg->teen->parents->first()->phone ?: $reg->emergency_contact_phone }}</div>
                                @else
                                    <span class="text-muted small">Emergency: {{ $reg->emergency_contact_phone ?: 'N/A' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->medical_conditions || $reg->medication_notes)
                                    <div class="p-2 rounded bg-danger-subtle text-danger small border border-danger">
                                        @if($reg->medical_conditions)
                                            <div><strong>Conditions:</strong> {{ $reg->medical_conditions }}</div>
                                        @endif
                                        @if($reg->medication_notes)
                                            <div><strong>Medication:</strong> {{ $reg->medication_notes }}</div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i> None reported</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $reg->phone_carried ? 'warning text-dark' : 'secondary' }}">
                                    {{ $reg->phone_carried ? 'Carrying Phone' : 'No Phone' }}
                                </span>
                            </td>
                            <td>
                                @if($reg->status === 'signed_in')
                                    <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i> Signed In</span>
                                @elseif($reg->status === 'withdrawn')
                                    <span class="badge bg-secondary">Withdrawn</span>
                                @else
                                    <span class="badge bg-danger">Registered</span>
                                @endif
                            </td>
                            <td style="min-width: 140px;">
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>KES {{ number_format($reg->total_paid, 2) }}</span>
                                    <span>{{ $reg->payment_percent }}%</span>
                                </div>
                                <div class="camp-progress" style="height: 6px;">
                                    <div class="camp-progress-bar" style="width: {{ $reg->payment_percent }}%;"></div>
                                </div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap">
                                    {{-- Prominent Family & Sibling View Button --}}
                                    <button type="button" class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}"
                                            title="View parent info, siblings network, and edit details">
                                        <i class="bi bi-people-fill me-1"></i> Family &amp; Siblings
                                    </button>

                                    @if($reg->teen->is_suspended)
                                        <span class="badge bg-danger">Suspended</span>
                                    @endif

                                    <form action="{{ route('backoffice.admin.users.toggle-suspension', $reg->teen) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to {{ $reg->teen->is_suspended ? 'reactivate' : 'suspend' }} account for {{ $reg->teen->name }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $reg->teen->is_suspended ? 'btn-outline-success' : 'btn-outline-warning' }} py-1 px-2" style="font-size: 11px;" title="{{ $reg->teen->is_suspended ? 'Reactivate Account' : 'Suspend Account' }}">
                                            <i class="bi {{ $reg->teen->is_suspended ? 'bi-person-check-fill' : 'bi-slash-circle' }}"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('backoffice.admin.users.delete', $reg->teen) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete camper {{ $reg->teen->name }} ({{ $reg->teen->email }})? All registrations and history will be removed.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2" style="font-size: 11px;" title="Delete Account Permanently">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No camper records found matching the selected search criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $registrations->links() }}
        </div>
    </div>
</div>

{{-- ===== Teen Camper & Family Modals ===== --}}
@php
    $modalTeens = collect();
    foreach($registrations as $reg) {
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

