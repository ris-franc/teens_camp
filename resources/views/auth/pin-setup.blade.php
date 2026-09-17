@extends($isStaff ? 'layouts.backoffice' : 'layouts.app')

@section('title', 'Set Your 4-Digit PIN')

@push('styles')
<style>
.pin-setup-root {
    min-height: calc(100vh - 110px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
    background: #0d0d10;
}

.pin-setup-card {
    width: 100%;
    max-width: 460px;
    background: #141418;
    border: 1px solid rgba(255,255,255,.09);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,.5);
    position: relative;
    overflow: hidden;
}

.pin-setup-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: #D32F2F;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: #fff;
    margin: 0 auto 1.5rem;
    box-shadow: 0 8px 28px rgba(211,47,47,.4);
}

.pin-setup-input {
    background: rgba(255,255,255,.05) !important;
    border: 2px solid rgba(255,255,255,.12) !important;
    color: #fff !important;
    border-radius: 14px !important;
    font-size: 2rem !important;
    font-weight: 900 !important;
    letter-spacing: 1.5rem !important;
    text-align: center !important;
    padding: 1rem !important;
    transition: border-color .2s, background .2s !important;
}

.pin-setup-input:focus {
    background: rgba(255,255,255,.08) !important;
    border-color: rgba(239,68,68,.6) !important;
    box-shadow: 0 0 0 4px rgba(239,68,68,.12) !important;
    color: #fff !important;
}

.pin-setup-input::placeholder { color: rgba(255,255,255,.15) !important; }

.pin-strength {
    display: flex;
    gap: .4rem;
    justify-content: center;
    margin-top: .6rem;
}

.pin-strength-bar {
    flex: 1;
    height: 3px;
    border-radius: 99px;
    background: rgba(255,255,255,.1);
    transition: background .3s;
}

.pin-strength-bar.filled { background: #EF4444; }

.pin-match-indicator {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: .8rem;
    margin-top: .5rem;
    transition: color .2s;
}

.pin-setup-submit {
    background: #D32F2F;
    border: none;
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    border-radius: 12px;
    padding: 1rem;
    width: 100%;
    cursor: pointer;
    transition: all .15s ease;
    box-shadow: 0 4px 20px rgba(211,47,47,.35);
    margin-top: 1.25rem;
}

.pin-setup-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 6px 28px rgba(211,47,47,.5);
}

.pin-setup-submit:disabled { opacity: .4; cursor: not-allowed; }

.pin-setup-label {
    display: block;
    font-size: .78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: rgba(255,255,255,.45);
    margin-bottom: .5rem;
    text-align: center;
}
</style>
@endpush

