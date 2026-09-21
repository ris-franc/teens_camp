@extends('layouts.backoffice')

@section('title', 'Camp-Day Live Sign-In')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Camp-Day Live Sign-In</h3>
            <p class="text-muted small mb-0">1-click arrival check-in for campers arriving at camp venue. Season: <strong class="text-white">{{ $season ? $season->name : 'None' }}</strong></p>
        </div>
        <div class="d-grid d-sm-flex gap-2 w-100 w-md-auto">
            <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-camp-red btn-sm d-flex align-items-center justify-content-center gap-1 py-2 px-3">
                <i class="bi bi-person-plus-fill me-1"></i> Desk Intake
            </a>
            <a href="{{ route('backoffice.registration.dashboard') }}" class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-1 py-2 px-3">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <!-- Search Box -->
    <div class="camp-card p-3 mb-4">
        <form action="{{ route('backoffice.registration.signin') }}" method="GET" class="row g-2">
            <div class="col-12 col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-danger"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control form-control-lg" name="search" value="{{ $search }}" placeholder="Search camper or parent phone..." autofocus>
                </div>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-camp-red flex-grow-1 d-flex align-items-center justify-content-center gap-1 py-2">
                    <i class="bi bi-search"></i> Search
                </button>
                @if($search)
                    <a href="{{ route('backoffice.registration.signin') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center px-3">Clear</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Sign-In Roster Container -->
    <div class="camp-card p-3 p-md-4">
        {{-- Desktop / Tablet Table View --}}
        <div class="d-none d-md-block table-responsive">
            <table class="table table-camp align-middle mb-0">
                <thead>
                    <tr>
                        <th>Camper</th>
                        <th>Parent Contact</th>
                        <th>Payment Status</th>
                        <th>Medical Flag</th>
                        <th>Check-In Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        <tr class="{{ $reg->status === 'signed_in' ? 'table-success-subtle' : '' }}">
                            <td>
                                <div class="fw-bold fs-6">{{ $reg->teen->name }}</div>
                                <div class="small text-muted">{{ $reg->teen->email }} | {{ ucfirst($reg->teen->gender ?: 'N/A') }}</div>
                            </td>
                            <td>
                                @if($reg->teen->parents->count() > 0)
                                    <div>{{ $reg->teen->parents->first()->name }}</div>
                                    <div class="small text-muted">{{ $reg->teen->parents->first()->phone }}</div>
                                @else
                                    <span class="small text-muted">Emergency: {{ $reg->emergency_contact_phone }}</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->balance_remaining <= 0)
                                    <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i> Paid in Full</span>
                                @else
                                    <span class="badge bg-danger">Due: KES {{ number_format($reg->balance_remaining, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->medical_conditions || $reg->medication_notes)
                                    <span class="badge bg-warning text-dark" title="{{ $reg->medical_conditions }} | {{ $reg->medication_notes }}">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Medical Flag
                                    </span>
                                @else
                                    <span class="text-muted small"><i class="bi bi-check-circle text-success me-1"></i> Clear</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->status === 'signed_in')
                                    <span class="badge bg-success py-2 px-3">
                                        <i class="bi bi-check-circle-fill me-1"></i> Signed In at {{ $reg->signed_in_at ? $reg->signed_in_at->format('h:i A') : 'Desk' }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary py-2 px-3">Awaiting Arrival</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($reg->status === 'signed_in')
                                        <form action="{{ route('backoffice.registration.signin.undo', $reg) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-secondary btn-sm" title="Undo Sign-In">
                                                <i class="bi bi-arrow-counterclockwise"></i> Undo
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('backoffice.registration.signin.checkin', $reg) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-camp-red btn-sm px-3">
                                                <i class="bi bi-check-lg me-1"></i> Check In
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('backoffice.registration.edit', $reg) }}" class="btn btn-outline-light btn-sm" title="Edit Registration">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No campers found matching search.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Arrival Feed (Phones < 768px) --}}
        <div class="d-md-none d-flex flex-column gap-3">
            @forelse($registrations as $reg)
                <div class="mobile-data-card {{ $reg->status === 'signed_in' ? 'border-success' : '' }}">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle {{ $reg->status === 'signed_in' ? 'bg-success' : 'bg-danger' }} text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                  style="width: 36px; height: 36px; font-size: 13px;">
                                {{ strtoupper(substr($reg->teen->name, 0, 1)) }}
                            </span>
                            <div>
                                <div class="fw-bold text-white fs-6">{{ $reg->teen->name }}</div>
                                <div class="text-muted" style="font-size: 11px;">
                                    <span class="badge bg-dark border border-secondary text-uppercase py-0 px-1">{{ $reg->teen->gender }}</span>
                                    {{ $reg->teen->email }}
                                </div>
                            </div>
                        </div>
                        <div>
                            @if($reg->status === 'signed_in')
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Checked In</span>
                            @else
                                <span class="badge bg-secondary">Awaiting</span>
                            @endif
                        </div>
                    </div>

                    {{-- Parent contact --}}
                    <div class="p-2 rounded mb-2" style="background-color: rgba(255, 255, 255, 0.03); font-size: 12px;">
                        <div class="text-muted small">Parent / Contact:</div>
                        @if($reg->teen->parents->count() > 0)
                            @php $p = $reg->teen->parents->first(); @endphp
                            <div class="fw-semibold text-white">{{ $p->name }}</div>
                            @if($p->phone)
                                <a href="tel:{{ $p->phone }}" class="text-info text-decoration-none d-inline-flex align-items-center gap-1 mt-1">
                                    <i class="bi bi-telephone-fill"></i> {{ $p->phone }}
                                </a>
                            @endif
                        @else
                            <div class="text-muted">Emergency: {{ $reg->emergency_contact_phone ?: 'None' }}</div>
                        @endif
                    </div>

                    {{-- Medical Alert & Tuition Status --}}
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            @if($reg->medical_conditions || $reg->medication_notes)
                                <span class="badge bg-warning text-dark" style="font-size: 11px;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> Medical Alert
                                </span>
                            @else
                                <span class="badge bg-dark border border-secondary text-muted" style="font-size: 10px;">
                                    <i class="bi bi-check me-1 text-success"></i> Medical Clear
                                </span>
                            @endif
                        </div>
                        <div>
                            @if($reg->balance_remaining <= 0)
                                <span class="badge bg-success-subtle text-success" style="font-size: 11px;">
                                    <i class="bi bi-check2-all me-1"></i> Paid in Full
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger" style="font-size: 11px;">
                                    Due: KES {{ number_format($reg->balance_remaining, 2) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions: 1-Click Check In or Undo --}}
                    <div class="d-flex gap-2">
                        @if($reg->status === 'signed_in')
                            <form action="{{ route('backoffice.registration.signin.undo', $reg) }}" method="POST" class="m-0 flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-2">
                                    <i class="bi bi-arrow-counterclockwise"></i> Undo Sign-In
                                </button>
                            </form>
                        @else
                            <form action="{{ route('backoffice.registration.signin.checkin', $reg) }}" method="POST" class="m-0 flex-grow-1">
                                @csrf
                                <button type="submit" class="btn btn-camp-red btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-2 fw-bold">
                                    <i class="bi bi-check-lg fs-6"></i> Check In Camper
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('backoffice.registration.edit', $reg) }}" class="btn btn-outline-light btn-sm d-flex align-items-center justify-content-center px-3" title="Edit Registration">
                            <i class="bi bi-pencil-square"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                    No campers found matching search.
                </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $registrations->links() }}
        </div>
    </div>
</div>
@endsection
