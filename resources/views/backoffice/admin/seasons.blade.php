@extends('layouts.backoffice')

@section('title', 'Camp Season Lifecycle Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0">Camp Season Lifecycle</h3>
            <p class="text-muted small mb-0">Manage camp years, pricing, capacity, dates, venue, and public landing page content.</p>
        </div>
        <button type="button" class="btn btn-camp-red btn-sm" data-bs-toggle="modal" data-bs-target="#createSeasonModal">
            <i class="bi bi-calendar-plus me-1"></i> Create New Camp Season
        </button>
    </div>

    <!-- Seasons List Table -->
    <div class="camp-card p-4 mb-4">
        <div class="table-responsive">
            <table class="table table-camp align-middle">
                <thead>
                    <tr>
                        <th>Season Name</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Dates</th>
                        <th>Venue</th>
                        <th>Price</th>
                        <th>Capacity</th>
                        <th>Registrations</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($seasons as $s)
                        <tr>
                            <td>
                                <div class="fw-bold text-danger">{{ $s->name }}</div>
                                <div class="small text-muted">Theme: {{ $s->theme ?: 'N/A' }}</div>
                            </td>
                            <td><span class="badge bg-dark border text-white">{{ $s->year }}</span></td>
                            <td>
                                @if($s->status === 'active')
                                    <span class="badge bg-success"><i class="bi bi-broadcast me-1"></i> Active Live Season</span>
                                @elseif($s->status === 'archived')
                                    <span class="badge bg-secondary"><i class="bi bi-archive me-1"></i> Archived (Preserved)</span>
                                @else
                                    <span class="badge bg-warning text-dark"><i class="bi bi-pencil me-1"></i> Draft</span>
                                @endif
                            </td>
                            <td>{{ $s->start_date->format('M d') }} - {{ $s->end_date->format('M d, Y') }}</td>
                            <td>{{ $s->venue }}</td>
                            <td class="fw-bold">KES {{ number_format($s->price, 2) }}</td>
                            <td>{{ $s->capacity }}</td>
                            <td>
                                <span class="badge bg-danger">{{ $s->registrations_count }} campers</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editSeasonModal-{{ $s->id }}">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </button>
                                <a href="{{ route('backoffice.switch-season', $s) }}" class="btn btn-camp-dark btn-sm ms-1">
                                    <i class="bi bi-eye me-1"></i> View Data
                                </a>
                            </td>
                        </tr>

                        <!-- Edit Season Modal -->
                        <div class="modal fade" id="editSeasonModal-{{ $s->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('backoffice.admin.seasons.update', $s) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold">Edit Season: {{ $s->name }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Season Name</label>
                                                    <input type="text" class="form-control" name="name" value="{{ $s->name }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Year</label>
                                                    <input type="text" class="form-control" name="year" value="{{ $s->year }}" required maxlength="4">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Status</label>
                                                    <select class="form-select" name="status" required>
                                                        <option value="draft" {{ $s->status === 'draft' ? 'selected' : '' }}>Draft</option>
                                                        <option value="active" {{ $s->status === 'active' ? 'selected' : '' }}>Active (Current Season)</option>
                                                        <option value="archived" {{ $s->status === 'archived' ? 'selected' : '' }}>Archived (Past)</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Camp Theme / Motto</label>
                                                    <input type="text" class="form-control" name="theme" value="{{ $s->theme }}">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-semibold">Venue / Location</label>
                                                    <input type="text" class="form-control" name="venue" value="{{ $s->venue }}" required>
                                                </div>

                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Start Date</label>
                                                    <input type="datetime-local" class="form-control" name="start_date" value="{{ $s->start_date->format('Y-m-d\TH:i') }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">End Date</label>
                                                    <input type="datetime-local" class="form-control" name="end_date" value="{{ $s->end_date->format('Y-m-d\TH:i') }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Camp Price (KES)</label>
                                                    <input type="number" step="1" class="form-control" name="price" value="{{ $s->price }}" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold">Camper Capacity</label>
                                                    <input type="number" class="form-control" name="capacity" value="{{ $s->capacity }}" required>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Landing Page Subtitle / Teaser</label>
                                                    <textarea class="form-control" name="landing_subtitle" rows="2">{{ $s->landing_subtitle }}</textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold">Full Description</label>
                                                    <textarea class="form-control" name="description" rows="3">{{ $s->description }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-camp-red btn-sm">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Season Modal -->
    <div class="modal fade" id="createSeasonModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('backoffice.admin.seasons.create') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Create New Camp Season</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Season Name</label>
                                <input type="text" class="form-control" name="name" placeholder="e.g. Teen Camp 2027" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Year</label>
                                <input type="text" class="form-control" name="year" placeholder="2027" required maxlength="4">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Status</label>
                                <select class="form-select" name="status" required>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active (Set as Current)</option>
                                    <option value="archived">Archived</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Theme / Motto</label>
                                <input type="text" class="form-control" name="theme" placeholder="e.g. Greater Works">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Venue / Location</label>
                                <input type="text" class="form-control" name="venue" placeholder="e.g. Brackenhurst Conference Centre, Limuru" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Start Date</label>
                                <input type="datetime-local" class="form-control" name="start_date" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">End Date</label>
                                <input type="datetime-local" class="form-control" name="end_date" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Camp Price (KES)</label>
                                <input type="number" step="1" class="form-control" name="price" value="15000" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Capacity</label>
                                <input type="number" class="form-control" name="capacity" value="250" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control" name="description" rows="3" placeholder="Camp details for participants"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-camp-red btn-sm">Create Camp Season</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