@section('content')
<div class="pin-setup-root">
    <div class="pin-setup-card">

        <div class="pin-setup-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <div class="text-center mb-4">
            <h2 class="fw-black text-white mb-1" style="font-size:1.6rem;letter-spacing:-.5px;">
                Set Your Secret PIN
            </h2>
            <p class="mb-0" style="color:rgba(255,255,255,.45);font-size:.9rem;max-width:320px;margin:0 auto;">
                Welcome, <strong class="text-white">{{ $user->name }}</strong>!
                Choose your personal 4-digit PIN for secure future logins.
            </p>
        </div>

        @if($errors->any())
            <div class="d-flex align-items-center gap-2 p-3 rounded-3 mb-4"
                 style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);">
                <i class="bi bi-exclamation-circle-fill text-danger"></i>
                <span class="small text-danger fw-semibold">{{ $errors->first() }}</span>
            </div>
        @endif

        <form action="{{ $isStaff ? route('backoffice.pin.setup.post') : route('public.pin.setup.post') }}" method="POST" id="pinSetupForm">
            @csrf

            {{-- New PIN --}}
            <div class="mb-4">
                <label class="pin-setup-label" for="newPinInput">Choose 4-Digit PIN</label>
                <input type="password" maxlength="4" pattern="[0-9]{4}" inputmode="numeric"
                       class="pin-setup-input w-100 @error('pin') is-invalid @enderror"
                       id="newPinInput" name="pin"
                       placeholder="• • • •" required autocomplete="new-password" autofocus>

                {{-- Strength bars --}}
                <div class="pin-strength" id="pinStrength">
                    <div class="pin-strength-bar" data-pos="0"></div>
                    <div class="pin-strength-bar" data-pos="1"></div>
                    <div class="pin-strength-bar" data-pos="2"></div>
                    <div class="pin-strength-bar" data-pos="3"></div>
                </div>

                <p class="text-center mt-2 mb-0" style="font-size:.78rem;color:rgba(255,255,255,.3);">
                    Enter exactly 4 digits. Do not use <code style="color:#EF4444;">0000</code>.
                </p>
            </div>

            {{-- Confirm PIN --}}
            <div class="mb-2">
                <label class="pin-setup-label" for="pinConfirm">Confirm PIN</label>
                <input type="password" maxlength="4" pattern="[0-9]{4}" inputmode="numeric"
                       class="pin-setup-input w-100"
                       id="pinConfirm" name="pin_confirmation"
                       placeholder="• • • •" required autocomplete="new-password">

                <div class="pin-match-indicator justify-content-center" id="pinMatchIndicator" style="color:rgba(255,255,255,.25);">
                    <i class="bi bi-dash-circle" id="pinMatchIcon"></i>
                    <span id="pinMatchText">Enter both PINs to verify</span>
                </div>
            </div>

            <button type="submit" class="pin-setup-submit" id="pinSubmitBtn" disabled>
                <i class="bi bi-check-circle-fill me-2"></i> Save PIN &amp; Access Dashboard
            </button>
        </form>

        <div class="text-center mt-4 pt-3" style="border-top:1px solid rgba(255,255,255,.07);">
            <div class="small d-flex align-items-center justify-content-center gap-1"
                 style="color:rgba(255,255,255,.25);">
                <i class="bi bi-lock-fill text-danger"></i>
                PINs are encrypted &mdash; never stored in plaintext or visible to staff.
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const newPin   = document.getElementById('newPinInput');
    const confirm  = document.getElementById('pinConfirm');
    const bars     = document.querySelectorAll('.pin-strength-bar');
    const matchIco = document.getElementById('pinMatchIcon');
    const matchTxt = document.getElementById('pinMatchText');
    const matchEl  = document.getElementById('pinMatchIndicator');
    const submitBtn= document.getElementById('pinSubmitBtn');

    function updateStrength() {
        const len = newPin.value.length;
        bars.forEach((b, i) => b.classList.toggle('filled', i < len));
    }

    function updateMatch() {
        const p1 = newPin.value, p2 = confirm.value;
        if (!p2) {
            matchEl.style.color  = 'rgba(255,255,255,.25)';
            matchIco.className   = 'bi bi-dash-circle';
            matchTxt.textContent = 'Enter both PINs to verify';
        } else if (p1 === p2 && p1.length === 4 && p1 !== '0000') {
            matchEl.style.color  = '#4ade80';
            matchIco.className   = 'bi bi-check-circle-fill';
            matchTxt.textContent = 'PINs match ✓';
        } else if (p1 !== p2) {
            matchEl.style.color  = '#f87171';
            matchIco.className   = 'bi bi-x-circle-fill';
            matchTxt.textContent = 'PINs do not match';
        } else if (p1 === '0000') {
            matchEl.style.color  = '#fb923c';
            matchIco.className   = 'bi bi-exclamation-triangle-fill';
            matchTxt.textContent = '0000 is not allowed as a PIN';
        }

        const valid = p1.length === 4 && p1 === p2 && p1 !== '0000';
        submitBtn.disabled = !valid;
    }

    newPin.addEventListener('input', () => { updateStrength(); updateMatch(); });
    confirm.addEventListener('input', updateMatch);
})();
</script>
@endpush
@endsection
