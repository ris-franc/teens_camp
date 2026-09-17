@extends('layouts.backoffice')

@section('title', 'Camp-Day Live Sign-In')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0">Camp-Day Live Sign-In</h3>
            <p class="text-muted small mb-0">1-click arrival check-in for campers arriving at camp venue. Season: <strong>{{ $season ? $season->name : 'None' }}</strong></p>
        </div>
        <a href="{{ route('backoffice.registration.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <!-- Search Box -->
    <div class="camp-card p-3 mb-4">
        <form action="{{ route('backoffice.registration.signin') }}" method="GET" class="row g-2">
            <div class="col-md-9">
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-danger"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control form-control-lg" name="search" value="{{ $search }}" placeholder="Quick camper search by name, email, or phone..." autofocus>
                </div>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-camp-red flex-grow-1">Search</button>
                <a href="{{ route('backoffice.registration.signin') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>

    <!-- Sign-In Roster Table -->
    <div class="camp-card p-4">
        <div class="table-responsive">
            <table class="table table-camp align-middle">
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

        <div class="mt-3">
            {{ $registrations->links() }}
        </div>
    </div>
</div>
@endsection
