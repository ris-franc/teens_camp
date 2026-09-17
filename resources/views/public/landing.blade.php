@extends('layouts.app')

@section('title', $season ? $season->name . ' — Official Church Camp Portal' : 'Teen Camp 2026')

@push('styles')
<style>
/* ── Clean Modern Styles (No Gradients, Logo Typography) ── */
.landing-hero {
    position: relative;
    border-radius: 20px;
    background: #101014;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.5);
    padding: 3.5rem 2.5rem;
    margin-bottom: 3.5rem;
}

.landing-badge {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: rgba(211, 47, 47, 0.15);
    border: 1px solid rgba(211, 47, 47, 0.4);
    border-radius: 999px;
    padding: .35rem 1rem;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #EF4444;
}

.pulse-dot-red {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #EF4444;
    box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
    animation: pulseRedDot 2s infinite;
}

@keyframes pulseRedDot {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(239, 68, 68, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}

.landing-title {
    font-family: 'Cinzel', Georgia, 'Times New Roman', serif;
    font-size: clamp(2.4rem, 5vw, 3.8rem);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    line-height: 1.1;
    color: #fff;
    margin-bottom: .85rem;
}

.landing-theme {
    font-size: clamp(1.1rem, 2.5vw, 1.6rem);
    font-weight: 700;
    color: #EF4444;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 1.2rem;
}

.hero-pill-stat {
    background: #17171D;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: .85rem 1.2rem;
}

/* ── Camp Poster Showcase Frame ── */
.camp-poster-frame {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    background: #17171D;
    border: 2px solid rgba(211, 47, 47, 0.35);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
    aspect-ratio: 4/5;
    max-height: 480px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.camp-poster-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .4s ease;
}

.camp-poster-frame:hover .camp-poster-img {
    transform: scale(1.02);
}

.camp-poster-tag {
    position: absolute;
    bottom: 14px;
    left: 14px;
    right: 14px;
    background: rgba(11, 11, 14, 0.88);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    padding: .6rem .9rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    backdrop-filter: blur(8px);
}

/* Countdown Card */
.countdown-box-clean {
    background: #17171D;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    margin-top: 1.25rem;
}

.cd-digit-box {
    background: #0D0D10;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    padding: .75rem .4rem;
    text-align: center;
}

.cd-digit {
    font-size: 1.8rem;
    font-weight: 800;
    color: #EF4444;
    line-height: 1;
}

.cd-label {
    font-size: .65rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: rgba(255, 255, 255, 0.45);
    margin-top: .3rem;
}

/* Section Titles */
.section-title {
    font-family: 'Cinzel', Georgia, 'Times New Roman', serif;
    font-size: clamp(1.8rem, 3.2vw, 2.5rem);
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #fff;
    margin-bottom: .6rem;
}

/* Values Cards (Solid colors, No gradients) */
.value-card {
    background: #131317;
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 16px;
    padding: 2rem 1.6rem;
    height: 100%;
    transition: all .2s ease;
}

.value-card:hover {
    border-color: rgba(239, 68, 68, 0.4);
    transform: translateY(-3px);
}

.value-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: #1C1C24;
    border: 1px solid rgba(239, 68, 68, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #EF4444;
    margin-bottom: 1.25rem;
}

/* Clean Portal Log In Card (Concise, No bloat) */
.portal-login-card {
    background: #131317;
    border: 1px solid rgba(255, 255, 255, 0.09);
    border-radius: 20px;
    padding: 2.5rem;
}

.gateway-box {
    background: #1A1A22;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: border-color .2s;
}

.gateway-box:hover {
    border-color: rgba(239, 68, 68, 0.4);
}
</style>
@endpush

@section('content')
<div class="container py-3">

    {{-- ══════════════════════════════════════════════════════════
         1. HERO SECTION: CAMP POSTER & INVITATION
    ══════════════════════════════════════════════════════════ --}}
    <section class="landing-hero position-relative">
        <div class="row align-items-center g-4 g-lg-5">
            
            {{-- Left: The Invitation --}}
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset('images/church-logo.jpg') }}" alt="Church Logo"
                         class="rounded bg-white p-2 shadow-sm"
                         style="height: 56px; width: auto; object-fit: contain;">
                    <div class="landing-badge">
                        @if(!$season || $season->isRegistrationOpen())
                            <span class="pulse-dot-red"></span>
                            <span id="regStatusHeroText">Registration Open</span>
                        @else
                            <i class="bi bi-door-closed-fill text-muted me-1"></i>
                            <span id="regStatusHeroText" class="text-muted">Registration Closed</span>
                        @endif
                    </div>
                </div>

                <h1 class="landing-title">
                    {{ $season ? $season->name : 'Teen Camp 2026' }}
                </h1>

                @if($season && $season->theme)
                    <div class="landing-theme">
                        <i class="bi bi-fire me-1"></i> &ldquo;{{ $season->theme }}&rdquo;
                    </div>
                @endif

                <p class="fs-5 mb-4 text-white-50 pe-lg-3" style="line-height: 1.65; max-width: 580px;">
                    You are invited to an unforgettable, life-transforming week in Kenya! Experience high-impact mountain adventure, dynamic worship, small-group discipleship, and lifelong friendships.
                </p>

                {{-- Venue, Dates, Camp Fee Badges (Replaced Tuition with Camp Fee) --}}
                @if($season)
                    <div class="row g-2 mb-4 pe-lg-3">
                        <div class="col-sm-6">
                            <div class="hero-pill-stat">
                                <div class="text-danger small fw-bold text-uppercase mb-1">
                                    <i class="bi bi-geo-alt-fill me-1"></i> Venue &amp; Location
                                </div>
                                <div class="fw-bold text-white small">
                                    {{ $season->venue }}
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="hero-pill-stat">
                                <div class="text-danger small fw-bold text-uppercase mb-1">
                                    <i class="bi bi-calendar-event me-1"></i> Camp Dates
                                </div>
                                <div class="fw-bold text-white small">
                                    {{ $season->start_date->format('M d') }} &ndash; {{ $season->end_date->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="hero-pill-stat d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div>
                                    <span class="text-danger small fw-bold text-uppercase d-block">
                                        <i class="bi bi-cash-stack me-1"></i> All-Inclusive Camp Fee
                                    </span>
                                    <span class="text-white-50 small">Includes accommodation, all meals, sessions &amp; adventures</span>
                                </div>
                                <div class="fs-4 fw-black text-white">
                                    KES {{ number_format($season->price, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Action CTA Buttons --}}
                <div class="d-flex flex-wrap gap-3 align-items-center">
                    @if(!$season || $season->isRegistrationOpen())
                        <a href="{{ route('public.register') }}" class="btn btn-camp-red btn-lg px-4 py-3 shadow">
                            <i class="bi bi-pencil-square me-2"></i> Register
                        </a>
                    @else
                        <button class="btn btn-secondary btn-lg px-4 py-3" disabled title="Registration is closed for this season">
                            <i class="bi bi-door-closed me-2"></i> Registration Closed
                        </button>
                    @endif
                    <a href="{{ route('login') }}" class="btn btn-camp-dark btn-lg px-4 py-3">
                        <i class="bi bi-box-arrow-in-right me-2 text-danger"></i> Sign In
                    </a>
                </div>
            </div>

            {{-- Right: Camp Poster Display & Live Countdown --}}
            <div class="col-lg-5">
                {{-- Official Camp Poster Frame --}}
                <div class="camp-poster-frame">
                    <img src="{{ $season ? $season->getPosterUrl() : asset('images/camp-poster.jpg') }}"
                         onerror="this.onerror=null;this.src='{{ asset('images/hero-camp.jpg') }}';"
                         alt="{{ $season ? $season->name : 'Teen Camp' }} Official Poster"
                         class="camp-poster-img">
                    <div class="camp-poster-tag">
                        <span class="small text-white fw-bold">
                            <i class="bi bi-image text-danger me-1"></i> Camp Poster
                        </span>
                        <span class="badge bg-danger text-uppercase" style="font-size:10px;">
                            {{ $season ? $season->name : 'Teen Camp 2026' }}
                        </span>
                    </div>
                </div>

                {{-- Countdown Box: Remains Open For Registration Even After Countdown --}}
                <div class="countdown-box-clean text-white">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-danger text-uppercase px-2 py-1 small">
                            <i class="bi bi-hourglass-split me-1"></i> Starts in:
                        </span>
                        @if(!$season || $season->isRegistrationOpen())
                            <span class="small text-success fw-bold" id="camp-reg-status-pill">
                                <i class="bi bi-check-circle-fill me-1"></i> Registration Open
                            </span>
                        @else
                            <span class="small text-danger fw-bold" id="camp-reg-status-pill">
                                <i class="bi bi-door-closed-fill me-1"></i> Registration Closed
                            </span>
                        @endif
                    </div>

                    {{-- 4 Digit Countdown --}}
                    <div class="row g-2 my-2" id="heroCountdownDigits">
                        <div class="col-3">
                            <div class="cd-digit-box">
                                <div class="cd-digit" id="camp-cd-days-lg">00</div>
                                <div class="cd-label">Days</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="cd-digit-box">
                                <div class="cd-digit" id="camp-cd-hours-lg">00</div>
                                <div class="cd-label">Hours</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="cd-digit-box">
                                <div class="cd-digit" id="camp-cd-mins-lg">00</div>
                                <div class="cd-label">Mins</div>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="cd-digit-box">
                                <div class="cd-digit" id="camp-cd-secs-lg">00</div>
                                <div class="cd-label">Secs</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-2">
                        @if(!$season || $season->isRegistrationOpen())
                            <span class="small text-white-50">
                                <i class="bi bi-info-circle text-danger me-1"></i> Registration remains open before and throughout camp.
                            </span>
                        @else
                            <span class="small text-white-50">
                                <i class="bi bi-exclamation-circle text-danger me-1"></i> Registration is currently closed by the camp administrators.
                            </span>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </section>


    {{-- ══════════════════════════════════════════════════════════
         2. OUR CAMP VALUES (SOLID BACKGROUNDS, NO GRADIENTS)
    ══════════════════════════════════════════════════════════ --}}
    <section class="py-3 mb-5" id="camp-values">
        <div class="text-center mb-4">
            <h2 class="section-title">Our Camp Values</h2>
            <p class="text-white-50 mb-0">
                The core pillars guiding every activity, devotion, and friendship at Teen Camp.
            </p>
        </div>

        @php
            $coreValues = $season ? $season->getCoreValues() : (new \App\Models\CampSeason())->getCoreValues();
        @endphp

        <div class="row g-4">
            @foreach($coreValues as $index => $val)
                @php
                    $colClass = 'col-md-6 col-lg-4';
                    if (count($coreValues) % 3 !== 0 && $index >= count($coreValues) - (count($coreValues) % 3)) {
                        $colClass = (count($coreValues) % 3 === 1) ? 'col-12 col-lg-8 mx-auto' : 'col-md-6 col-lg-6';
                    }
                @endphp
                <div class="{{ $colClass }}">
                    <div class="value-card h-100">
                        <div class="value-icon-box">
                            <i class="bi {{ $val['icon'] ?? 'bi-stars' }}"></i>
                        </div>
                        <h5 class="fw-bold text-white mb-2" style="font-family: 'Cinzel', Georgia, serif;">{{ $val['title'] }}</h5>
                        <p class="text-white-50 small mb-0" style="line-height: 1.7;">
                            {{ $val['description'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>


    {{-- ══════════════════════════════════════════════════════════
         3. LOG IN SECTION (CLEAN & CONCISE — NO SYSTEM OVERLOAD)
    ══════════════════════════════════════════════════════════ --}}
    <section class="py-3 mb-5" id="portal-login">
        <div class="portal-login-card text-white">
            <div class="row align-items-center g-4">
                
                <div class="col-lg-5">
                    <span class="badge bg-danger text-uppercase px-2 py-1 mb-2" style="font-size: 11px;">
                        <i class="bi bi-key-fill me-1"></i> Sign In
                    </span>
                    <h2 class="section-title mb-2">Log In to Camp Portal</h2>
                    <p class="text-white-50 mb-4" style="line-height: 1.6;">
                        Sign in with your registered email address and 4-digit PIN.
                    </p>
                    <a href="{{ route('login') }}" class="btn btn-camp-red btn-lg px-4 py-3 w-100 shadow text-center">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Go to Sign In Page &rarr;
                    </a>
                </div>

                {{-- Simple, Clean Dual Gateways --}}
                <div class="col-lg-7">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="gateway-box">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="p-2 rounded bg-dark border border-danger text-danger d-inline-flex">
                                            <i class="bi bi-people-fill fs-5"></i>
                                        </div>
                                        <span class="badge bg-danger text-uppercase" style="font-size: 10px;">Parents</span>
                                    </div>
                                    <h5 class="fw-bold text-white mb-1">Parent Portal</h5>
                                    <p class="text-white-50 small mb-3">
                                        Pay camp fees, view receipts, and review declarations.
                                    </p>
                                </div>
                                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm w-100 py-2">
                                    Sign In as Parent &rarr;
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="gateway-box">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="p-2 rounded bg-dark border border-secondary text-white d-inline-flex">
                                            <i class="bi bi-person-badge fs-5"></i>
                                        </div>
                                        <span class="badge bg-dark border text-white-50 text-uppercase" style="font-size: 10px;">Teens</span>
                                    </div>
                                    <h5 class="fw-bold text-white mb-1">Teen Portal</h5>
                                    <p class="text-white-50 small mb-3">
                                        View packing checklists, camp schedule, and forms.
                                    </p>
                                </div>
                                <a href="{{ route('login') }}" class="btn btn-outline-light btn-sm w-100 py-2">
                                    Sign In as Teen &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
@if($season)
<script>
document.addEventListener('DOMContentLoaded', function() {
    const target = new Date("{{ $season->start_date->toIso8601String() }}").getTime();
    function updateHeroTimer() {
        const now = new Date().getTime();
        const diff = target - now;
        const daysEl = document.getElementById('camp-cd-days-lg');
        const hoursEl = document.getElementById('camp-cd-hours-lg');
        const minsEl = document.getElementById('camp-cd-mins-lg');
        const secsEl = document.getElementById('camp-cd-secs-lg');
        const statusPill = document.getElementById('camp-reg-status-pill');
        const heroStatusText = document.getElementById('regStatusHeroText');

        if (diff <= 0) {
            // After countdown expires, registration is STILL explicitly OPEN
            if (daysEl)  daysEl.textContent = '00';
            if (hoursEl) hoursEl.textContent = '00';
            if (minsEl)  minsEl.textContent = '00';
            if (secsEl)  secsEl.textContent = '00';
            if (statusPill) {
                statusPill.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Camp in Session — Registration Open';
            }
            if (heroStatusText) {
                heroStatusText.textContent = 'Registration Open';
            }
            return;
        }

        if (daysEl)  daysEl.textContent = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0');
        if (hoursEl) hoursEl.textContent = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
        if (minsEl)  minsEl.textContent = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
        if (secsEl)  secsEl.textContent = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
    }
    updateHeroTimer();
    setInterval(updateHeroTimer, 1000);
});
</script>
@endif
@endpush
