@extends('layouts.backoffice')

@section('title', '2-Minute Desk Intake')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">2-Minute Desk Registration &amp; Management</h3>
            <p class="text-muted small mb-0">Fast church desk intake flow for walk-up teens and parents. Season: <strong class="text-white">{{ $season ? $season->name : 'None' }}</strong></p>
        </div>
        <div class="d-grid d-sm-flex align-items-center gap-2 w-100 w-md-auto" style="grid-template-columns: repeat(2, 1fr);">
            @if($season)
                <form action="{{ route('backoffice.admin.seasons.toggle-registration', $season->id) }}" method="POST" class="m-0">
                    @csrf
                    @if($season->isRegistrationOpen())
                        <button type="submit" class="btn btn-outline-warning btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-2" title="Close Registration for this season">
                            <i class="bi bi-door-closed-fill"></i>
                            <span>Close Reg</span>
                        </button>
                    @else
                        <button type="submit" class="btn btn-success btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-2" title="Re-open Registration for this season">
                            <i class="bi bi-door-open-fill text-white"></i>
                            <span>Re-open</span>
                        </button>
                    @endif
                </form>
                <a href="{{ route('backoffice.reports.registrations.pdf', ['season_id' => $season->id]) }}" class="btn btn-outline-danger btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-2" target="_blank">
                    <i class="bi bi-file-earmark-pdf-fill"></i>
                    <span>Roster PDF</span>
                </a>
            @endif
            <a href="{{ route('backoffice.registration.signin') }}" class="btn btn-outline-light btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-2">
                <i class="bi bi-clipboard-check"></i>
                <span>Camp Sign-in</span>
            </a>
            <a href="{{ route('backoffice.registration.dashboard') }}" class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-2">
                <i class="bi bi-arrow-left"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill text-success"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(!$season)
        <div class="alert alert-warning">No active camp season found. Please create or activate a season first.</div>
    @else
        {{-- Navigation Tabs: Horizontal Swipeable on Mobile --}}
        <ul class="nav nav-pills camp-chip-scroll flex-nowrap gap-2 mb-4 border-bottom border-secondary border-opacity-25 pb-3" id="deskTabs" role="tablist">
            <li class="nav-item flex-shrink-0" role="presentation">
                <button class="nav-link {{ request()->filled('search') || request()->tab === 'settings' ? '' : 'active' }} px-3 px-md-4 py-2 fw-semibold text-nowrap"
                        id="new-intake-tab" data-bs-toggle="pill" data-bs-target="#new-intake" type="button" role="tab">
                    <i class="bi bi-stopwatch me-1"></i> Express Intake
                </button>
            </li>
            <li class="nav-item flex-shrink-0" role="presentation">
                <button class="nav-link {{ request()->filled('search') ? 'active' : '' }} px-3 px-md-4 py-2 fw-semibold text-nowrap"
                        id="recent-list-tab" data-bs-toggle="pill" data-bs-target="#recent-list" type="button" role="tab">
                    <i class="bi bi-pencil-square me-1"></i> Registered Campers &amp; Edit
                    <span class="badge bg-danger ms-1">{{ $recentRegistrations->count() }}</span>
                </button>
            </li>
            <li class="nav-item flex-shrink-0" role="presentation">
                <button class="nav-link {{ request()->tab === 'settings' ? 'active' : '' }} px-3 px-md-4 py-2 fw-semibold text-nowrap"
                        id="form-settings-tab" data-bs-toggle="pill" data-bs-target="#form-settings" type="button" role="tab">
                    <i class="bi bi-sliders me-1"></i> Form Fields
                </button>
            </li>
        </ul>

        <div class="tab-content" id="deskTabsContent">

            {{-- ===================== TAB 1: Dynamic Intake Form ===================== --}}
            <div class="tab-pane fade {{ request()->filled('search') || request()->tab === 'settings' ? '' : 'show active' }}" id="new-intake" role="tabpanel">
                <div class="row justify-content-center">
                    <div class="col-lg-11">
                        <div class="camp-card p-3 p-md-5 border-danger">
                            <div class="d-flex align-items-center gap-3 border-bottom pb-3 mb-4">
                                <span class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; font-size: 1.25rem;">
                                    <i class="bi bi-stopwatch"></i>
                                </span>
                                <div>
                                    <h4 class="fw-bold mb-1 fs-5 fs-md-4">Express Camper Intake</h4>
                                    <p class="text-muted small mb-0">
                                        Single intake form creates both Parent and Teen accounts with default PIN <strong>0000</strong> (forcing self-set on first login).
                                    </p>
                                </div>
                            </div>

                            <form action="{{ route('backoffice.registration.desk.process') }}" method="POST">
                                @csrf

                                @php
                                    $sections = [
                                        'parent'       => ['icon' => 'bi-person-heart',    'label' => '1. Parent / Guardian Information'],
                                        'teen'         => ['icon' => 'bi-person-fill',     'label' => '2. Teen Camper Information'],
                                        'declarations' => ['icon' => 'bi-shield-plus',     'label' => '3. Camp Declarations &amp; Medical Notes'],
                                        'payment'      => ['icon' => 'bi-phone-vibrate',   'label' => '4. Desk Payment (M-Pesa or Cash)'],
                                        'custom'       => ['icon' => 'bi-puzzle',          'label' => '5. Additional Info'],
                                    ];
                                    $enabledFields = $formConfig->enabledFields();
                                    $fieldsBySection = collect($enabledFields)->groupBy('section');
                                    $sectionIdx = 0;
                                @endphp

                                @foreach($sections as $sectionKey => $sectionMeta)
                                    @if(($fieldsBySection[$sectionKey] ?? collect())->isNotEmpty())
                                        @php $sectionIdx++ @endphp
                                        <div class="mb-4 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                            <h5 class="fw-bold text-danger mb-3">
                                                <i class="{{ $sectionMeta['icon'] }} me-2"></i>
                                                {!! preg_replace('/^\d+\./', $sectionIdx . '.', $sectionMeta['label']) !!}
                                            </h5>
                                            <div class="row g-3">
                                                @foreach($fieldsBySection[$sectionKey] ?? [] as $field)
                                                    @php
                                                        $isCore = in_array($field['key'], $coreKeys);
                                                        $colClass = $field['type'] === 'textarea' ? 'col-md-12' : 'col-md-4';
                                                    @endphp
                                                    <div class="{{ $colClass }}">
                                                        <label class="form-label fw-semibold small">
                                                            {{ $field['label'] }}
                                                            @if($field['required'] ?? false)<span class="text-danger">*</span>@endif
                                                        </label>

                                                        @if($field['type'] === 'checkbox')
                                                            <div class="form-check mt-1">
                                                                <input class="form-check-input" type="checkbox"
                                                                       name="{{ $field['key'] }}" value="1"
                                                                       id="field_{{ $field['key'] }}">
                                                                <label class="form-check-label small" for="field_{{ $field['key'] }}">
                                                                    Yes
                                                                </label>
                                                            </div>

                                                        @elseif($field['type'] === 'select' && !empty($field['options']))
                                                            <select class="form-select" name="{{ $field['key'] }}"
                                                                    {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                                                @if(!($field['required'] ?? false))
                                                                    <option value="">— Select —</option>
                                                                @endif
                                                                @foreach($field['options'] as $opt)
                                                                    <option value="{{ $opt }}">{{ ucfirst($opt) }}</option>
                                                                @endforeach
                                                            </select>

                                                        @elseif($field['type'] === 'textarea')
                                                            <textarea class="form-control" name="{{ $field['key'] }}"
                                                                      rows="2" placeholder="{{ $field['label'] }}"
                                                                      {{ ($field['required'] ?? false) ? 'required' : '' }}></textarea>

                                                        @else
                                                            <input type="{{ $field['type'] }}" class="form-control"
                                                                   name="{{ $field['key'] }}"
                                                                   {{ ($field['required'] ?? false) ? 'required' : '' }}
                                                                   @if($field['key'] === 'teen_gender') value="" @endif
                                                                   placeholder="{{ $field['label'] }}">
                                                        @endif

                                                        @if($field['key'] === 'parent_email')
                                                            <div class="form-text small">If parent exists, child will link automatically.</div>
                                                        @elseif($field['key'] === 'teen_email')
                                                            <div class="form-text small">Used for teen portal login.</div>
                                                        @elseif($field['key'] === 'initial_payment')
                                                            <div class="form-text small">Full Camp Price: <strong>KES {{ number_format($season->price, 2) }}</strong></div>
                                                        @elseif($field['key'] === 'payment_reference')
                                                            <div class="form-text small">10-char code from Safaricom SMS.</div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                                    <span class="small text-muted text-center text-sm-start"><i class="bi bi-clock me-1"></i> Average time to complete: 1 min 45 sec</span>
                                    <button type="submit" class="btn btn-camp-red btn-lg w-100 w-sm-auto px-5">
                                        <i class="bi bi-check-circle-fill me-2"></i> Complete Desk Registration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===================== TAB 2: Registered Campers & Edit ===================== --}}
            <div class="tab-pane fade {{ request()->filled('search') ? 'show active' : '' }}" id="recent-list" role="tabpanel">
                <div class="camp-card p-3 p-md-4 border-danger">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
                        <div>
                            <h4 class="fw-bold mb-1 fs-5 fs-md-4">Registered Campers &amp; Edit</h4>
                            <p class="text-muted small mb-0">Review intake contents and update camper details, medical declarations, or contact info.</p>
                        </div>
                        <form action="{{ route('backoffice.registration.desk') }}" method="GET" class="d-flex gap-2 w-100 w-md-auto">
                            <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm flex-grow-1" placeholder="Search camper or parent..." style="min-width: 0;">
                            <button type="submit" class="btn btn-camp-red btn-sm px-3 flex-shrink-0"><i class="bi bi-search"></i></button>
                            @if($search)
                                <a href="{{ route('backoffice.registration.desk') }}" class="btn btn-outline-secondary btn-sm flex-shrink-0">Clear</a>
                            @endif
                        </form>
                    </div>

                    {{-- Desktop / Tablet Table --}}
                    <div class="d-none d-md-block table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Camper Details</th>
                                    <th>Parent / Guardian</th>
                                    <th>Declarations</th>
                                    <th>Camp Fee Paid</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentRegistrations as $reg)
                                    @php $p = $reg->teen->parents->first(); @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                                      style="width:34px;height:34px;font-size:13px;">
                                                    {{ strtoupper(substr($reg->teen->name, 0, 1)) }}
                                                </span>
                                                <div>
                                                    <div class="fw-bold">{{ $reg->teen->name }}</div>
                                                    <div class="small text-muted">
                                                        <span class="badge bg-dark border border-secondary text-uppercase" style="font-size:10px;">{{ $reg->teen->gender }}</span>
                                                        {{ $reg->teen->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($p)
                                                <div class="fw-semibold">{{ $p->name }}</div>
                                                <div class="small text-muted">{{ $p->phone }} &bull; <span class="badge bg-secondary-subtle text-secondary" style="font-size:10px;">{{ $p->pivot->relationship ?? 'Guardian' }}</span></div>
                                            @else
                                                <span class="text-muted small">None linked</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($reg->phone_carried)
                                                    <span class="badge bg-warning text-dark" style="font-size:10px;"><i class="bi bi-phone"></i> Phone</span>
                                                @endif
                                                @if($reg->medical_conditions)
                                                    <span class="badge bg-danger" style="font-size:10px;" title="{{ $reg->medical_conditions }}"><i class="bi bi-heart-pulse"></i> Medical</span>
                                                @endif
                                                @if(!$reg->phone_carried && !$reg->medical_conditions)
                                                    <span class="badge bg-secondary-subtle text-secondary" style="font-size:10px;">None</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold {{ $reg->balance_remaining == 0 ? 'text-success' : 'text-danger' }}">
                                                KES {{ number_format($reg->total_paid, 2) }}
                                            </div>
                                            <div class="small text-muted" style="font-size:11px;">Balance: KES {{ number_format($reg->balance_remaining, 2) }}</div>
                                        </td>
                                        <td>
                                            @if($reg->status === 'signed_in')
                                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Signed In</span>
                                            @elseif($reg->status === 'registered')
                                                <span class="badge bg-primary"><i class="bi bi-clock me-1"></i> Registered</span>
                                            @else
                                                <span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i> Withdrawn</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('backoffice.registration.edit', $reg) }}" class="btn btn-camp-red btn-sm px-3">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                            No registrations found for this season.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile Card List (<768px) --}}
                    <div class="d-md-none d-flex flex-column gap-3">
                        @forelse($recentRegistrations as $reg)
                            @php $p = $reg->teen->parents->first(); @endphp
                            <div class="mobile-data-card">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                              style="width: 36px; height: 36px; font-size: 13px;">
                                            {{ strtoupper(substr($reg->teen->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="fw-bold text-white">{{ $reg->teen->name }}</div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <span class="badge bg-dark border border-secondary text-uppercase py-0 px-1">{{ $reg->teen->gender }}</span>
                                                {{ $reg->teen->email }}
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        @if($reg->status === 'signed_in')
                                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Signed In</span>
                                        @elseif($reg->status === 'registered')
                                            <span class="badge bg-primary"><i class="bi bi-clock me-1"></i> Registered</span>
                                        @else
                                            <span class="badge bg-secondary"><i class="bi bi-slash-circle me-1"></i> Withdrawn</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Parent info --}}
                                <div class="p-2 rounded mb-2" style="background-color: rgba(255, 255, 255, 0.03); font-size: 12px;">
                                    <div class="text-muted small">Parent / Guardian:</div>
                                    @if($p)
                                        <div class="fw-semibold text-white">{{ $p->name }} <span class="badge bg-secondary-subtle text-secondary py-0" style="font-size:10px;">{{ $p->pivot->relationship ?? 'Guardian' }}</span></div>
                                        @if($p->phone)
                                            <a href="tel:{{ $p->phone }}" class="text-info text-decoration-none d-inline-flex align-items-center gap-1 mt-1">
                                                <i class="bi bi-telephone-fill"></i> {{ $p->phone }}
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-muted">None linked</span>
                                    @endif
                                </div>

                                {{-- Badges & Fee --}}
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                                    <div class="d-flex flex-wrap gap-1">
                                        @if($reg->phone_carried)
                                            <span class="badge bg-warning text-dark" style="font-size: 10px;"><i class="bi bi-phone"></i> Phone</span>
                                        @endif
                                        @if($reg->medical_conditions)
                                            <span class="badge bg-danger" style="font-size: 10px;" title="{{ $reg->medical_conditions }}"><i class="bi bi-heart-pulse"></i> Medical</span>
                                        @endif
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold {{ $reg->balance_remaining == 0 ? 'text-success' : 'text-danger' }}" style="font-size: 13px;">
                                            Paid: KES {{ number_format($reg->total_paid, 2) }}
                                        </div>
                                        <div class="small text-muted" style="font-size: 10px;">Balance: KES {{ number_format($reg->balance_remaining, 2) }}</div>
                                    </div>
                                </div>

                                {{-- Action --}}
                                <a href="{{ route('backoffice.registration.edit', $reg) }}" class="btn btn-camp-red btn-sm w-100 d-flex align-items-center justify-content-center gap-1 py-2">
                                    <i class="bi bi-pencil-square"></i> Edit Registration
                                </a>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                No registrations found for this season.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- ===================== TAB 3: Form Field Settings ===================== --}}
            <div class="tab-pane fade {{ request()->tab === 'settings' ? 'show active' : '' }}" id="form-settings" role="tabpanel">
                <div class="row justify-content-center">
                    <div class="col-xl-10">
                        <div class="camp-card p-4">
                            <div class="d-flex align-items-center gap-3 border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                                <span class="p-3 rounded-circle bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-sliders fs-3"></i>
                                </span>
                                <div>
                                    <h4 class="fw-bold mb-1">Form Field Settings</h4>
                                    <p class="text-muted small mb-0">
                                        Customise what fields appear on the 2-minute intake form (both desk and public).
                                        Drag rows to reorder. Core fields (marked <span class="badge bg-danger">Core</span>) cannot be disabled.
                                    </p>
                                </div>
                            </div>

                            <form action="{{ route('backoffice.registration.desk.form-config.save') }}" method="POST" id="formConfigForm">
                                @csrf

                                <div id="fieldsList" class="overflow-x-auto pb-2" style="-webkit-overflow-scrolling: touch;">
                                    @foreach($formConfig->allFieldsSorted() as $idx => $field)
                                    @php
                                        $isCore = in_array($field['key'], $coreKeys);
                                        $sectionColors = [
                                            'parent'       => 'border-start border-3 border-info',
                                            'teen'         => 'border-start border-3 border-warning',
                                            'declarations' => 'border-start border-3 border-danger',
                                            'payment'      => 'border-start border-3 border-success',
                                            'custom'       => 'border-start border-3 border-secondary',
                                        ];
                                        $bc = $sectionColors[$field['section']] ?? 'border-start border-3 border-secondary';
                                    @endphp
                                    <div class="field-row d-flex align-items-center gap-2 p-3 mb-2 rounded {{ $bc }}"
                                         data-index="{{ $idx }}"
                                         style="background:rgba(255,255,255,0.03);cursor:grab;min-width:620px;">

                                        {{-- Hidden inputs --}}
                                        <input type="hidden" name="fields[{{ $idx }}][key]"     value="{{ $field['key'] }}">
                                        <input type="hidden" name="fields[{{ $idx }}][section]" value="{{ $field['section'] }}">
                                        <input type="hidden" name="fields[{{ $idx }}][order]"   class="order-input" value="{{ $field['order'] }}">

                                        {{-- Drag handle --}}
                                        <span class="drag-handle text-muted me-1" title="Drag to reorder" style="cursor:grab;font-size:18px;">⠿</span>

                                        {{-- Enabled toggle --}}
                                        <div class="form-check form-switch mb-0" style="min-width:54px;">
                                            <input class="form-check-input" type="checkbox"
                                                   name="fields[{{ $idx }}][enabled]" value="1"
                                                   {{ ($field['enabled'] ?? true) ? 'checked' : '' }}
                                                   {{ $isCore ? 'disabled checked' : '' }}>
                                        </div>

                                        {{-- Label input --}}
                                        <input type="text" class="form-control form-control-sm"
                                               name="fields[{{ $idx }}][label]"
                                               value="{{ $field['label'] }}"
                                               placeholder="Field Label"
                                               style="min-width:200px;">

                                        {{-- Field type --}}
                                        <select class="form-select form-select-sm" name="fields[{{ $idx }}][type]" style="max-width:120px;">
                                            @foreach(['text','email','tel','date','number','textarea','checkbox','select'] as $t)
                                                <option value="{{ $t }}" {{ ($field['type'] ?? 'text') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                                            @endforeach
                                        </select>

                                        {{-- Section badge --}}
                                        <span class="badge bg-secondary text-capitalize" style="white-space:nowrap;">{{ $field['section'] }}</span>

                                        {{-- Required toggle --}}
                                        <div class="form-check mb-0 d-flex align-items-center gap-1" style="white-space:nowrap;">
                                            <input class="form-check-input" type="checkbox"
                                                   name="fields[{{ $idx }}][required]" value="1"
                                                   {{ ($field['required'] ?? false) ? 'checked' : '' }}
                                                   {{ $isCore ? 'disabled checked' : '' }}
                                                   id="req_{{ $idx }}">
                                            <label class="form-check-label small" for="req_{{ $idx }}">Required</label>
                                        </div>

                                        @if($isCore)
                                            <span class="badge bg-danger ms-1">Core</span>
                                        @else
                                            {{-- Delete button for non-core fields --}}
                                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto delete-field-btn" title="Remove field">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>

                                {{-- Add Custom Field --}}
                                <div class="mt-4 p-3 rounded border border-secondary border-opacity-25" style="background:rgba(255,255,255,0.02);">
                                    <h6 class="fw-semibold mb-3"><i class="bi bi-plus-circle me-1 text-danger"></i> Add Custom Field</h6>
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold">Field Label</label>
                                            <input type="text" class="form-control form-control-sm" name="new_field_label" id="new_field_label" placeholder="e.g. T-Shirt Size">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-semibold">Type</label>
                                            <select class="form-select form-select-sm" name="new_field_type" id="new_field_type">
                                                @foreach(['text','email','tel','date','number','textarea','checkbox','select'] as $t)
                                                    <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label small fw-semibold">Section</label>
                                            <select class="form-select form-select-sm" name="new_field_section">
                                                <option value="custom">Custom</option>
                                                <option value="parent">Parent</option>
                                                <option value="teen">Teen</option>
                                                <option value="declarations">Declarations</option>
                                                <option value="payment">Payment</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2" id="newFieldOptions" style="display:none;">
                                            <label class="form-label small fw-semibold">Options <span class="text-muted">(comma separated)</span></label>
                                            <input type="text" class="form-control form-control-sm" name="new_field_options" placeholder="Small, Medium, Large">
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" name="new_field_required" value="1" id="new_field_required">
                                                <label class="form-check-label small" for="new_field_required">Required</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mt-4 pt-3 border-top border-secondary border-opacity-25">
                                    <p class="small text-muted mb-0 text-center text-sm-start">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Changes apply immediately to both the backoffice desk form and the public <code>/register-camper</code> page.
                                    </p>
                                    <button type="submit" class="btn btn-camp-red w-100 w-sm-auto px-4">
                                        <i class="bi bi-floppy me-2"></i> Save Form Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- end tab-content --}}
    @endif
