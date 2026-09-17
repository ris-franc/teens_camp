@extends('layouts.app')

@section('title', '2-Minute Camper Registration — ' . ($season ? $season->name : 'Teen Camp'))

@push('styles')
<style>
/* ── Registration Page Styles ── */
.reg-page {
    min-height: calc(100vh - 110px);
    background: #0D0D10;
}

/* Hero Banner */
.reg-hero {
    position: relative;
    padding: 3.5rem 0 2.5rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
    background: #111115;
}

/* Step Progress Bar */
.reg-steps {
    display: flex;
    align-items: center;
    gap: 0;
    margin: 2.5rem 0;
}

.reg-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .35rem;
    position: relative;
    flex: 1;
    text-align: center;
}

.reg-step-num {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: .9rem;
    border: 2px solid rgba(255,255,255,.12);
    background: rgba(255,255,255,.04);
    color: rgba(255,255,255,.35);
    transition: all .3s ease;
    z-index: 2;
    position: relative;
}

.reg-step.active .reg-step-num {
    background: #D32F2F;
    border-color: #D32F2F;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(211,47,47,.2), 0 4px 16px rgba(211,47,47,.4);
}

.reg-step.done .reg-step-num {
    background: #16a34a;
    border-color: #16a34a;
    color: #fff;
}

.reg-step-label {
    font-size: .72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: rgba(255,255,255,.3);
    transition: color .3s;
}

