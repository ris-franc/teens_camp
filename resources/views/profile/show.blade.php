@extends($isStaff ? 'layouts.backoffice' : 'layouts.app')

@section('title', 'My Profile & Security')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="camp-card p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                    <div>
                        <h3 class="fw-bold mb-1">My Profile & Security</h3>
                        <p class="text-muted small mb-0">Update your photo avatar, contact details, and 4-digit security PIN</p>
                    </div>
                    <span class="badge bg-danger text-uppercase px-3 py-2">
                        {{ str_replace('_', ' ', $user->role) }}
                    </span>
                </div>

                <form action="{{ $isStaff ? route('backoffice.profile.update') : route('public.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Avatar Section -->
                    <div class="d-flex flex-column flex-sm-row align-items-center gap-4 mb-4 pb-4 border-bottom">
                        <div class="position-relative">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" class="rounded-circle border border-3 border-danger shadow" width="96" height="96" alt="Avatar">
                            @else
                                <div class="rounded-circle bg-dark border border-2 border-danger text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow" style="width: 96px; height: 96px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1 text-center text-sm-start">
                            <label for="avatar" class="form-label fw-bold">Profile Picture</label>
                            <input class="form-control" type="file" id="avatar" name="avatar" accept="image/*">
                            <div class="form-text small">JPEG, PNG or WEBP up to 2MB.</div>
                        </div>
                    </div>

                    <!-- Personal Information -->
                    <div class="row g-3 mb-4 pb-4 border-bottom">
                        <div class="col-12">
                            <h5 class="fw-bold"><i class="bi bi-person me-2 text-danger"></i> Personal Details</h5>
                        </div>
                        <div class="col-md-6">
                            <label for="name" class="form-label fw-semibold">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control bg-secondary-subtle" id="email" value="{{ $user->email }}" readonly disabled>
                            <div class="form-text small">Email cannot be changed directly.</div>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label fw-semibold">Phone Number</label>
                            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+1 234 567 8900">
                        </div>
                    </div>

                    <!-- 4-Digit PIN Change Section -->
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <h5 class="fw-bold"><i class="bi bi-shield-lock me-2 text-danger"></i> Change 4-Digit PIN</h5>
                            <p class="text-muted small mb-3">Leave blank if you do not wish to change your PIN.</p>
                        </div>
                        <div class="col-md-4">
                            <label for="current_pin" class="form-label fw-semibold">Current PIN</label>
                            <input type="password" maxlength="4" pattern="[0-9]{4}" class="form-control text-center fs-5 tracking-widest @error('current_pin') is-invalid @enderror" 
                                   id="current_pin" name="current_pin" placeholder="••••">
                            @error('current_pin')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="new_pin" class="form-label fw-semibold">New 4-Digit PIN</label>
                            <input type="password" maxlength="4" pattern="[0-9]{4}" class="form-control text-center fs-5 tracking-widest @error('new_pin') is-invalid @enderror" 
                                   id="new_pin" name="new_pin" placeholder="••••">
                            @error('new_pin')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="new_pin_confirmation" class="form-label fw-semibold">Confirm New PIN</label>
                            <input type="password" maxlength="4" pattern="[0-9]{4}" class="form-control text-center fs-5 tracking-widest" 
                                   id="new_pin_confirmation" name="new_pin_confirmation" placeholder="••••">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-camp-red px-4 py-2">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
