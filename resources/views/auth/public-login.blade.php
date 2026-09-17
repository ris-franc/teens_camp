@extends('layouts.app')

@section('title', 'Sign In — Teen &amp; Parent Portal')

@push('styles')
<style>
/* ── Login Page Full-Screen Split Layout ── */
.login-root {
    min-height: calc(100vh - 110px);
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
}

@media (max-width: 991px) {
    .login-root { grid-template-columns: 1fr; }
    .login-panel-left { display: none; }
}

/* Left decorative panel */
.login-panel-left {
    position: relative;
    overflow: hidden;
    background: #0B0B0C;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 2.5rem;
}

.login-panel-left::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(211, 47, 47, 0.06);
    pointer-events: none;
}

.login-grid-lines {
    display: none;
}

.login-hero-text {
    position: relative;
    z-index: 1;
    text-align: center;
    color: #fff;
}

.login-hero-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(211,47,47,.2);
    border: 1px solid rgba(211,47,47,.5);
    border-radius: 999px;
    padding: .35rem 1rem;
    font-size: .75rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #EF4444;
    margin-bottom: 1.5rem;
}

.login-hero-title {
    font-family: 'Cinzel', Georgia, serif;
    font-size: clamp(2rem, 4vw, 3.2rem);
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: -0.5px;
    line-height: 1.1;
    margin-bottom: 1rem;
}

.login-hero-title span { color: #EF4444; }

.login-feature-pill {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem 1rem;
    border-radius: 999px;
    background: rgba(255,255,255,.05);
    border: 1px solid rgba(255,255,255,.08);
    font-size: .82rem;
    font-weight: 600;
    color: rgba(255,255,255,.75);
    white-space: nowrap;
}

.login-feature-pill i { color: #EF4444; }

/* Right login panel */
.login-panel-right {
    background: #111114;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1.5rem;
}

.login-card {
    width: 100%;
    max-width: 420px;
}

/* Glassmorphic separator */
.login-divider {
    display: flex;
    align-items: center;
    gap: .75rem;
    color: rgba(255,255,255,.25);
    font-size: .8rem;
    margin: 1.5rem 0;
}

.login-divider::before, .login-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: rgba(255,255,255,.1);
}

/* Form input styling */
.login-input {
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.12) !important;
    color: #fff !important;
    border-radius: 10px !important;
    padding: .75rem 1rem !important;
    transition: border-color .2s, background .2s !important;
}

.login-input:focus {
    background: rgba(255,255,255,.07) !important;
    border-color: rgba(239,68,68,.6) !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,.1) !important;
    color: #fff !important;
}

.login-input::placeholder { color: rgba(255,255,255,.3) !important; }

.login-submit {
    background: #D32F2F;
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: .5px;
    border-radius: 12px;
    padding: .85rem 1.5rem;
    width: 100%;
    cursor: pointer;
    transition: transform .12s, box-shadow .12s, opacity .12s;
    box-shadow: 0 4px 20px rgba(211,47,47,.35);
}

.login-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 28px rgba(211,47,47,.5);
}

.login-submit:active { transform: translateY(0); }
.login-submit:disabled { opacity: .5; cursor: not-allowed; transform: none; }

/* Floating label */
.login-label {
    display: block;
    font-size: .82rem;
    font-weight: 600;
    color: rgba(255,255,255,.55);
    text-transform: uppercase;
    letter-spacing: .7px;
    margin-bottom: .4rem;
}

/* Pulse animation on badge */
@keyframes pulse-red {
    0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.5); }
    50%       { box-shadow: 0 0 0 8px rgba(239,68,68,0); }
}
.pulse-badge { animation: pulse-red 2s infinite; }

/* Input group icon */
.login-icon-wrap {
    position: relative;
}

.login-icon-wrap .input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: rgba(255,255,255,.3);
    font-size: 1rem;
    pointer-events: none;
    z-index: 2;
}

.login-icon-wrap .login-input {
    padding-left: 2.6rem !important;
    padding-right: 2.6rem !important;
}

.pin-toggle-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    color: rgba(255,255,255,.4);
    cursor: pointer;
    font-size: 1.1rem;
    padding: 6px 8px;
    transition: color .2s;
    z-index: 2;
}