</div>

@push('scripts')
<script>
// ── Drag & drop reorder ──────────────────────────────────────────────────
(function () {
    const list = document.getElementById('fieldsList');
    if (!list) return;

    let dragging = null;

    list.querySelectorAll('.field-row').forEach(row => {
        row.addEventListener('dragstart', e => {
            dragging = row;
            row.style.opacity = '0.4';
        });
        row.addEventListener('dragend', e => {
            row.style.opacity = '1';
            dragging = null;
            renumberFields();
        });
        row.addEventListener('dragover', e => {
            e.preventDefault();
            if (!dragging || dragging === row) return;
            const rect = row.getBoundingClientRect();
            const mid  = rect.top + rect.height / 2;
            if (e.clientY < mid) {
                list.insertBefore(dragging, row);
            } else {
                list.insertBefore(dragging, row.nextSibling);
            }
        });
        row.setAttribute('draggable', 'true');
    });

    function renumberFields () {
        list.querySelectorAll('.field-row').forEach((row, i) => {
            // Update name attributes to match new indices
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/\[\d+\]/, '[' + i + ']');
            });
            // Update order hidden field
            const orderInput = row.querySelector('.order-input');
            if (orderInput) orderInput.value = i + 1;
            row.dataset.index = i;
        });
    }

    // Delete field rows
    list.addEventListener('click', e => {
        if (e.target.closest('.delete-field-btn')) {
            const row = e.target.closest('.field-row');
            if (row && confirm('Remove this field?')) {
                row.remove();
                renumberFields();
            }
        }
    });

    // Show/hide options input for select type on new field
    const newType = document.getElementById('new_field_type');
    const optDiv  = document.getElementById('newFieldOptions');
    if (newType && optDiv) {
        newType.addEventListener('change', () => {
            optDiv.style.display = newType.value === 'select' ? '' : 'none';
        });
    }
})();
</script>
@endpush
@endsection
