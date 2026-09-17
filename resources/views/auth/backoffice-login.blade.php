@extends('layouts.app')

@section('title', 'Staff Portal - Back-Office Sign In')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="camp-card p-4 p-sm-5 border-danger">
                <div class="text-center mb-4">
                    <img src="{{ asset('images/church-logo.jpg') }}" alt="40th Anniversary Logo" class="rounded bg-white p-2 mb-3 shadow" style="height: 65px; width: auto; object-fit: contain;">
                    <h3 class="fw-bold text-uppercase tracking-tight">Staff Back-Office</h3>
                    <p class="text-muted small">Authorized Personnel Only: Admin, Pastors, Registration & Campaign Staff</p>
                </div>

                <form action="{{ route('backoffice.login.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Staff Email</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 bg-transparent text-danger">
                                <i class="bi bi-envelope-fill"></i>
                            </span>
                            <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" placeholder="staff@church.org" required autofocus>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Staff PIN Input -->
                    <div class="mb-4">
                        <label for="pin" class="form-label fw-semibold">Staff 4-Digit PIN</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0 bg-transparent text-danger">
                                <i class="bi bi-shield-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control border-start-0 border-end-0 ps-0 @error('pin') is-invalid @enderror" 
                                   id="pin" name="pin" maxlength="4" pattern="[0-9]{4}" inputmode="numeric" placeholder="••••" required autocomplete="current-password"
                                   style="letter-spacing: 3px; font-size: 1.05rem;">
                            <button class="btn btn-outline-secondary border-start-0 text-muted" type="button" id="toggleStaffPin" style="border-color: #495057;">
                                <i class="bi bi-eye-slash" id="toggleStaffPinIcon"></i>
                            </button>
                        </div>
                        @error('pin')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                            <label class="form-check-label small text-muted" for="remember">Keep signed in</label>
                        </div>
                        <span class="small text-muted">Default PIN: <strong>0000</strong></span>
                    </div>

                    <button type="submit" class="btn btn-camp-red w-100 py-2 fs-6">
                        <i class="bi bi-shield-lock-fill me-2"></i> Authenticate Staff Access
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <span class="badge bg-dark border text-muted small px-3 py-2">
                        <i class="bi bi-shield-exclamation text-danger me-1"></i> Multi-season isolation & full activity audit active
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const pin = document.getElementById('pin');
    const toggle = document.getElementById('toggleStaffPin');
    const icon = document.getElementById('toggleStaffPinIcon');
    if (pin) {
        pin.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
    if (toggle && pin && icon) {
        toggle.addEventListener('click', function() {
            if (pin.type === 'password') {
                pin.type = 'text';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            } else {
                pin.type = 'password';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            }
        });
    }
});
</script>
@endpush
@endsection
