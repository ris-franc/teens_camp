@php
    $parent = $teen->parents->first() ?? $teen->parents()->first();
    $siblings = $teen->siblings();
    $allTeensList = $allTeens ?? \App\Models\User::where('role', 'teen')->where('id', '!=', $teen->id)->orderBy('name')->get();
@endphp

<div class="modal fade" id="teenFamilyModal{{ $teen->id }}" tabindex="-1" aria-labelledby="teenFamilyLabel{{ $teen->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <form action="{{ route('backoffice.admin.teens.update-family', $teen) }}" method="POST" class="modal-content border-0 text-white shadow-lg" style="background:#18181c; border-radius:14px; border: 1px solid rgba(255,255,255,0.08); max-height: 90vh;">
            @csrf

            {{-- Modal Header --}}
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3 flex-shrink-0" style="background:#141418;">
                <div class="d-flex align-items-center gap-3">
                    @if($teen->avatar)
                        <img src="{{ asset('storage/' . $teen->avatar) }}" class="rounded-circle border border-danger" width="46" height="46" alt="Avatar">
                    @else
                        <span class="rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                              style="width:46px;height:46px;font-size:18px;background:#B91C1C;">
                            {{ strtoupper(substr($teen->name, 0, 1)) }}
                        </span>
                    @endif
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-white" id="teenFamilyLabel{{ $teen->id }}">{{ $teen->name }}</h5>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-uppercase" style="font-size:11px;">Camper</span>
                            @if($teen->is_suspended)
                                <span class="badge bg-danger text-white">Suspended</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Active Account</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-0">Camper ID #{{ $teen->id }} · {{ $teen->email }} · {{ $teen->phone ?: 'No direct phone' }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body: Fully scrollable with high visibility --}}
            <div class="modal-body px-4 py-3" style="overflow-y: auto; max-height: calc(90vh - 140px); scrollbar-width: thin; scrollbar-color: #dc3545 #202026;">
                {{-- Quick Summary Pill Bar --}}
                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="p-2 rounded d-flex align-items-center gap-2" style="background:#22222a; border:1px solid rgba(255,255,255,0.05);">
                            <i class="bi bi-person-heart text-warning fs-3"></i>
                            <div class="lh-sm text-truncate">
                                <span class="text-muted d-block" style="font-size:10.5px; text-transform:uppercase; letter-spacing:0.5px;">PRIMARY PARENT</span>
                                <strong class="text-white small text-truncate d-block">
                                    {{ $parent ? $parent->name : 'No Parent Linked Yet' }}
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 rounded d-flex align-items-center gap-2" style="background:#22222a; border:1px solid rgba(255,255,255,0.05);">
                            <i class="bi bi-people-fill text-info fs-3"></i>
                            <div class="lh-sm">
                                <span class="text-muted d-block" style="font-size:10.5px; text-transform:uppercase; letter-spacing:0.5px;">SIBLINGS NETWORK</span>
                                <strong class="text-info small">
                                    {{ $siblings->count() }} {{ Str::plural('Sibling', $siblings->count()) }} Linked
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-2 rounded d-flex align-items-center gap-2" style="background:#22222a; border:1px solid rgba(255,255,255,0.05);">
                            <i class="bi bi-calendar2-check text-success fs-3"></i>
                            <div class="lh-sm">
                                <span class="text-muted d-block" style="font-size:10.5px; text-transform:uppercase; letter-spacing:0.5px;">SEASONS ATTENDED</span>
                                <strong class="text-success small">
                                    {{ $teen->registrations()->count() }} Camp {{ Str::plural('Season', $teen->registrations()->count()) }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main 2-Column Content Grid --}}
                <div class="row g-3">
                    {{-- LEFT COLUMN: Parent / Guardian Info (PROMINENT AT TOP) & Camper Profile --}}
                    <div class="col-lg-6">
                        {{-- 1. Parent / Guardian Information Card --}}
                        <div class="p-3 rounded mb-3" style="background:#1e1e24; border:1px solid rgba(234, 179, 8, 0.3); box-shadow: 0 0 15px rgba(234, 179, 8, 0.05);">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-25">
                                <h6 class="text-warning fw-bold mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-person-hearts text-warning fs-5"></i> Parent / Guardian Information
                                </h6>
                                @if($parent)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i> ID #{{ $parent->id }}
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-exclamation-circle-fill me-1"></i> Not Linked
                                    </span>
                                @endif
                            </div>
                            <p class="text-muted small mb-3" style="font-size:12px;">
                                Primary parent/guardian contact for this camper. Changes here automatically sync across the parent portal and all linked siblings.
                            </p>

                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-light mb-1">
                                        Parent Full Name <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-dark border-secondary text-warning"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary"
                                               name="parent_name" value="{{ $parent ? $parent->name : '' }}" placeholder="e.g. Sarah Muthoni">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-light mb-1">
                                        Parent Phone <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-dark border-secondary text-warning"><i class="bi bi-telephone"></i></span>
                                        <input type="tel" class="form-control form-control-sm bg-dark text-white border-secondary"
                                               name="parent_phone" value="{{ $parent ? $parent->phone : '' }}" placeholder="+254 7XX XXX XXX">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-light mb-1">Parent Email</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-dark border-secondary text-warning"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control form-control-sm bg-dark text-white border-secondary"
                                               name="parent_email" value="{{ $parent ? $parent->email : '' }}" placeholder="parent@domain.com">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-light mb-1">Relationship to Camper</label>
                                    @php
                                        $currRel = strtolower($parent?->pivot?->relationship ?? 'mother');
                                    @endphp
                                    <select class="form-select form-select-sm bg-dark text-white border-secondary" name="parent_relationship">
                                        <option value="Mother" {{ in_array($currRel, ['mother', 'mom']) ? 'selected' : '' }}>Mother</option>
                                        <option value="Father" {{ in_array($currRel, ['father', 'dad']) ? 'selected' : '' }}>Father</option>
                                        <option value="Guardian" {{ in_array($currRel, ['guardian']) ? 'selected' : '' }}>Guardian</option>
                                        <option value="Parent" {{ in_array($currRel, ['parent']) ? 'selected' : '' }}>Parent</option>
                                        <option value="Aunt / Uncle" {{ in_array($currRel, ['aunt', 'uncle', 'aunt / uncle']) ? 'selected' : '' }}>Aunt / Uncle</option>
                                        <option value="Sponsor" {{ in_array($currRel, ['sponsor']) ? 'selected' : '' }}>Sponsor</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 2. Camper Profile Details Card --}}
                        <div class="p-3 rounded" style="background:#1e1e24; border:1px solid rgba(255,255,255,0.08);">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-25">
                                <h6 class="text-danger fw-bold mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-person-badge-fill text-danger"></i> Camper Profile Details
                                </h6>
                                <span class="badge bg-secondary text-light" style="font-size:10px;">ID #{{ $teen->id }}</span>
                            </div>
                            <div class="row g-2">
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-light mb-1">Camper Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="name" value="{{ $teen->name }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-light mb-1">Camper Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control form-control-sm bg-dark text-white border-secondary" name="email" value="{{ $teen->email }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-light mb-1">Camper Phone</label>
                                    <input type="tel" class="form-control form-control-sm bg-dark text-white border-secondary" name="phone" value="{{ $teen->phone }}" placeholder="+254 7XX XXX XXX">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-light mb-1">Gender</label>
                                    <select class="form-select form-select-sm bg-dark text-white border-secondary" name="gender">
                                        <option value="">— Select Gender —</option>
                                        <option value="male" {{ $teen->gender === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $teen->gender === 'female' ? 'selected' : '' }}>Female</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-light mb-1">Date of Birth</label>
                                    <input type="date" class="form-control form-control-sm bg-dark text-white border-secondary" name="date_of_birth"
                                           value="{{ $teen->date_of_birth ? $teen->date_of_birth->format('Y-m-d') : '' }}">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-light mb-1">Home Address</label>
                                    <input type="text" class="form-control form-control-sm bg-dark text-white border-secondary" name="address" value="{{ $teen->address }}" placeholder="Estate, Street, House / City">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- RIGHT COLUMN: Siblings Network --}}
                    <div class="col-lg-6">
                        <div class="p-3 rounded h-100 d-flex flex-column" style="background:#1e1e24; border:1px solid rgba(6, 182, 212, 0.3); box-shadow: 0 0 15px rgba(6, 182, 212, 0.05);">
                            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom border-secondary border-opacity-25">
                                <h6 class="text-info fw-bold mb-0 d-flex align-items-center gap-2">
                                    <i class="bi bi-people-fill text-info fs-5"></i> Siblings Linked to Same Parent
                                </h6>
                                <span class="badge bg-info text-dark fw-bold">
                                    {{ $siblings->count() }} {{ Str::plural('Sibling', $siblings->count()) }}
                                </span>
                            </div>
                            <p class="text-muted small mb-3" style="font-size:12px;">
                                Other registered campers sharing <strong class="text-white">{{ $parent ? $parent->name : 'this family' }}</strong> as their parent or guardian.
                            </p>

                            {{-- Sibling Cards List --}}
                            <div class="flex-grow-1">
                                @if($siblings->count() > 0)
                                    <div class="d-flex flex-column gap-2 mb-3">
                                        @foreach($siblings as $sib)
                                            <div class="p-3 rounded bg-dark border border-secondary border-opacity-50 d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-3">
                                                    <span class="rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                          style="width:42px;height:42px;font-size:15px;background:#0e7490;">
                                                        {{ strtoupper(substr($sib->name, 0, 1)) }}
                                                    </span>
                                                    <div>
                                                        <div class="fw-bold text-white lh-sm mb-1 fs-6">{{ $sib->name }}</div>
                                                        <div class="text-muted small" style="font-size:11.5px;">
                                                            <span class="badge bg-secondary-subtle text-light border border-secondary text-capitalize me-1">{{ $sib->gender ?: 'Camper' }}</span>
                                                            @if($sib->date_of_birth)
                                                                <span class="text-light">{{ $sib->date_of_birth->age }} yrs</span> ({{ $sib->date_of_birth->format('M Y') }})
                                                            @else
                                                                <span class="text-muted">Age unrecorded</span>
                                                            @endif
                                                            · <span class="text-info">{{ $sib->registrations()->count() }} {{ Str::plural('season', $sib->registrations()->count()) }}</span>
                                                        </div>
                                                        <div class="text-muted small mt-1" style="font-size:11px;">
                                                            <i class="bi bi-envelope me-1 text-info"></i>{{ $sib->email }}
                                                            @if($sib->phone) · <i class="bi bi-telephone me-1 text-info"></i>{{ $sib->phone }} @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column align-items-end gap-1 flex-shrink-0">
                                                    <span class="badge {{ $sib->is_suspended ? 'bg-danger' : 'bg-success' }}" style="font-size:10px;">
                                                        {{ $sib->is_suspended ? 'Suspended' : 'Active' }}
                                                    </span>
                                                    <button type="button" class="btn btn-outline-info btn-sm py-1 px-2 mt-1 fw-semibold" style="font-size:11px;"
                                                            onclick="switchTeenModal('{{ $teen->id }}', '{{ $sib->id }}')"
                                                            title="Switch to {{ $sib->name }}'s profile">
                                                        <i class="bi bi-person-lines-fill me-1"></i> View Sibling
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-4 rounded bg-dark border border-secondary border-opacity-25 text-center text-muted small mb-3">
                                        <i class="bi bi-people fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        <div class="fw-semibold text-white mb-1">No Other Siblings Recorded</div>
                                        <div>There are currently no other campers linked to {{ $parent ? $parent->name : 'this camper\'s parent' }}.</div>
                                    </div>
                                @endif
                            </div>

                            {{-- Link Another Existing Teen as Sibling --}}
                            <div class="border-top border-secondary border-opacity-25 pt-3 mt-auto">
                                <label class="form-label small fw-semibold text-light mb-1">
                                    <i class="bi bi-link-45deg text-info me-1"></i> Link Another Existing Camper to this Family
                                </label>
                                <select class="form-select form-select-sm bg-dark text-white border-secondary" name="add_sibling_id">
                                    <option value="">— Select camper to link as sibling —</option>
                                    @foreach($allTeensList as $at)
                                        @if($at->id !== $teen->id && !$siblings->contains('id', $at->id))
                                            <option value="{{ $at->id }}">{{ $at->name }} ({{ $at->email }})</option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="text-muted mt-1" style="font-size:11px;">
                                    Selecting a camper links them to <strong class="text-light">{{ $parent ? $parent->name : 'this family' }}</strong> as siblings.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal Footer with persistent action bar --}}
            <div class="modal-footer border-top border-secondary border-opacity-25 px-4 py-3 flex-shrink-0 d-flex justify-content-between align-items-center" style="background:#141418;">
                {{-- Account Actions (Left) --}}
                <div class="d-flex align-items-center gap-2">
                    @if(auth()->guard('staff')->id() !== $teen->id)
                        {{-- Suspend / Reactivate Trigger --}}
                        <button type="button" class="btn btn-sm {{ $teen->is_suspended ? 'btn-outline-success' : 'btn-outline-warning' }}"
                                onclick="document.getElementById('teen-suspend-form-{{ $teen->id }}').submit();">
                            <i class="bi {{ $teen->is_suspended ? 'bi-person-check-fill' : 'bi-slash-circle' }} me-1"></i>
                            {{ $teen->is_suspended ? 'Reactivate Camper' : 'Suspend Camper' }}
                        </button>

                        {{-- Delete Trigger --}}
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="if(confirm('Are you sure you want to PERMANENTLY delete camper {{ $teen->name }} ({{ $teen->email }})? All registrations and history will be deleted. This cannot be undone.')){ document.getElementById('teen-delete-form-{{ $teen->id }}').submit(); }">
                            <i class="bi bi-trash3-fill me-1"></i> Delete Camper
                        </button>
                    @endif
                </div>

                {{-- Form Save (Right) --}}
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-camp-red btn-sm px-4 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Save Camper &amp; Family Information
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Standalone Quick Suspend & Delete Forms --}}
    <form id="teen-suspend-form-{{ $teen->id }}" action="{{ route('backoffice.admin.users.toggle-suspension', $teen) }}" method="POST" class="d-none">
        @csrf
    </form>
    <form id="teen-delete-form-{{ $teen->id }}" action="{{ route('backoffice.admin.users.delete', $teen) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
    if (typeof window.switchTeenModal === 'undefined') {
        window.switchTeenModal = function(currentId, targetId) {
            var currentEl = document.getElementById('teenFamilyModal' + currentId);
            var targetEl = document.getElementById('teenFamilyModal' + targetId);
            if (!targetEl) return;
            var currentInstance = bootstrap.Modal.getInstance(currentEl);
            if (currentInstance) {
                currentInstance.hide();
                currentEl.addEventListener('hidden.bs.modal', function handler() {
                    currentEl.removeEventListener('hidden.bs.modal', handler);
                    var targetInstance = bootstrap.Modal.getOrCreateInstance(targetEl);
                    targetInstance.show();
                });
            } else {
                var targetInstance = bootstrap.Modal.getOrCreateInstance(targetEl);
                targetInstance.show();
            }
        };
    }
</script>
