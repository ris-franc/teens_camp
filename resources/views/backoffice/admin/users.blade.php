@extends('layouts.backoffice')

@section('title', 'User & Access Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-0 text-white">User &amp; Access Rights</h3>
            <p class="text-muted small mb-0">Manage camp accounts across all roles. Edit profiles, reset PINs, or add new users.</p>
        </div>
        <button type="button" class="btn btn-camp-red btn-sm w-100 w-sm-auto py-2 px-3 fw-semibold text-nowrap" data-bs-toggle="modal" data-bs-target="#createUserModal" style="min-height: 44px;">
            <i class="bi bi-person-plus-fill me-1"></i> Add User
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filters & Search --}}
    <div class="camp-card p-3 mb-4">
        <form action="{{ route('backoffice.admin.users') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent text-danger border-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control border-secondary" name="search" value="{{ request('search') }}" placeholder="Search by name, email or phone...">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select class="form-select border-secondary" name="role" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="teen"          {{ request('role') === 'teen'          ? 'selected' : '' }}>Teen</option>
                    <option value="parent"        {{ request('role') === 'parent'        ? 'selected' : '' }}>Parent</option>
                    <option value="admin"         {{ request('role') === 'admin'         ? 'selected' : '' }}>System Admin</option>
                    <option value="pastor"        {{ request('role') === 'pastor'        ? 'selected' : '' }}>Pastor</option>
                    <option value="registration"  {{ request('role') === 'registration'  ? 'selected' : '' }}>Registration Team</option>
                    <option value="campaign"      {{ request('role') === 'campaign'      ? 'selected' : '' }}>Campaign Team</option>
                    <option value="campaign_head" {{ request('role') === 'campaign_head' ? 'selected' : '' }}>Campaign Team Head</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-camp-dark btn-sm flex-fill py-2" style="min-height: 38px;">Filter</button>
                <a href="{{ route('backoffice.admin.users') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center justify-content-center text-decoration-none" style="min-height: 38px;">Reset</a>
            </div>
        </form>
    </div>

    {{-- User Table (Desktop) & Cards (Mobile) --}}
    <div class="camp-card p-0 overflow-hidden">
        <!-- Desktop Table View -->
        <div class="d-none d-md-block table-responsive">
            <table class="table table-camp align-middle mb-0">
                <thead class="border-bottom border-secondary border-opacity-25">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Gender / DOB</th>
                        <th>Registrations</th>
                        <th>PIN Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $u)
                    <tr id="user-row-{{ $u->id }}">
                        {{-- Avatar + Name --}}
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-2">
                                @if($u->avatar)
                                    <img src="{{ asset('storage/' . $u->avatar) }}" class="rounded-circle border" width="36" height="36" alt="Avatar">
                                @else
                                    <span class="rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                          style="width:36px;height:36px;font-size:13px;background:{{ $u->isStaff() ? '#B91C1C' : '#374151' }}">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </span>
                                @endif
                                <div>
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="fw-semibold lh-sm text-white">{{ $u->name }}</div>
                                        @if($u->is_suspended)
                                            <span class="badge bg-danger ms-1" style="font-size:10px;">Suspended</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        {{-- Role --}}
                        <td>
                            <span class="badge text-uppercase fw-semibold
                                {{ $u->isAdmin() ? 'bg-danger' :
                                   ($u->isPastor() ? 'bg-purple' :
                                   ($u->isStaff() ? 'bg-dark border border-danger text-danger' : 'bg-secondary')) }}">
                                {{ str_replace('_', ' ', $u->role) }}
                            </span>
                        </td>
                        {{-- Phone --}}
                        <td class="small">{{ $u->phone ?: '—' }}</td>
                        {{-- Gender / DOB --}}
                        <td class="small text-muted">
                            @if($u->gender)
                                <span class="badge {{ $u->gender === 'male' ? 'bg-primary' : 'bg-pink' }} text-capitalize me-1">{{ $u->gender }}</span>
                            @endif
                            @if($u->date_of_birth)
                                {{ $u->date_of_birth->format('M d, Y') }}
                            @else
                                —
                            @endif
                        </td>
                        {{-- Registrations count --}}
                        <td>
                            <span class="badge bg-secondary">{{ $u->registrations_count }}</span>
                        </td>
                        {{-- PIN Status --}}
                        <td>
                            @if($u->pin_reset_required)
                                <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Pending Reset</span>
                            @else
                                <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i> Active PIN</span>
                            @endif
                        </td>
                        {{-- Actions --}}
                        <td class="text-end pe-4">
                            <div class="d-flex justify-content-end gap-1 flex-wrap">
                                {{-- Edit Button --}}
                                <button type="button" class="btn btn-outline-info btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}"
                                        title="Edit user details">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>

                                {{-- Reset PIN --}}
                                <form action="{{ route('backoffice.admin.users.reset-pin', $u) }}" method="POST"
                                      onsubmit="return confirm('Reset PIN for {{ $u->name }} to 0000?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="Reset PIN to 0000">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>

                                {{-- Suspend & Delete Accounts --}}
                                @if(auth()->guard('staff')->id() !== $u->id)
                                    {{-- Suspend / Reactivate Account --}}
                                    <form action="{{ route('backoffice.admin.users.toggle-suspension', $u) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to {{ $u->is_suspended ? 'reactivate' : 'suspend' }} account for {{ $u->name }}?')">
                                        @csrf
                                        <button type="submit" class="btn {{ $u->is_suspended ? 'btn-outline-success' : 'btn-outline-warning' }} btn-sm"
                                                title="{{ $u->is_suspended ? 'Reactivate Account' : 'Suspend Account' }}">
                                            <i class="bi {{ $u->is_suspended ? 'bi-person-check-fill' : 'bi-slash-circle' }}"></i>
                                        </button>
                                    </form>

                                    {{-- Delete Account --}}
                                    <form action="{{ route('backoffice.admin.users.delete', $u) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to PERMANENTLY delete user {{ $u->name }} ({{ $u->email }})? All records, history, and registrations will be removed. This cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete Account Permanently">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-people fs-3 d-block mb-2 opacity-25"></i>
                            No users found matching your criteria.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card Feed View -->
        <div class="d-md-none p-3 d-flex flex-column gap-3">
            @forelse($users as $u)
                <div class="camp-card p-3 border border-secondary border-opacity-25 shadow-sm" id="user-card-{{ $u->id }}">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div class="d-flex align-items-center gap-2">
                            @if($u->avatar)
                                <img src="{{ asset('storage/' . $u->avatar) }}" class="rounded-circle border flex-shrink-0" width="38" height="38" alt="Avatar">
                            @else
                                <span class="rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                      style="width:38px;height:38px;font-size:14px;background:{{ $u->isStaff() ? '#B91C1C' : '#374151' }}">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </span>
                            @endif
                            <div class="overflow-hidden">
                                <div class="fw-bold text-white fs-6 text-truncate">{{ $u->name }}</div>
                                <div class="small text-muted text-truncate">{{ $u->email }}</div>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <span class="badge text-uppercase fw-semibold
                                {{ $u->isAdmin() ? 'bg-danger' :
                                   ($u->isPastor() ? 'bg-purple' :
                                   ($u->isStaff() ? 'bg-dark border border-danger text-danger' : 'bg-secondary')) }}">
                                {{ str_replace('_', ' ', $u->role) }}
                            </span>
                            @if($u->is_suspended)
                                <div class="mt-1"><span class="badge bg-danger" style="font-size: 10px;">Suspended</span></div>
                            @endif
                        </div>
                    </div>

                    @if($u->phone)
                        <div class="small mb-2">
                            <a href="tel:{{ $u->phone }}" class="text-decoration-none text-white-50 d-inline-flex align-items-center gap-1">
                                <i class="bi bi-telephone-fill text-danger small"></i> {{ $u->phone }}
                            </a>
                        </div>
                    @endif

                    <div class="row g-2 py-2 my-2 border-top border-bottom border-secondary border-opacity-25 bg-black bg-opacity-25 rounded px-2 small">
                        <div class="col-6">
                            <span class="text-muted d-block" style="font-size: 10px;">GENDER / DOB</span>
                            <div class="text-white-50 mt-1">
                                @if($u->gender)
                                    <span class="badge {{ $u->gender === 'male' ? 'bg-primary' : 'bg-pink' }} text-capitalize px-1 py-0 me-1">{{ $u->gender }}</span>
                                @endif
                                {{ $u->date_of_birth ? $u->date_of_birth->format('M d, Y') : '—' }}
                            </div>
                        </div>
                        <div class="col-6 text-end">
                            <span class="text-muted d-block" style="font-size: 10px;">PIN STATUS</span>
                            <div class="mt-1">
                                @if($u->pin_reset_required)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Reset Pending</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-shield-check me-1"></i> Active PIN</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between small text-muted mb-3 px-1">
                        <span>Registrations: <strong class="text-white">{{ $u->registrations_count }}</strong></span>
                        <span>ID #{{ $u->id }}</span>
                    </div>

                    <div class="d-flex gap-2 flex-wrap pt-2 border-top border-secondary border-opacity-25">
                        {{-- Edit Button --}}
                        <button type="button" class="btn btn-outline-info btn-sm flex-fill py-2"
                                data-bs-toggle="modal" data-bs-target="#editUserModal{{ $u->id }}"
                                style="min-height: 42px;">
                            <i class="bi bi-pencil-fill me-1"></i> Edit
                        </button>

                        {{-- Reset PIN --}}
                        <form action="{{ route('backoffice.admin.users.reset-pin', $u) }}" method="POST" class="flex-fill"
                              onsubmit="return confirm('Reset PIN for {{ $u->name }} to 0000?')">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100 py-2" style="min-height: 42px;">
                                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset PIN
                            </button>
                        </form>

                        @if(auth()->guard('staff')->id() !== $u->id)
                            {{-- Suspend / Reactivate --}}
                            <form action="{{ route('backoffice.admin.users.toggle-suspension', $u) }}" method="POST" class="flex-fill"
                                  onsubmit="return confirm('Are you sure you want to {{ $u->is_suspended ? 'reactivate' : 'suspend' }} account for {{ $u->name }}?')">
                                @csrf
                                <button type="submit" class="btn {{ $u->is_suspended ? 'btn-outline-success' : 'btn-outline-warning' }} btn-sm w-100 py-2" style="min-height: 42px;">
                                    <i class="bi {{ $u->is_suspended ? 'bi-person-check-fill' : 'bi-slash-circle' }} me-1"></i>
                                    {{ $u->is_suspended ? 'Reactivate' : 'Suspend' }}
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form action="{{ route('backoffice.admin.users.delete', $u) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to PERMANENTLY delete user {{ $u->name }} ({{ $u->email }})?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm py-2 px-3" style="min-height: 42px;" title="Delete Permanently">
                                    <i class="bi bi-trash3-fill"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4 camp-card">
                    <i class="bi bi-people fs-3 d-block mb-2 opacity-25"></i>
                    No users found matching your criteria.
                </div>
            @endforelse
        </div>

        @if($users->hasPages())
        <div class="px-4 py-3 border-top border-secondary border-opacity-25">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ===== Edit User Modals (one per user) ===== --}}
