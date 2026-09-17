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
                                <button type="button" class="btn btn-outline-danger btn-sm ms-1" data-bs-toggle="modal" data-bs-target="#brandingModal-{{ $s->id }}">
                                    <i class="bi bi-image me-1"></i> Poster &amp; Values
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

                        <!-- Poster & Core Values Modal -->
                        <div class="modal fade" id="brandingModal-{{ $s->id }}" tabindex="-1">
                            <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                <div class="modal-content">
                                    <form action="{{ route('backoffice.admin.seasons.update', $s) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="name" value="{{ $s->name }}">
                                        <input type="hidden" name="year" value="{{ $s->year }}">
                                        <input type="hidden" name="status" value="{{ $s->status }}">
                                        <input type="hidden" name="theme" value="{{ $s->theme }}">
                                        <input type="hidden" name="venue" value="{{ $s->venue }}">
                                        <input type="hidden" name="start_date" value="{{ $s->start_date->format('Y-m-d\TH:i') }}">
                                        <input type="hidden" name="end_date" value="{{ $s->end_date->format('Y-m-d\TH:i') }}">
                                        <input type="hidden" name="price" value="{{ $s->price }}">
                                        <input type="hidden" name="capacity" value="{{ $s->capacity }}">
                                        <input type="hidden" name="landing_subtitle" value="{{ $s->landing_subtitle }}">
                                        <input type="hidden" name="description" value="{{ $s->description }}">

                                        <div class="modal-header bg-dark text-white border-bottom border-danger">
                                            <div>
                                                <h5 class="modal-title fw-bold mb-0">
                                                    <i class="bi bi-palette-fill text-danger me-2"></i> Camp Poster &amp; Core Values — {{ $s->name }}
                                                </h5>
                                                <div class="small text-white-50">Changes made here update the public landing page immediately.</div>
                                            </div>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body p-4">
                                            {{-- 1. Poster Section --}}
                                            <div class="camp-card p-3 p-md-4 mb-4 border">
                                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                                    <h6 class="fw-bold mb-0 text-uppercase tracking-wide text-danger">
                                                        <i class="bi bi-image me-1"></i> Official Camp Poster Image
                                                    </h6>
                                                    <span class="badge bg-dark border text-white small">Display: Landing Page Hero</span>
                                                </div>
                                                <div class="row align-items-center g-4">
                                                    <div class="col-md-4 text-center">
                                                        <div class="position-relative d-inline-block rounded-3 overflow-hidden shadow-sm border border-secondary" style="max-width: 220px;">
                                                            <img src="{{ $s->getPosterUrl() }}"
                                                                 alt="Camp Poster"
                                                                 id="posterPreview-{{ $s->id }}"
                                                                 class="img-fluid rounded"
                                                                 style="max-height: 260px; object-fit: cover;">
                                                        </div>
                                                        <div class="small text-muted mt-2">Current Poster</div>
                                                    </div>
                                                    <div class="col-md-8">
                                                        <label class="form-label fw-bold">Upload New Poster Image</label>
                                                        <input type="file"
                                                               class="form-control mb-2"
                                                               name="poster"
                                                               accept="image/jpeg,image/png,image/jpg,image/webp"
                                                               onchange="previewPosterImage(event, 'posterPreview-{{ $s->id }}')">
                                                        <div class="form-text text-muted mb-3">
                                                            Recommended: Portrait ratio (e.g., 4:5 or 1080x1350px). Formats: JPG, PNG, WebP (Max 5MB).
                                                        </div>
                                                        @if($s->poster_path)
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="checkbox" name="remove_poster" value="1" id="removePoster-{{ $s->id }}">
                                                                <label class="form-check-label text-danger small fw-semibold" for="removePoster-{{ $s->id }}">
                                                                    <i class="bi bi-trash3 me-1"></i> Remove custom poster &amp; revert to default church camp poster
                                                                </label>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- 2. Core Values Section --}}
                                            <div class="camp-card p-3 p-md-4 border">
                                                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-uppercase tracking-wide text-danger">
                                                            <i class="bi bi-stars me-1"></i> Camp Core Values
                                                        </h6>
                                                        <div class="small text-muted">Customize the values, scripture focus, and pillars shown to parents &amp; campers.</div>
                                                    </div>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="addCoreValueRow('valuesContainer-{{ $s->id }}')">
                                                        <i class="bi bi-plus-circle me-1"></i> Add Value
                                                    </button>
                                                </div>

                                                <div id="valuesContainer-{{ $s->id }}" class="vstack gap-3">
                                                    @php
                                                        $values = $s->getCoreValues();
                                                    @endphp
                                                    @foreach($values as $idx => $val)
                                                        <div class="value-row p-3 rounded bg-light border position-relative">
                                                            <div class="row g-2 align-items-start">
                                                                <div class="col-md-3">
                                                                    <label class="form-label small fw-bold mb-1">Icon</label>
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text"><i class="bi {{ $val['icon'] ?? 'bi-stars' }}" id="iconPreview-{{ $s->id }}-{{ $idx }}"></i></span>
                                                                        <input type="text"
                                                                               class="form-control"
                                                                               name="core_values[{{ $idx }}][icon]"
                                                                               value="{{ $val['icon'] ?? 'bi-stars' }}"
                                                                               placeholder="bi-fire"
                                                                               oninput="document.getElementById('iconPreview-{{ $s->id }}-{{ $idx }}').className = 'bi ' + this.value">
                                                                    </div>
                                                                    <div class="form-text" style="font-size: 11px;">e.g. bi-fire, bi-people-fill, bi-compass-fill, bi-award-fill, bi-shield-fill-check</div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label small fw-bold mb-1">Value Title</label>
                                                                    <input type="text"
                                                                           class="form-control form-control-sm"
                                                                           name="core_values[{{ $idx }}][title]"
                                                                           value="{{ $val['title'] }}"
                                                                           placeholder="e.g. Christ-Centered Faith"
                                                                           required>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label class="form-label small fw-bold mb-1">Description</label>
                                                                    <textarea class="form-control form-control-sm"
                                                                              name="core_values[{{ $idx }}][description]"
                                                                              rows="2"
                                                                              placeholder="Short description for campers and parents">{{ $val['description'] }}</textarea>
                                                                </div>
                                                                <div class="col-md-1 text-end pt-md-4">
                                                                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.value-row').remove()" title="Delete this value">
                                                                        <i class="bi bi-trash3"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-camp-red btn-sm px-4">
                                                <i class="bi bi-check2-circle me-1"></i> Save Poster &amp; Core Values
                                            </button>
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