.pin-toggle-btn:hover {
    color: #EF4444;
}

/* Sub-text */
.login-subtext {
    font-size: .8rem;
    color: rgba(255,255,255,.35);
    text-align: center;
    margin-top: .5rem;
}

a.login-link {
    color: #EF4444;
    text-decoration: none;
    font-weight: 600;
}

a.login-link:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="login-root">

    {{-- ═══════════ LEFT: Branding Panel ═══════════ --}}
    <div class="login-panel-left">
        <div class="login-grid-lines"></div>

        <div class="login-hero-text">
            <img src="{{ asset('images/church-logo.jpg') }}" alt="Church Logo"
                 class="rounded bg-white p-2 shadow mb-4"
                 style="height:72px;width:auto;object-fit:contain;box-shadow:0 4px 24px rgba(211,47,47,.4);">

            <div class="login-hero-badge pulse-badge">
                <span style="width:7px;height:7px;border-radius:50%;background:#EF4444;flex-shrink:0;"></span>
                {{ $currentSeason ? ($currentSeason->isActive() ? 'Registration Open' : ucfirst($currentSeason->status)) : 'Participant Portal' }}
            </div>

            <h1 class="login-hero-title">
                Teen<span>Camp</span><br>Portal
            </h1>

            @if($currentSeason && $currentSeason->theme)
                <p class="fw-bold text-danger mb-4" style="font-size:1.1rem;letter-spacing:.5px;">
                    &ldquo;{{ $currentSeason->theme }}&rdquo;
                </p>
            @endif

            @if($currentSeason)
                <p class="mb-5" style="color:rgba(255,255,255,.5);font-size:.95rem;max-width:340px;line-height:1.7;">
                    {{ $currentSeason->name }}
                    &mdash; {{ $currentSeason->start_date->format('M d') }} to {{ $currentSeason->end_date->format('M d, Y') }}
                    &mdash; {{ $currentSeason->venue }}
                </p>
            @else
                <p class="mb-5" style="color:rgba(255,255,255,.5);font-size:.95rem;max-width:340px;line-height:1.7;">
                    Your secure gateway to camp forms, receipts, packing checklists and real-time camp updates.
                </p>
            @endif

            <div class="d-flex flex-wrap justify-content-center gap-2">
                <span class="login-feature-pill"><i class="bi bi-shield-fill-check"></i> 4-Digit PIN Auth</span>
                <span class="login-feature-pill"><i class="bi bi-phone"></i> M-Pesa Receipts</span>
                <span class="login-feature-pill"><i class="bi bi-people-fill"></i> Parent &amp; Teen Portals</span>
                <span class="login-feature-pill"><i class="bi bi-bell-fill"></i> Live Notifications</span>
            </div>
        </div>
    </div>

    {{-- ═══════════ RIGHT: Login Form ═══════════ --}}
    <div class="login-panel-right">
        <div class="login-card">

            {{-- Mobile logo (hidden on desktop where left panel shows) --}}
            <div class="text-center d-lg-none mb-4">
                <img src="{{ asset('images/church-logo.jpg') }}" alt="Logo"
                     class="rounded bg-white p-2 shadow" style="height:56px;width:auto;object-fit:contain;">
            </div>

            <div class="mb-4">
                <h2 class="fw-black text-white mb-1" style="font-size:1.9rem;letter-spacing:-.5px;">
                    Welcome back <span class="text-danger">.</span>
                </h2>
                <p class="mb-0" style="color:rgba(255,255,255,.45);font-size:.9rem;">
                    Sign in with your registered email and 4-digit PIN
                </p>
            </div>

            @if($errors->any())
                <div class="d-flex align-items-center gap-2 p-3 rounded mb-4"
                     style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);">
                    <i class="bi bi-exclamation-circle-fill text-danger flex-shrink-0"></i>
                    <span class="small text-danger fw-semibold">{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('public.login.post') }}" method="POST" id="loginForm">
                @csrf

                {{-- Email --}}
                <div class="mb-4">
                    <label class="login-label" for="loginEmail">Email Address</label>
                    <div class="login-icon-wrap">
                        <i class="bi bi-envelope-fill input-icon"></i>
                        <input type="email" class="login-input w-100" id="loginEmail" name="email"
                               value="{{ old('email') }}"
                               placeholder="your@email.com" required autocomplete="email" autofocus>
                    </div>
                </div>

                {{-- Standard PIN Input (No on-screen keypad) --}}
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="login-label mb-0" for="loginPin">4-Digit PIN</label>
                        <span class="small" style="color:rgba(255,255,255,.4);font-size:.78rem;">
                            Default: <strong style="color:#EF4444;">0000</strong>
                        </span>
                    </div>

                    <div class="login-icon-wrap">
                        <i class="bi bi-shield-lock-fill input-icon"></i>
                        <input type="password" class="login-input w-100" id="loginPin" name="pin"
                               maxlength="4" pattern="[0-9]{4}" inputmode="numeric"
                               placeholder="Enter 4-digit PIN" required autocomplete="current-password"
                               style="letter-spacing: 4px; font-size: 1.05rem;">
                        <button type="button" class="pin-toggle-btn" id="togglePinVisibility" title="Show or hide PIN">
                            <i class="bi bi-eye-slash-fill" id="togglePinIcon"></i>
                        </button>
                    </div>

                    <p class="login-subtext text-start mt-2 mb-0">
                        <i class="bi bi-info-circle text-danger me-1"></i>
                        First login? Enter PIN <strong style="color:#EF4444;">0000</strong>. You will be prompted to choose your personal PIN.
                    </p>
                </div>

                {{-- Remember me --}}
                <div class="d-flex align-items-center gap-2 mb-4">
                    <input type="checkbox" name="remember" id="remember"
                           class="form-check-input" style="width:18px;height:18px;cursor:pointer;" checked>
                    <label for="remember" class="mb-0 small" style="color:rgba(255,255,255,.5);cursor:pointer;">
                        Keep me signed in on this device
                    </label>
                </div>

                <button type="submit" class="login-submit" id="loginSubmitBtn" disabled>
                    <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Portal
                </button>
            </form>

            <div class="login-divider">or</div>

            <div class="text-center">
                <p class="mb-3" style="color:rgba(255,255,255,.4);font-size:.85rem;">
                    New to camp? Register your teen in 2 minutes:
                </p>
                <a href="{{ route('public.register') }}"
                   class="d-block w-100 py-3 rounded-3 text-center fw-bold text-decoration-none"
                   style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.8);transition:all .2s;"
                   onmouseover="this.style.background='rgba(239,68,68,.1)';this.style.borderColor='rgba(239,68,68,.3)'"
                   onmouseout="this.style.background='rgba(255,255,255,.04)';this.style.borderColor='rgba(255,255,255,.1)'">
                    <i class="bi bi-pencil-square text-danger me-2"></i>
                    Register
                </a>
            </div>

            <div class="text-center mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,.07);">
                <p class="login-subtext mb-0">
                    <i class="bi bi-lock-fill text-danger me-1"></i>
                    PINs are cryptographically hashed &mdash; never stored in plaintext.
                    Forgot your PIN? Contact the registration desk.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const emailInput = document.getElementById('loginEmail');
    const pinInput   = document.getElementById('loginPin');
    const submitBtn  = document.getElementById('loginSubmitBtn');
    const toggleBtn  = document.getElementById('togglePinVisibility');
    const toggleIcon = document.getElementById('togglePinIcon');

    function updateSubmitState() {
        const emailValid = emailInput.value.trim().length > 0;
        const pinValid   = pinInput.value.trim().length === 4;
        submitBtn.disabled = !(emailValid && pinValid);
    }

    emailInput.addEventListener('input', updateSubmitState);

    pinInput.addEventListener('input', function() {
        // Enforce numeric only
        this.value = this.value.replace(/[^0-9]/g, '');
        updateSubmitState();
    });

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (pinInput.type === 'password') {
                pinInput.type = 'text';
                toggleIcon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            } else {
                pinInput.type = 'password';
                toggleIcon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            }
        });
    }

    // Initial check
    updateSubmitState();
})();
</script>
@endpush
@endsection