.reg-step.active .reg-step-label { color: #EF4444; }
.reg-step.done  .reg-step-label  { color: #4ade80; }

.reg-step::before {
    content: '';
    position: absolute;
    top: 20px;
    left: calc(-50% + 20px);
    right: calc(50% + 20px);
    height: 2px;
    background: rgba(255,255,255,.08);
    z-index: 1;
}

.reg-step:first-child::before { display: none; }

/* Section Card */
.reg-section {
    display: none;
    animation: fadeInUp .35s ease;
}

.reg-section.active { display: block; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* Form Inputs */
.reg-input {
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.1) !important;
    color: #fff !important;
    border-radius: 10px !important;
    padding: .75rem 1rem !important;
    transition: border-color .2s, background .2s !important;
}

.reg-input:focus {
    background: rgba(255,255,255,.07) !important;
    border-color: rgba(239,68,68,.55) !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,.1) !important;
    color: #fff !important;
}

.reg-input::placeholder { color: rgba(255,255,255,.25) !important; }

.reg-select {
    background: rgba(255,255,255,.04) !important;
    border: 1px solid rgba(255,255,255,.1) !important;
    color: #fff !important;
    border-radius: 10px !important;
    padding: .75rem 1rem !important;
}

.reg-select option { background: #1a1a1e; color: #fff; }

.reg-select:focus {
    border-color: rgba(239,68,68,.55) !important;
    box-shadow: 0 0 0 3px rgba(239,68,68,.1) !important;
}

.reg-label {
    font-size: .8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: rgba(255,255,255,.5);
    margin-bottom: .4rem;
    display: flex;
    align-items: center;
    gap: .35rem;
}

.reg-label .req { color: #EF4444; }

/* Section heading */
.reg-section-heading {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.25rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.75rem;
}

.reg-section-heading.parent { background: rgba(59,130,246,.08); border-left: 3px solid #3b82f6; }
.reg-section-heading.teen   { background: rgba(234,179,8,.08);  border-left: 3px solid #eab308; }
.reg-section-heading.health { background: rgba(239,68,68,.08);  border-left: 3px solid #ef4444; }
.reg-section-heading.payment{ background: rgba(34,197,94,.08);  border-left: 3px solid #22c55e; }
.reg-section-heading.custom { background: rgba(168,85,247,.08); border-left: 3px solid #a855f7; }

.reg-section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.reg-section-heading.parent  .reg-section-icon { background: rgba(59,130,246,.15);  color: #60a5fa; }
.reg-section-heading.teen    .reg-section-icon { background: rgba(234,179,8,.15);   color: #fbbf24; }
.reg-section-heading.health  .reg-section-icon { background: rgba(239,68,68,.15);   color: #f87171; }
.reg-section-heading.payment .reg-section-icon { background: rgba(34,197,94,.15);   color: #4ade80; }
.reg-section-heading.custom  .reg-section-icon { background: rgba(168,85,247,.15);  color: #c084fc; }

/* Nav buttons */
.reg-nav-btn {
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: .95rem;
    padding: .8rem 2rem;
    cursor: pointer;
    transition: all .15s ease;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
}

.reg-nav-btn.next {
    background: #D32F2F;
    color: #fff;
    box-shadow: 0 4px 14px rgba(211,47,47,.35);
}

.reg-nav-btn.next:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(211,47,47,.5); }

.reg-nav-btn.prev {
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.7);
    border: 1px solid rgba(255,255,255,.1);
}

.reg-nav-btn.prev:hover { background: rgba(255,255,255,.1); }

.reg-nav-btn.submit {
    background: #B71C1C;
    color: #fff;
    font-size: 1.05rem;
    padding: 1rem 2.5rem;
    box-shadow: 0 4px 18px rgba(183,28,28,.4);
}

/* M-Pesa card */
.mpesa-card {
    background: #141418;
    border: 1px solid rgba(34,197,94,.3);
    border-radius: 14px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.25rem;
}

.mpesa-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    background: #16a34a;
    color: #fff;
    font-size: .78rem;
    font-weight: 700;
    padding: .3rem .75rem;
    border-radius: 999px;
    letter-spacing: .5px;
}

.mpesa-till {
    font-size: 1.4rem;
    font-weight: 900;
    color: #fff;
    background: rgba(211,47,47,.12);
    border: 1px solid rgba(211,47,47,.3);
    border-radius: 8px;
    padding: .25rem .75rem;
    display: inline-block;
    letter-spacing: 2px;
}


/* Checkbox toggle */
.reg-toggle-row {
    display: flex;
    align-items: center;
    gap: .9rem;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.08);
    cursor: pointer;
    transition: background .15s, border-color .15s;
}

.reg-toggle-row:hover { background: rgba(255,255,255,.06); }

.reg-toggle-row input:checked ~ .reg-toggle-label { color: #fff; }

/* Error highlight */
.reg-error { color: #f87171; font-size: .8rem; margin-top: .3rem; }

/* Responsive */
@media (max-width: 768px) {
    .reg-step-label { display: none; }
    .reg-nav-btn { padding: .7rem 1.25rem; font-size: .85rem; }
}
</style>
@endpush

@section('content')
<div class="reg-page">

    {{-- ════════════════════ HERO BANNER ════════════════════ --}}
    <div class="reg-hero">
        <div class="reg-hero-grid"></div>
        <div class="reg-hero-glow"></div>

        <div class="container position-relative">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ asset('images/church-logo.jpg') }}" alt="Logo"
                             class="rounded bg-white p-1 shadow-sm"
                             style="height:48px;width:auto;object-fit:contain;">
                        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill"
                             style="background:rgba(211,47,47,.18);border:1px solid rgba(211,47,47,.45);">
                            <i class="bi bi-stopwatch-fill text-danger"></i>
                            <span class="text-uppercase fw-bold text-danger small" style="letter-spacing:.7px;">
                                Express Camper Registration &bull; 2-Minute Intake
                            </span>
                        </div>
                    </div>

                    <h1 class="fw-black text-white text-uppercase mb-2"
                        style="font-size:clamp(1.8rem,4vw,3rem);letter-spacing:-1px;line-height:1.05;">
                        Register Your<br>
                        <span class="text-danger">Teen Camper</span>
                    </h1>

                    <p class="mb-0" style="color:rgba(255,255,255,.5);font-size:1rem;max-width:540px;line-height:1.7;">
                        Sign up for <strong class="text-white">{{ $season ? $season->name : 'Teen Camp 2026' }}</strong> in under 2 minutes.
                        Instant account creation, no passwords &mdash; just your email and a 4-digit PIN.
                    </p>
                </div>

                <div class="col-lg-4">
                    @if($season)
                    <div class="text-center p-3 rounded-3" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                        <div class="small text-uppercase fw-bold mb-2" style="color:rgba(255,255,255,.4);letter-spacing:.8px;">Camp Details</div>
                        <div class="fw-bold text-white mb-1">{{ $season->name }}</div>
                        @if($season->theme)
                            <div class="text-danger small fw-semibold mb-2">&ldquo;{{ $season->theme }}&rdquo;</div>
                        @endif
                        <div class="d-flex flex-column gap-1">
                            <div class="small" style="color:rgba(255,255,255,.5);">
                                <i class="bi bi-calendar3 text-danger me-1"></i>
                                {{ $season->start_date->format('M d') }} &ndash; {{ $season->end_date->format('M d, Y') }}
                            </div>
                            <div class="small" style="color:rgba(255,255,255,.5);">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                {{ $season->venue }}
                            </div>
                            <div class="mt-2">
                                <span class="badge bg-danger px-3 py-2 fs-6">KES {{ number_format($season->price, 0) }}</span>
                            </div>
                        </div>
                        <div class="mt-3 pt-2" style="border-top:1px solid rgba(255,255,255,.08);">
                            <a href="{{ route('login') }}" class="small text-decoration-none" style="color:rgba(255,255,255,.4);">
                                Already registered? <span class="text-danger fw-bold">Sign In →</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════ CONTENT ════════════════════ --}}
    <div class="container py-4">

        @if(!$season || !$season->isRegistrationOpen())
            <div class="text-center py-5 my-5">
                <div class="d-inline-flex p-4 rounded-circle mb-3" style="background:rgba(239,68,68,.1);">
                    <i class="bi bi-door-closed fs-1 text-danger"></i>
                </div>
                <h3 class="text-white fw-bold">Registration Currently Closed</h3>
                <p class="mt-2" style="color:rgba(255,255,255,.4);max-width:480px;margin:0 auto;">
                    {{ $season ? 'Online camper registration for ' . $season->name . ' has been closed by camp administration.' : 'There is no active camp season at the moment.' }}
                    If you are already registered, you can log in below to access your camp portal.
                </p>
                <div class="mt-4">
                    <a href="{{ route('login') }}" class="btn btn-camp-red px-4 py-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Camp Portal
                    </a>
                </div>
            </div>
        @else

        @if(session('success'))
            <div class="d-flex align-items-center gap-3 p-4 rounded-3 mb-4"
                 style="background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);">
                <i class="bi bi-check-circle-fill text-success fs-4 flex-shrink-0"></i>
                <span class="text-white fw-semibold">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="d-flex align-items-center gap-3 p-4 rounded-3 mb-4"
                 style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-4 flex-shrink-0"></i>
                <div>
                    <div class="fw-bold text-danger small mb-1">Please fix the following:</div>
                    @foreach($errors->all() as $e)
                        <div class="small" style="color:rgba(255,255,255,.7);">{{ $e }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ════ STEPPER ════ --}}
        @php
            $sections    = [
                'parent'       => ['icon'=>'bi-person-heart',    'label'=>'Parent Info',    'color'=>'parent'],
                'teen'         => ['icon'=>'bi-person-fill',     'label'=>'Teen Details',   'color'=>'teen'],
                'declarations' => ['icon'=>'bi-shield-plus',     'label'=>'Health & Camp',  'color'=>'health'],
                'payment'      => ['icon'=>'bi-phone-vibrate',   'label'=>'Payment',        'color'=>'payment'],
                'custom'       => ['icon'=>'bi-puzzle',          'label'=>'Additional',     'color'=>'custom'],
            ];
            $enabledFields   = $formConfig->enabledFields();
            $fieldsBySection = collect($enabledFields)->groupBy('section');
            $filledSections  = collect($sections)->filter(fn($m,$k) => ($fieldsBySection[$k] ?? collect())->isNotEmpty());
            $sectionKeys     = $filledSections->keys()->values();
            $totalSteps      = $sectionKeys->count();
        @endphp

        <div class="row justify-content-center">
            {{-- Main form column --}}
            <div class="col-lg-10 col-xl-9">

                {{-- Step indicators --}}
                <div class="reg-steps mb-2" id="stepIndicators">
                    @foreach($filledSections as $sKey => $sMeta)
                    <div class="reg-step {{ $loop->first ? 'active' : '' }}" data-step="{{ $loop->index }}">
                        <div class="reg-step-num">
                            {{ $loop->first ? '' : $loop->iteration }}
                            @if($loop->first)<i class="bi bi-1-circle-fill" style="font-size:.9rem;"></i>@else{{ $loop->iteration }}@endif
                        </div>
                        <span class="reg-step-label">{{ $sMeta['label'] }}</span>
                    </div>
                    @endforeach
                </div>

                <form action="{{ route('public.register.post') }}" method="POST" id="regForm">
                    @csrf

                    @foreach($filledSections as $sectionKey => $meta)
                    @php
                        $sectionIdx = $loop->index;
                        $isLast     = $loop->last;
                        $colorClass = $meta['color'];
                    @endphp
                    <div class="reg-section {{ $loop->first ? 'active' : '' }}" data-section="{{ $sectionIdx }}">

                        {{-- Section heading --}}
                        <div class="reg-section-heading {{ $colorClass }}">
                            <div class="reg-section-icon">
                                <i class="bi {{ $meta['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-white" style="font-size:1.1rem;">
                                    {{ $meta['label'] }}
                                </div>
                                <div style="font-size:.83rem;color:rgba(255,255,255,.45);">
                                    @if($sectionKey === 'parent')    Enter your details as the parent or guardian
                                    @elseif($sectionKey === 'teen')  Your teen's personal details for their portal
                                    @elseif($sectionKey === 'declarations') Health and camp activity declarations
                                    @elseif($sectionKey === 'payment') Optional deposit or full payment via M-Pesa
                                    @else Additional camp-specific information
                                    @endif
                                </div>
                            </div>
                            <div class="ms-auto text-end">
                                <div class="small fw-bold" style="color:rgba(255,255,255,.3);">
                                    Step {{ $sectionIdx + 1 }} / {{ $totalSteps }}
                                </div>
                            </div>
                        </div>

                        {{-- M-Pesa card (payment section only) --}}
                        @if($sectionKey === 'payment')
                        <div class="mpesa-card mb-4">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="mb-2">
                                        <span class="mpesa-badge"><i class="bi bi-phone-fill"></i> Lipa na M-Pesa Paybill</span>
                                    </div>
                                    <div class="mb-1 small" style="color:rgba(255,255,255,.7);">
                                        <strong class="text-white">Paybill:</strong> <span class="mpesa-till ms-1">{{ \App\Models\MpesaSetting::getSettings()->paybill_number ?? '880100' }}</span>
                                    </div>
                                    <div class="small" style="color:rgba(255,255,255,.6);">
                                        <strong class="text-white">Account:</strong> {{ \App\Models\MpesaSetting::getSettings()->camp_fee_account ?? 'CAMP-FEES' }} (or Camper's Name)
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="small text-uppercase fw-bold mb-1" style="color:rgba(255,255,255,.35);letter-spacing:.8px;">Camp Fee</div>
                                    <div class="fw-black" style="font-size:1.9rem;color:#EF4444;">
                                        KES {{ number_format($season->price, 0) }}
                                    </div>
                                    <div class="small" style="color:rgba(255,255,255,.35);">Pay now or later from dashboard</div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Fields grid --}}
                        <div class="row g-3">
                            @foreach($fieldsBySection[$sectionKey] ?? [] as $field)
                            @php
                                $colClass = match(true) {
                                    $field['type'] === 'textarea' => 'col-12',
                                    $field['type'] === 'checkbox' => 'col-12',
                                    default                       => 'col-md-6',
                                };
                            @endphp
                            <div class="{{ $colClass }}">

                                @if($field['type'] === 'checkbox')
                                    <label class="reg-toggle-row w-100">
                                        <input class="form-check-input flex-shrink-0" type="checkbox"
                                               name="{{ $field['key'] }}" value="1"
                                               {{ old($field['key']) ? 'checked' : '' }}
                                               style="width:22px;height:22px;cursor:pointer;accent-color:#D32F2F;">
                                        <div>
                                            <div class="fw-semibold text-white small">{{ $field['label'] }}</div>
                                            @if($field['key'] === 'phone_carried')
                                                <div class="small mt-1" style="color:rgba(255,255,255,.4);">
                                                    Devices are stored safely per our digital wellness policy
                                                </div>
                                            @endif
                                        </div>
                                    </label>

                                @elseif($field['type'] === 'select' && !empty($field['options']))
                                    <label class="reg-label">
                                        <i class="bi bi-chevron-down" style="font-size:.7rem;opacity:.6;"></i>
                                        {{ $field['label'] }}
                                        @if($field['required'] ?? false)<span class="req">*</span>@endif
                                    </label>
                                    <select class="reg-select w-100" name="{{ $field['key'] }}"
                                            {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                        @if(!($field['required'] ?? false))
                                            <option value="">— Select —</option>
                                        @endif
                                        @foreach($field['options'] as $opt)
                                            <option value="{{ $opt }}" {{ old($field['key']) === $opt ? 'selected' : '' }}>
                                                {{ ucfirst($opt) }}
                                            </option>
                                        @endforeach
                                    </select>

                                @elseif($field['type'] === 'textarea')
                                    <label class="reg-label">
                                        {{ $field['label'] }}
                                        @if($field['required'] ?? false)<span class="req">*</span>@endif
                                    </label>
                                    <textarea class="reg-input w-100" name="{{ $field['key'] }}"
                                              rows="3" placeholder="{{ $field['label'] }}"
                                              {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ old($field['key']) }}</textarea>

                                @else
                                    <label class="reg-label">
                                        {{ $field['label'] }}
                                        @if($field['required'] ?? false)<span class="req">*</span>@endif
                                    </label>
                                    <input type="{{ $field['type'] }}"
                                           class="reg-input w-100 {{ $field['key'] === 'payment_reference' ? 'text-uppercase' : '' }}"
                                           name="{{ $field['key'] }}"
                                           value="{{ old($field['key']) }}"
                                           {{ ($field['required'] ?? false) ? 'required' : '' }}
                                           placeholder="{{ $field['label'] }}"
                                           @if($field['key'] === 'initial_payment') min="0" max="{{ $season->price }}" @endif>

                                    @if($field['key'] === 'parent_email')
                                        <div class="reg-error" style="color:rgba(255,255,255,.35) !important;">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Primary contact for receipts and emergency alerts
                                        </div>
                                    @elseif($field['key'] === 'teen_email')
                                        <div class="reg-error" style="color:rgba(255,255,255,.35) !important;">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Used for teen portal login
                                        </div>
                                    @elseif($field['key'] === 'initial_payment')
                                        <div class="reg-error" style="color:rgba(255,255,255,.35) !important;">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Optional — leave blank to pay later from your dashboard
                                        </div>
                                    @elseif($field['key'] === 'payment_reference')
                                        <div class="reg-error" style="color:rgba(255,255,255,.35) !important;">
                                            <i class="bi bi-info-circle me-1"></i>
                                            10-character M-Pesa code from your Safaricom SMS
                                        </div>
                                    @endif
                                @endif

                                @error($field['key'])
                                    <div class="reg-error"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror
                            </div>
                            @endforeach
                        </div>

                        {{-- Navigation buttons --}}
                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3"
                             style="border-top:1px solid rgba(255,255,255,.07);">
                            @if($sectionIdx > 0)
                                <button type="button" class="reg-nav-btn prev"
                                        onclick="goToStep({{ $sectionIdx - 1 }})">
                                    <i class="bi bi-arrow-left"></i> Back
                                </button>
                            @else
                                <div></div>
                            @endif

                            @if(!$isLast)
                                <button type="button" class="reg-nav-btn next"
                                        onclick="goToStep({{ $sectionIdx + 1 }})">
                                    Next: {{ $filledSections->values()[$sectionIdx + 1]['label'] ?? 'Continue' }}
                                    <i class="bi bi-arrow-right"></i>
                                </button>
                            @else
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <button type="submit" class="reg-nav-btn submit">
                                        <i class="bi bi-check-circle-fill"></i>
                                        Complete Registration
                                    </button>
                                    <span class="small" style="color:rgba(255,255,255,.3);">
                                        <i class="bi bi-shield-check text-success me-1"></i>
                                        Accounts created instantly with PIN <code style="color:#EF4444;">0000</code>
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach

                </form>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function() {
    const totalSteps = {{ $totalSteps ?? 1 }};
    let currentStep  = 0;

    // If there are validation errors, jump to the right step
    @if($errors->any())
    (function findErrorStep() {
        const errorFields = {!! json_encode($errors->keys()) !!};
        const sectionKeys = {!! json_encode($sectionKeys->toArray()) !!};
        const fieldMap    = {!! json_encode($fieldsBySection->map(fn($f) => $f->pluck('key')->toArray())->toArray()) !!};

        for (let s = 0; s < sectionKeys.length; s++) {
            const sk = sectionKeys[s];
            const fields = fieldMap[sk] || [];
            for (const ef of errorFields) {
                if (fields.includes(ef)) { currentStep = s; break; }
            }
        }
        goToStep(currentStep);
    })();
    @endif

    window.goToStep = function(step) {
        if (step < 0 || step >= totalSteps) return;

        // Mark indicators
        document.querySelectorAll('.reg-step').forEach((el, i) => {
            el.classList.toggle('active', i === step);
            el.classList.toggle('done', i < step);
        });

        // Show sections
        document.querySelectorAll('.reg-section').forEach((el, i) => {
            el.classList.toggle('active', i === step);
        });

        currentStep = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
})();
</script>
@endpush
@endsection