@push('scripts')
<script>
function previewPosterImage(event, previewId) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(previewId);
            if (preview) {
                preview.src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function addCoreValueRow(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const index = container.querySelectorAll('.value-row').length + Date.now();
    const row = document.createElement('div');
    row.className = 'value-row p-3 rounded bg-light border position-relative';
    row.innerHTML = `
        <div class="row g-2 align-items-start">
            <div class="col-md-3">
                <label class="form-label small fw-bold mb-1">Icon</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="bi bi-stars" id="iconPreview-new-${index}"></i></span>
                    <input type="text"
                           class="form-control"
                           name="core_values[${index}][icon]"
                           value="bi-stars"
                           placeholder="bi-fire"
                           oninput="document.getElementById('iconPreview-new-${index}').className = 'bi ' + this.value">
                </div>
                <div class="form-text" style="font-size: 11px;">e.g. bi-fire, bi-people-fill, bi-compass-fill</div>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Value Title</label>
                <input type="text"
                       class="form-control form-control-sm"
                       name="core_values[${index}][title]"
                       placeholder="e.g. Kingdom Excellence"
                       required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold mb-1">Description</label>
                <textarea class="form-control form-control-sm"
                          name="core_values[${index}][description]"
                          rows="2"
                          placeholder="Short description for campers and parents"></textarea>
            </div>
            <div class="col-md-1 text-end pt-md-4">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="this.closest('.value-row').remove()" title="Delete this value">
                    <i class="bi bi-trash3"></i>
                </button>
            </div>
        </div>
    `;
    container.appendChild(row);
}
</script>
@endpush
@endsection
