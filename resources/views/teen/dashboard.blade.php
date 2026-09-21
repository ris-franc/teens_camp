@extends('layouts.app')

@section('title', 'Teen Camper Portal — ' . ($user->name ?? 'Dashboard'))

@push('styles')
<style>
/* ── Modern Teen Camper Dashboard ── */
.teen-hero-banner {
    position: relative;
    border-radius: 20px;
    background: #111115;
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 2.2rem;
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
}

.teen-avatar-box {
    width: 64px;
    height: 64px;
    border-radius: 18px;
    background: #EF4444;
    color: #fff;
    font-size: 1.6rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.4);
    flex-shrink: 0;
}

.teen-status-card {
    background: rgba(255, 255, 255, 0.03);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 18px;
    padding: 1.75rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: border-color .2s;
}

.teen-status-card:hover {
    border-color: rgba(239, 68, 68, 0.35);
}

.teen-form-card {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 16px;
    padding: 1.5rem;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: all .2s;
}

.teen-form-card:hover {
    border-color: rgba(239, 68, 68, 0.3);
    background: rgba(255, 255, 255, 0.04);
}

.packing-category-card {
    background: rgba(255, 255, 255, 0.025);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: 16px;
    padding: 1.4rem;
    height: 100%;
}
</style>
@endpush