@foreach($users as $u)
<div class="modal fade" id="editUserModal{{ $u->id }}" tabindex="-1" aria-labelledby="editUserLabel{{ $u->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0" style="background:#1a1a1e;">
            <form action="{{ route('backoffice.admin.users.update', $u) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <div class="d-flex align-items-center gap-2">
                        @if($u->avatar)
                            <img src="{{ asset('storage/' . $u->avatar) }}" class="rounded-circle border" width="40" height="40" alt="">
                        @else
                            <span class="rounded-circle text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                  style="width:40px;height:40px;font-size:15px;background:{{ $u->isStaff() ? '#B91C1C' : '#374151' }}">
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            </span>
                        @endif
                        <div>
                            <h6 class="modal-title fw-bold mb-0" id="editUserLabel{{ $u->id }}">Edit User — {{ $u->name }}</h6>
                            <p class="text-muted small mb-0">ID #{{ $u->id }} · Created {{ $u->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        {{-- Full Name --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ $u->name }}" required>
                        </div>
                        {{-- Email --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" value="{{ $u->email }}" required>
                        </div>
                        {{-- Phone --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone Number</label>
                            <input type="tel" class="form-control" name="phone" value="{{ $u->phone }}" placeholder="+254 7XX XXX XXX">
                        </div>
                        {{-- Role --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" required>
                                @foreach(['teen' => 'Teen', 'parent' => 'Parent', 'admin' => 'System Admin', 'pastor' => 'Pastor', 'registration' => 'Registration Team', 'campaign' => 'Campaign Team', 'campaign_head' => 'Campaign Team Head'] as $val => $label)
                                    <option value="{{ $val }}" {{ $u->role === $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Gender --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Gender</label>
                            <select class="form-select" name="gender">
                                <option value="">— Not specified —</option>
                                <option value="male"   {{ $u->gender === 'male'   ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ $u->gender === 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                        {{-- DOB --}}
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Date of Birth</label>
                            <input type="date" class="form-control" name="date_of_birth"
                                   value="{{ $u->date_of_birth ? $u->date_of_birth->format('Y-m-d') : '' }}">
                        </div>
                        {{-- Address --}}
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Home Address</label>
                            <textarea class="form-control" name="address" rows="2" placeholder="Street, Estate, City">{{ $u->address }}</textarea>
                        </div>
                        {{-- Force PIN Reset --}}
                        <div class="col-12">
                            <div class="form-check form-switch mt-1">
                                <input class="form-check-input" type="checkbox" name="force_pin_reset" id="forcePinReset{{ $u->id }}" value="1">
                                <label class="form-check-label small fw-semibold" for="forcePinReset{{ $u->id }}">
                                    Force PIN reset to <code>0000</code> on next login
                                    @if($u->pin_reset_required)
                                        <span class="badge bg-warning text-dark ms-1">Already pending</span>
                                    @endif
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-camp-red btn-sm">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

{{-- ===== Create User Modal ===== --}}
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0" style="background:#1a1a1e;">
            <form action="{{ route('backoffice.admin.users.create') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom border-secondary border-opacity-25">
                    <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2 text-danger"></i> Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" required>
                                <option value="teen">Teen</option>
                                <option value="parent">Parent</option>
                                <option value="admin">System Admin</option>
                                <option value="pastor">Pastor</option>
                                <option value="registration">Registration Team</option>
                                <option value="campaign">Campaign Team</option>
                                <option value="campaign_head">Campaign Team Head</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Phone Number</label>
                            <input type="text" class="form-control" name="phone" placeholder="+254 7XX XXX XXX">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small">Initial 4-Digit PIN</label>
                            <input type="password" maxlength="4" pattern="[0-9]{4}" class="form-control" name="pin" placeholder="Defaults to 0000">
                            <div class="form-text small text-muted">If left blank, user sets their PIN on first login.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-secondary border-opacity-25">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-camp-red btn-sm">
                        <i class="bi bi-person-plus me-1"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-pink { background: #db2777 !important; }
.bg-purple { background: #7c3aed !important; }
.table-camp td, .table-camp th { border-color: rgba(255,255,255,.07); }
.table-camp tbody tr:hover { background: rgba(255,255,255,.03); }
</style>
@endsection
