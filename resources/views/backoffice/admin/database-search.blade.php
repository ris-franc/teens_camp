@extends('layouts.backoffice')

@section('title', 'Database Search & Filter')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Camper Database &amp; Medical Filter</h3>
            <p class="text-muted small mb-0">Search and filter participants across seasons by medical conditions, medications, gender, phone status, and payments.</p>
        </div>
        <div class="d-grid d-sm-flex gap-2 w-100 w-md-auto">
            <a href="{{ route('backoffice.reports.database.pdf', request()->all()) }}" target="_blank" class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center gap-1 py-2 px-3">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF Report
            </a>
            <a href="{{ route('backoffice.admin.dashboard') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-1 py-2 px-3">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="camp-card p-3 p-md-4 mb-4">
        <form action="{{ route('backoffice.admin.database') }}" method="GET" class="row g-2 g-md-3">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-muted text-uppercase">Keyword Search</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-danger"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name, email, phone...">
                </div>
            </div>

            <div class="col-6 col-md-2">
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

            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Gender</label>
                <select class="form-select" name="gender">
                    <option value="">All Genders</option>
                    <option value="male" {{ ($filters['gender'] ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                    <option value="female" {{ ($filters['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Medical / Allergy</label>
                <select class="form-select" name="has_medical">
                    <option value="">All</option>
                    <option value="yes" {{ ($filters['has_medical'] ?? '') === 'yes' ? 'selected' : '' }}>Has Medical Notes</option>
                    <option value="no" {{ ($filters['has_medical'] ?? '') === 'no' ? 'selected' : '' }}>No Medical Notes</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label small fw-bold text-muted text-uppercase">Camp Status</label>
                <select class="form-select" name="status">
                    <option value="">All Statuses</option>
                    <option value="registered" {{ ($filters['status'] ?? '') === 'registered' ? 'selected' : '' }}>Registered</option>
                    <option value="signed_in" {{ ($filters['status'] ?? '') === 'signed_in' ? 'selected' : '' }}>Signed In</option>
                    <option value="withdrawn" {{ ($filters['status'] ?? '') === 'withdrawn' ? 'selected' : '' }}>Withdrawn</option>
                </select>
            </div>

            <div class="col-12 col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-camp-red w-100 d-flex align-items-center justify-content-center gap-1 py-2" title="Filter Database">
                    <i class="bi bi-funnel-fill"></i>
                    <span class="d-md-none">Apply Filters</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Results Container -->
    <div class="camp-card p-3 p-md-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <span class="text-muted small">Showing <strong>{{ $registrations->total() }}</strong> records matching filters</span>
            <a href="{{ route('backoffice.admin.database') }}" class="btn btn-link text-danger btn-sm text-decoration-none">Clear Filters</a>
        </div>

        {{-- Desktop / Large Tablet Table View --}}
        <div class="d-none d-lg-block table-responsive">
            <table class="table table-camp align-middle mb-0">
                <thead>
                    <tr>
                        <th>Camper</th>
                        <th>Season</th>
                        <th>Parent / Contact</th>
                        <th>Medical &amp; Medication Notes</th>
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

        {{-- Mobile Cards Feed (Phones & Small Tablets < 992px) --}}
        <div class="d-lg-none d-flex flex-column gap-3">
            @forelse($registrations as $reg)
                <div class="mobile-data-card">
                    {{-- Header: Avatar, Name, Gender, Season & Status --}}
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                  style="width: 38px; height: 38px; font-size: 14px;">
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
                        <div class="text-end">
                            @if($reg->status === 'signed_in')
                                <span class="badge bg-success"><i class="bi bi-check2-circle me-1"></i> Signed In</span>
                            @elseif($reg->status === 'withdrawn')
                                <span class="badge bg-secondary">Withdrawn</span>
                            @else
                                <span class="badge bg-danger">Registered</span>
                            @endif
                            <div class="mt-1">
                                <span class="badge bg-dark border border-secondary text-muted" style="font-size: 9px;">
                                    {{ $reg->campSeason ? $reg->campSeason->name : 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Parent / Emergency Contact --}}
                    <div class="p-2 rounded mb-2" style="background-color: rgba(255, 255, 255, 0.03); font-size: 12px;">
                        <div class="text-muted small">Parent / Emergency Contact:</div>
                        @if($reg->teen->parents->count() > 0)
                            @php $p = $reg->teen->parents->first(); @endphp
                            <div class="fw-semibold text-white">{{ $p->name }}</div>
                            @if($p->phone)
                                <a href="tel:{{ $p->phone }}" class="text-info text-decoration-none d-inline-flex align-items-center gap-1 mt-1">
                                    <i class="bi bi-telephone-fill"></i> {{ $p->phone }}
                                </a>
                            @endif
                        @else
                            <div class="text-muted">Emergency Phone: {{ $reg->emergency_contact_phone ?: 'None reported' }}</div>
                        @endif
                    </div>

                    {{-- Medical & Medication Notes Alert --}}
                    @if($reg->medical_conditions || $reg->medication_notes)
                        <div class="p-2 rounded mb-2 border border-danger" style="background: rgba(239, 68, 68, 0.1); font-size: 11px;">
                            <div class="fw-bold text-danger d-flex align-items-center gap-1 mb-1">
                                <i class="bi bi-exclamation-triangle-fill"></i> Medical Alert
                            </div>
                            @if($reg->medical_conditions)
                                <div class="text-light"><strong>Conditions:</strong> {{ $reg->medical_conditions }}</div>
                            @endif
                            @if($reg->medication_notes)
                                <div class="text-light"><strong>Medication:</strong> {{ $reg->medication_notes }}</div>
                            @endif
                        </div>
                    @else
                        <div class="small text-muted mb-2" style="font-size: 11px;">
                            <i class="bi bi-check-circle text-success me-1"></i> No medical / allergy notes reported
                        </div>
                    @endif

                    {{-- Phone Status & Tuition Progress --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <span class="badge bg-{{ $reg->phone_carried ? 'warning text-dark' : 'secondary' }}" style="font-size: 10px;">
                                <i class="bi {{ $reg->phone_carried ? 'bi-phone' : 'bi-slash-circle' }}"></i>
                                {{ $reg->phone_carried ? 'Carrying Phone' : 'No Phone' }}
                            </span>
                            @if($reg->teen->is_suspended)
                                <span class="badge bg-danger ms-1" style="font-size: 10px;">Suspended</span>
                            @endif
                        </div>
                        <div class="text-end">
                            <div class="small text-muted" style="font-size: 11px;">
                                Paid: <strong class="text-white">KES {{ number_format($reg->total_paid, 2) }}</strong> ({{ $reg->payment_percent }}%)
                            </div>
                            <div class="camp-progress mt-1" style="height: 5px; width: 110px;">
                                <div class="camp-progress-bar" style="width: {{ $reg->payment_percent }}%;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Row --}}
                    <div class="d-flex align-items-center gap-2 pt-2 border-top border-secondary border-opacity-25">
                        <button type="button" class="btn btn-outline-info btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-1 py-2"
                                data-bs-toggle="modal" data-bs-target="#teenFamilyModal{{ $reg->teen->id }}">
                            <i class="bi bi-people-fill"></i> Family
                        </button>

                        <form action="{{ route('backoffice.admin.users.toggle-suspension', $reg->teen) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to {{ $reg->teen->is_suspended ? 'reactivate' : 'suspend' }} account for {{ $reg->teen->name }}?')">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $reg->teen->is_suspended ? 'btn-outline-success' : 'btn-outline-warning' }} py-2 px-3" title="{{ $reg->teen->is_suspended ? 'Reactivate Account' : 'Suspend Account' }}">
                                <i class="bi {{ $reg->teen->is_suspended ? 'bi-person-check-fill' : 'bi-slash-circle' }}"></i>
                            </button>
                        </form>

                        <form action="{{ route('backoffice.admin.users.delete', $reg->teen) }}" method="POST" class="d-inline m-0" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete camper {{ $reg->teen->name }} ({{ $reg->teen->email }})? All registrations and history will be removed.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger py-2 px-3" title="Delete Account Permanently">
                                <i class="bi bi-trash3-fill"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                    No camper records found matching the selected search criteria.
                </div>
            @endforelse
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