@section('content')
<div class="container py-3">

    {{-- ══════════════════════════════════════════════════════════
         TEEN WELCOME HERO BANNER
    ══════════════════════════════════════════════════════════ --}}
    <div class="teen-hero-banner mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="teen-avatar-box">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-danger text-uppercase px-2 py-1" style="font-size: 11px;">Teen Camper</span>
                        @if($season && $season->theme)
                            <span class="badge bg-dark border border-danger text-danger px-2 py-1" style="font-size: 11px;">
                                <i class="bi bi-fire me-1"></i> {{ $season->theme }}
                            </span>
                        @endif
                    </div>
                    <h2 class="fw-black text-white mb-0" style="letter-spacing: -.5px;">
                        Hey, {{ $user->name }}! 🏕️
                    </h2>
                    <p class="text-white-50 small mb-0 mt-1">
                        Welcome to your camp headquarters for <strong>{{ $season ? $season->name : 'Teen Camp 2026' }}</strong>.
                    </p>
                </div>
            </div>

            <div>
                @if($registration)
                    @if($registration->status === 'signed_in')
                        <span class="badge bg-success py-2 px-3 fs-6 rounded-pill">
                            <i class="bi bi-check-circle-fill me-1"></i> Checked In to Camp!
                        </span>
                    @elseif($registration->status === 'withdrawn')
                        <span class="badge bg-secondary py-2 px-3 fs-6 rounded-pill">
                            <i class="bi bi-slash-circle me-1"></i> Registration Withdrawn
                        </span>
                    @else
                        <span class="badge bg-danger py-2 px-3 fs-6 rounded-pill">
                            <i class="bi bi-patch-check-fill me-1"></i> Officially Registered
                        </span>
                    @endif
                @else
                    <span class="badge bg-warning text-dark py-2 px-3 fs-6 rounded-pill">
                        Registration Pending
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════
         TOP ROW: FEE STATUS (VIEW ONLY) & CAMP LOGISTICS
    ══════════════════════════════════════════════════════════ --}}
    <div class="row g-4 mb-4">
        
        {{-- Card 1: Camp Fee Status (View Only) --}}
        <div class="col-lg-6">
            <div class="teen-status-card">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                        <h5 class="fw-bold text-white mb-0">
                            <i class="bi bi-wallet2 text-danger me-2"></i> Camp Fee Status
                        </h5>
                        <span class="badge bg-dark border border-secondary text-white-50">View Only</span>
                    </div>

                    <p class="text-white-50 small mb-3">
                        Camp fee payments and financial aid are managed directly by your parent or guardian.
                    </p>

                    @if($registration && $season)
                        <div class="p-3 rounded mb-3" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.08);">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <div>
                                    <span class="text-white-50 small text-uppercase fw-bold">Paid to Date:</span>
                                    <div class="fs-4 fw-black text-danger">KES {{ number_format($registration->total_paid, 2) }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="text-white-50 small text-uppercase fw-bold">Total Camp Fee:</span>
                                    <div class="fs-5 fw-bold text-white">KES {{ number_format($season->price, 2) }}</div>
                                </div>
                            </div>

                            <div class="camp-progress my-2" style="height: 8px;">
                                <div class="camp-progress-bar bg-danger" style="width: {{ $registration->payment_percent }}%;"></div>
                            </div>

                            <div class="d-flex justify-content-between small text-white-50">
                                <span>{{ $registration->payment_percent }}% Completed</span>
                                <span>Remaining: <strong class="{{ $registration->balance_remaining > 0 ? 'text-danger' : 'text-success' }}">KES {{ number_format($registration->balance_remaining, 2) }}</strong></span>
                            </div>
                        </div>

                        @if($registration->payment_percent >= 100)
                            <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.3);">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-patch-check-fill text-success fs-5"></i>
                                    <span class="small fw-semibold text-white">Camp fee is fully cleared! You are all set.</span>
                                </div>
                                @if($registration->payments->isNotEmpty())
                                    <a href="{{ route('receipts.show', $registration->payments->first()->receipt_number) }}" class="btn btn-outline-success btn-sm py-1 px-2" style="font-size: 11px;">
                                        Receipt &rarr;
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="d-flex align-items-center justify-content-between p-2 rounded" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-info-circle text-danger"></i>
                                    <span class="small text-white-50">Remaining balance is covered by your parent or sponsor.</span>
                                </div>
                                @if($registration->payments->isNotEmpty())
                                    <a href="{{ route('receipts.show', $registration->payments->first()->receipt_number) }}" class="btn btn-outline-danger btn-sm py-1 px-2" style="font-size: 11px;">
                                        Receipt &rarr;
                                    </a>
                                @endif
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning mb-0 small">
                            You are not yet registered for the current camp season.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Card 2: Camp Logistics & Details --}}
        <div class="col-lg-6">
            <div class="teen-status-card">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-secondary border-opacity-25 pb-2">
                        <h5 class="fw-bold text-white mb-0">
                            <i class="bi bi-compass-fill text-danger me-2"></i> Camp Logistics &amp; Info
                        </h5>
                        <span class="badge bg-danger text-uppercase px-2 py-1" style="font-size: 11px;">Essential</span>
                    </div>

                    @if($registration && $season)
                        <div class="d-flex flex-column gap-2 mb-2">
                            <div class="p-2 rounded d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                <span class="text-white-50 small"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Camp Venue:</span>
                                <span class="fw-semibold text-white small text-end">{{ $season->venue }}</span>
                            </div>
                            <div class="p-2 rounded d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                <span class="text-white-50 small"><i class="bi bi-calendar-event text-danger me-1"></i> Camp Dates:</span>
                                <span class="fw-semibold text-white small">{{ $season->start_date->format('M d') }} &ndash; {{ $season->end_date->format('M d, Y') }}</span>
                            </div>
                            <div class="p-2 rounded d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                <span class="text-white-50 small"><i class="bi bi-phone text-danger me-1"></i> Phone Policy:</span>
                                <span class="badge bg-{{ $registration->phone_carried ? 'warning text-dark' : 'secondary' }}">
                                    {{ $registration->phone_carried ? 'Phone Carried' : 'No Phone' }}
                                </span>
                            </div>
                            <div class="p-2 rounded d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06);">
                                <span class="text-white-50 small"><i class="bi bi-person-heart text-danger me-1"></i> Parent / Guardian:</span>
                                <span class="fw-semibold text-white small">{{ $user->parents->pluck('name')->join(', ') ?: 'Linked at Desk' }}</span>
                            </div>
                        </div>
                    @else
                        <p class="text-white-50 small mb-0">No active registration records found for this season.</p>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════════════════════
         MIDDLE SECTION: CAMP FORMS & SURVEYS
    ══════════════════════════════════════════════════════════ --}}
    <div class="camp-card p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25 pb-3 mb-3">
            <div>
                <h5 class="fw-bold mb-1 text-white">
                    <i class="bi bi-card-checklist text-danger me-2"></i> Camp Forms &amp; Surveys
                </h5>
                <p class="text-muted small mb-0">Complete required forms before camp. Forms needing parent sign-off route directly to your parent.</p>
            </div>
            <span class="badge bg-danger">{{ $forms->count() }} Forms</span>
        </div>

        @if($forms->count() > 0)
            <div class="row g-3">
                @foreach($forms as $form)
                    @php
                        $sub = $submissions->get($form->id);
                    @endphp
                    <div class="col-md-6">
                        <div class="teen-form-card">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-white mb-0">{{ $form->title }}</h6>
                                    @if($sub)
                                        @if($sub->status === 'submitted')
                                            <span class="badge bg-success"><i class="bi bi-check-all me-1"></i> Submitted</span>
                                        @elseif($sub->status === 'pending_parent_review')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass me-1"></i> Pending Parent Sign-Off</span>
                                        @elseif($sub->status === 'returned')
                                            <span class="badge bg-danger"><i class="bi bi-arrow-repeat me-1"></i> Returned for Correction</span>
                                        @endif
                                    @else
                                        <span class="badge bg-secondary">Not Started</span>
                                    @endif
                                </div>

                                <p class="text-white-50 small mb-2">{{ $form->description ?: 'Please fill out all required questions.' }}</p>

                                @if($form->requires_parent_approval)
                                    <div class="text-white-50 small mb-2">
                                        <i class="bi bi-shield-check text-danger me-1"></i> Requires Parent Approval
                                    </div>
                                @endif

                                @if($sub && $sub->status === 'returned')
                                    @if($sub->admin_feedback)
                                        <div class="alert alert-danger py-2 px-3 small my-2 border-0" style="background: rgba(239, 68, 68, 0.15); color: #FCA5A5;">
                                            <strong class="d-block text-white mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Camp Admin Feedback:</strong>
                                            {{ $sub->admin_feedback }}
                                        </div>
                                    @endif
                                    @if($sub->parent_feedback)
                                        <div class="alert alert-warning py-2 px-3 small my-2 border-0" style="background: rgba(234, 179, 8, 0.15); color: #FDE047;">
                                            <strong class="d-block text-white mb-1"><i class="bi bi-chat-quote-fill me-1"></i> Parent Feedback:</strong>
                                            {{ $sub->parent_feedback }}
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="mt-3 pt-2 border-top border-secondary border-opacity-25 d-flex gap-2">
                                @if(!$sub)
                                    <a href="{{ route('teen.forms.show', $form) }}" class="btn btn-camp-red btn-sm w-100 fw-bold">
                                        <i class="bi bi-pencil-square me-1"></i> Fill Form Now
                                    </a>
                                @elseif($sub->status === 'returned')
                                    <a href="{{ route('teen.forms.show', $form) }}" class="btn btn-danger btn-sm w-100 fw-bold">
                                        <i class="bi bi-arrow-repeat me-1"></i> Revise &amp; Resubmit
                                    </a>
                                @else
                                    <a href="{{ route('teen.forms.submission.show', $sub) }}" class="btn btn-outline-danger btn-sm w-100 fw-bold">
                                        <i class="bi bi-eye-fill me-1"></i> View My Responses
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-muted my-3 text-center">No camp forms assigned yet.</p>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════
         BOTTOM SECTION: CAMP PACKING CHECKLIST
    ══════════════════════════════════════════════════════════ --}}
    <div class="camp-card p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25 pb-3 mb-3">
            <div>
                <h5 class="fw-bold mb-1 text-white">
                    <i class="bi bi-backpack text-danger me-2"></i> Camp Packing Checklist
                </h5>
                <p class="text-muted small mb-0">What to pack for Teen Camp {{ $season ? $season->year : '2026' }}</p>
            </div>
            @if($packingList->count() > 0)
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('packing-list.pdf') }}" target="_blank" class="btn btn-outline-danger btn-sm shadow-sm d-flex align-items-center gap-1">
                        <i class="bi bi-printer-fill"></i>
                        <span>Download & Print PDF</span>
                    </a>
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Released</span>
                </div>
            @else
                <span class="badge bg-secondary"><i class="bi bi-lock-fill me-1"></i> Release Pending</span>
            @endif
        </div>

        @if($packingList->count() > 0)
            <div class="row g-3">
                @foreach($packingList as $category => $items)
                    <div class="col-md-6 col-lg-4">
                        <div class="packing-category-card">
                            <h6 class="fw-bold text-uppercase border-bottom border-secondary border-opacity-25 pb-2 text-danger">
                                <i class="bi bi-check2-square me-1"></i> {{ $category }}
                            </h6>
                            <ul class="list-unstyled mb-0 small">
                                @foreach($items as $item)
                                    <li class="py-1 d-flex align-items-start gap-2">
                                        <i class="bi bi-circle text-danger mt-1" style="font-size: 7px;"></i>
                                        <div>
                                            <span class="fw-semibold text-white">{{ $item->item_name }}</span>
                                            @if($item->is_essential)
                                                <span class="badge bg-danger ms-1" style="font-size: 9px;">Essential</span>
                                            @endif
                                            @if($item->notes)
                                                <div class="text-muted" style="font-size: 11px;">{{ $item->notes }}</div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-4 text-center text-muted border rounded" style="background: rgba(255,255,255,0.02); border-color: rgba(255,255,255,0.08) !important;">
                <i class="bi bi-hourglass-split text-danger fs-2 mb-2 d-block"></i>
                <h6 class="fw-bold text-white">Packing List Not Yet Released</h6>
                <p class="small text-muted mb-0">The leadership team will release the official camp packing list closer to the camp dates. Check back soon!</p>
            </div>
        @endif
    </div>

</div>
@endsection
