<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Portal') - Teen Camp Back-Office</title>

    <!-- Local Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Bootstrap 5.3 CSS (CDN + Local fallback) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <!-- Bootstrap Icons (CDN + Local fallback) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <!-- Local Camp Red/Black/White Theme -->
    <link rel="stylesheet" href="{{ asset('css/camp-theme.css') }}">

    @stack('styles')
</head>
<body>
    <!-- Unified Executive Backoffice Navbar -->
    <header class="camp-topbar py-2 sticky-top shadow-sm" style="overflow: visible !important; z-index: 1030;">
        <div class="container-fluid px-2 px-md-3 d-flex align-items-center justify-content-between gap-2 flex-nowrap">
            <!-- Left: Brand & Mobile Sidebar Toggle -->
            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                @auth('staff')
                    <button class="btn btn-outline-secondary btn-sm border-secondary text-white p-0 d-md-none rounded-2" 
                            type="button" 
                            data-bs-toggle="offcanvas" 
                            data-bs-target="#backofficeMobileDrawer" 
                            aria-controls="backofficeMobileDrawer" 
                            title="Toggle Navigation Menu"
                            style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                @endauth
                <a class="d-flex align-items-center gap-2 text-decoration-none text-white fw-bold fs-5" href="{{ route('backoffice.admin.dashboard') }}">
                    <img src="{{ asset('images/church-logo.jpg') }}" alt="Church Logo" class="rounded bg-white p-1" style="height: 32px; width: auto; object-fit: contain; box-shadow: 0 0 10px rgba(220,53,69,0.35);">
                    <span class="tracking-wide">CAMP</span>
                </a>
                <span class="badge bg-dark border border-secondary text-white text-uppercase d-none d-sm-inline" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    Staff Portal
                </span>
                @if($currentSeason)
                    <span class="badge bg-danger d-none d-lg-inline">{{ $currentSeason->name }}</span>
                @endif
            </div>

            <!-- Right Controls: Season Switcher, Live Countdown, Notifications, Theme, Profile -->
            <div class="d-flex align-items-center gap-1 gap-sm-2 flex-nowrap ms-auto">
                @if(isset($allSeasons) && $allSeasons->count() > 0)
                    <div class="dropdown">
                        <button class="btn btn-sm btn-dark dropdown-toggle text-white border border-secondary py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 12px;">
                            <i class="bi bi-calendar3 me-1 text-danger"></i> <strong>{{ $currentSeason ? $currentSeason->year : 'Season' }}</strong>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border border-secondary" style="z-index: 1060;">
                            <li><h6 class="dropdown-header">Switch Season View</h6></li>
                            @foreach($allSeasons as $s)
                                <li>
                                    <a class="dropdown-item d-flex justify-content-between align-items-center {{ $currentSeason && $currentSeason->id === $s->id ? 'active' : '' }}" 
                                       href="{{ route('backoffice.switch-season', $s) }}">
                                        <span>{{ $s->name }}</span>
                                        <span class="badge bg-{{ $s->isActive() ? 'danger' : 'secondary' }} ms-2">{{ ucfirst($s->status) }}</span>
                                    </a>
                                </li>
                            @endforeach
                            @if(Auth::guard('staff')->user() && Auth::guard('staff')->user()->isAdmin())
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="{{ route('backoffice.admin.seasons') }}">
                                        <i class="bi bi-gear-fill me-1"></i> Manage Seasons
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if($currentSeason)
                    <div class="countdown-box d-none d-lg-inline-flex py-1 px-2" id="camp-countdown" title="Days until {{ $currentSeason->name }}">
                        <div class="countdown-unit">
                            <span class="countdown-val camp-cd-days" id="camp-cd-days" style="font-size: 0.95rem;">00</span>
                            <span class="countdown-label">D</span>
                        </div>
                        <span class="countdown-sep">:</span>
                        <div class="countdown-unit">
                            <span class="countdown-val camp-cd-hours" id="camp-cd-hours" style="font-size: 0.95rem;">00</span>
                            <span class="countdown-label">H</span>
                        </div>
                        <span class="countdown-sep">:</span>
                        <div class="countdown-unit">
                            <span class="countdown-val camp-cd-mins" id="camp-cd-mins" style="font-size: 0.95rem;">00</span>
                            <span class="countdown-label">M</span>
                        </div>
                        <span class="countdown-sep">:</span>
                        <div class="countdown-unit">
                            <span class="countdown-val camp-cd-secs" id="camp-cd-secs" style="font-size: 0.95rem;">00</span>
                            <span class="countdown-label">S</span>
                        </div>
                    </div>
                @endif

                @auth('staff')
                    @php 
                        $staff = Auth::guard('staff')->user(); 
                        $unreadNotifsCount = $unreadNotifsCount ?? 0;
                        $topNavNotifs = $topNavNotifs ?? collect();
                    @endphp

                    <!-- Notification Bar & Dropdown -->
                    <div class="dropdown" id="camp-notifications-dropdown">
                        <button class="btn btn-outline-secondary btn-sm rounded-circle position-relative border-secondary text-white p-0 dropdown-toggle no-caret" 
                                type="button" id="campNotifDropdownBtn" data-bs-toggle="dropdown" aria-expanded="false" 
                                title="System Activity & Notifications" style="width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="bi bi-bell-fill"></i>
                            @if($unreadNotifsCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-dark" id="nav-notif-count" style="font-size: 10px;">
                                    {{ $unreadNotifsCount > 9 ? '9+' : $unreadNotifsCount }}
                                </span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg border border-danger-subtle bg-dark" style="width: 360px; max-width: 92vw; z-index: 1060;">
                            <div class="p-3 border-bottom border-secondary d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-bell-fill text-danger"></i>
                                    <span class="fw-bold text-white small text-uppercase tracking-wider">Live System Activity</span>
                                    <span class="badge bg-danger-subtle text-danger" style="font-size: 10px;">Live DB</span>
                                </div>
                                @if($unreadNotifsCount > 0)
                                    <form action="{{ route('backoffice.notifications.read-all') }}" method="POST" class="m-0 form-mark-all-read">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm text-white-50 p-0 text-decoration-none" style="font-size: 11px;">
                                            Mark all read
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <div class="list-group list-group-flush overflow-auto" id="backofficeNotifsContainer" style="max-height: 330px;">
                                @forelse($topNavNotifs as $n)
                                    <div class="list-group-item list-group-item-action notif-clickable-item bg-dark text-white border-secondary p-2 px-3 {{ !$n->is_read ? 'border-start border-3 border-danger' : 'opacity-75' }}"
                                         data-notif-id="{{ $n->id }}"
                                         data-is-read="{{ $n->is_read ? '1' : '0' }}"
                                         data-read-url="{{ route('backoffice.notifications.read', $n->id) }}"
                                         data-target-link="{{ $n->link ?: '' }}"
                                         style="cursor: pointer;">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="bi {{ $n->icon_class }} fs-5 mt-1 text-danger"></i>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <div class="d-flex justify-content-between align-items-baseline">
                                                    <strong class="small text-white text-truncate notif-title {{ !$n->is_read ? 'text-danger' : '' }}">{{ $n->title }}</strong>
                                                    <small class="text-white-50 ms-1 flex-shrink-0" style="font-size: 10px;">{{ $n->created_at ? $n->created_at->diffForHumans(null, true) : 'Just now' }}</small>
                                                </div>
                                                <p class="mb-1 text-white-50 small" style="font-size: 11px; line-height: 1.3;">{{ $n->message }}</p>
                                                @if($n->link)
                                                    <a href="{{ $n->link }}" class="badge bg-danger-subtle text-danger border border-danger text-decoration-none py-1 px-2" style="font-size: 10px;">
                                                        View Record &rarr;
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-white-50 small" id="noNotifsMsg">
                                        <i class="bi bi-check2-circle fs-3 text-success d-block mb-1"></i>
                                        No recent notifications. System running smoothly!
                                    </div>
                                @endforelse
                            </div>
                            <div class="p-2 text-center border-top border-secondary">
                                <a href="{{ route('backoffice.notifications.index') }}" class="btn btn-sm btn-outline-danger w-100" style="font-size: 11px;">
                                    View Complete Notification Log &rarr;
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Theme Toggle Button (Tablet/Desktop) -->
                    <button class="btn btn-outline-secondary btn-sm theme-toggle-btn rounded-circle p-1 d-none d-sm-inline-flex" onclick="window.toggleCampTheme()" title="Toggle Dark/Light Mode" style="width: 34px; height: 34px; align-items: center; justify-content: center;">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>

                    <!-- Staff Profile Dropdown -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2 text-white text-decoration-none" href="#" id="staffProfileDropdownBtn" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                            @if($staff->avatar)
                                <img src="{{ asset('storage/' . $staff->avatar) }}" class="rounded-circle border border-danger" width="32" height="32" alt="Avatar">
                            @else
                                <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                                    {{ strtoupper(substr($staff->name, 0, 1)) }}
                                </span>
                            @endif
                            <div class="text-start d-none d-xl-block">
                                <div class="fw-bold lh-1 text-white small">{{ $staff->name }}</div>
                                <small class="badge bg-secondary text-uppercase" style="font-size: 9px;">{{ str_replace('_', ' ', $staff->role) }}</small>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border border-secondary" style="z-index: 1060;">
                            <li class="px-3 py-2 border-bottom border-secondary d-xl-none">
                                <div class="fw-bold text-white small">{{ $staff->name }}</div>
                                <small class="text-white-50 text-uppercase" style="font-size: 10px;">{{ str_replace('_', ' ', $staff->role) }}</small>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('backoffice.profile') }}">
                                    <i class="bi bi-person-gear me-2 text-danger"></i> Profile &amp; PIN
                                </a>
                            </li>
                            <li class="d-sm-none">
                                <a class="dropdown-item" href="#" onclick="window.toggleCampTheme(); return false;">
                                    <i class="bi bi-moon-stars-fill me-2 text-warning"></i> Toggle Theme
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="{{ route('backoffice.logout') }}">
                                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    @if($currentSeason)
        <!-- Mobile Live Countdown Bar (< 992px) -->
        <div class="camp-mobile-countdown-strip d-lg-none bg-black border-bottom border-danger border-opacity-25 px-3 py-1 d-flex align-items-center justify-content-between" style="box-shadow: 0 2px 10px rgba(0,0,0,0.5);">
            <div class="d-flex align-items-center gap-1 small">
                <i class="bi bi-clock-history text-danger"></i>
                <span class="text-white-50 text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">{{ $currentSeason->name }}:</span>
            </div>
            <div class="countdown-box py-0 px-2" style="background: rgba(220, 38, 38, 0.12); border: 1px solid rgba(220, 38, 38, 0.35); border-radius: 8px;">
                <div class="countdown-unit">
                    <span class="countdown-val camp-cd-days" style="font-size: 0.85rem; color: #FFF; font-weight: 700;">00</span>
                    <span class="countdown-label text-danger" style="font-size: 8px; font-weight: 800;">D</span>
                </div>
                <span class="countdown-sep text-danger" style="font-size: 0.85rem; font-weight: 700;">:</span>
                <div class="countdown-unit">
                    <span class="countdown-val camp-cd-hours" style="font-size: 0.85rem; color: #FFF; font-weight: 700;">00</span>
                    <span class="countdown-label text-danger" style="font-size: 8px; font-weight: 800;">H</span>
                </div>
                <span class="countdown-sep text-danger" style="font-size: 0.85rem; font-weight: 700;">:</span>
                <div class="countdown-unit">
                    <span class="countdown-val camp-cd-mins" style="font-size: 0.85rem; color: #FFF; font-weight: 700;">00</span>
                    <span class="countdown-label text-danger" style="font-size: 8px; font-weight: 800;">M</span>
                </div>
                <span class="countdown-sep text-danger" style="font-size: 0.85rem; font-weight: 700;">:</span>
                <div class="countdown-unit">
                    <span class="countdown-val camp-cd-secs" style="font-size: 0.85rem; color: #FFF; font-weight: 700;">00</span>
                    <span class="countdown-label text-danger" style="font-size: 8px; font-weight: 800;">S</span>
                </div>
            </div>
        </div>
    @endif

    @auth('staff')
        @php 
            $staff = Auth::guard('staff')->user(); 
            $sidebarSeasonId = session('admin_selected_season_id') ?? \App\Models\CampSeason::getActive()?->id;
            $sidebarPendingAid = $sidebarSeasonId ? \App\Models\AdoptATeenRequest::where('camp_season_id', $sidebarSeasonId)->where('status', 'pending')->count() : 0;
        @endphp

        <!-- ══════════════════════════════════════════════════════════
             MOBILE OFFCANVAS SIDEBAR DRAWER (< 768px)
        ══════════════════════════════════════════════════════════ -->
        <div class="offcanvas offcanvas-start bg-dark text-white border-end border-secondary d-md-none" 
             tabindex="-1" 
             id="backofficeMobileDrawer" 
             aria-labelledby="backofficeMobileDrawerLabel"
             style="width: 300px; max-width: 85vw; background: #121216 !important; z-index: 1055;">
            
            <div class="offcanvas-header border-bottom border-secondary border-opacity-30 py-3 px-3">
                <div class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/church-logo.jpg') }}" alt="Logo" class="rounded bg-white p-1" style="height: 32px; width: auto; object-fit: contain;">
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="backofficeMobileDrawerLabel">TEEN CAMP</h6>
                        <small class="badge bg-danger text-uppercase" style="font-size: 9px;">Staff Control Panel</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body p-3 overflow-y-auto">
                <!-- User Profile Card in Drawer -->
                <div class="p-3 mb-3 rounded d-flex align-items-center justify-content-between" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; font-size: 14px;">
                            {{ strtoupper(substr($staff->name, 0, 1)) }}
                        </span>
                        <div>
                            <div class="fw-bold text-white small text-truncate" style="max-width: 130px;">{{ $staff->name }}</div>
                            <span class="badge bg-secondary text-uppercase" style="font-size: 9px;">{{ str_replace('_', ' ', $staff->role) }}</span>
                        </div>
                    </div>
                    <button class="btn btn-outline-secondary btn-sm rounded-circle p-1" onclick="window.toggleCampTheme()" title="Toggle Theme" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                        <i class="bi bi-moon-stars-fill"></i>
                    </button>
                </div>

                @if($currentSeason)
                    <!-- Mobile Drawer Countdown Widget -->
                    <div class="p-2 mb-3 rounded d-flex align-items-center justify-content-between" style="background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.25);">
                        <div class="small">
                            <div class="text-white-50 text-uppercase fw-bold" style="font-size: 9px; letter-spacing: 0.5px;">Live Countdown</div>
                            <div class="text-danger fw-bold small">{{ $currentSeason->name }}</div>
                        </div>
                        <div class="countdown-box py-0 px-2" style="background: rgba(0, 0, 0, 0.5); border: 1px solid rgba(220, 38, 38, 0.4); border-radius: 8px;">
                            <div class="countdown-unit">
                                <span class="countdown-val camp-cd-days" style="font-size: 0.85rem; color: #FFF; font-weight: 700;">00</span>
                                <span class="countdown-label text-danger" style="font-size: 7px; font-weight: 800;">D</span>
                            </div>
                            <span class="countdown-sep text-danger" style="font-size: 0.85rem; font-weight: 700;">:</span>
                            <div class="countdown-unit">
                                <span class="countdown-val camp-cd-hours" style="font-size: 0.85rem; color: #FFF; font-weight: 700;">00</span>
                                <span class="countdown-label text-danger" style="font-size: 7px; font-weight: 800;">H</span>
                            </div>
                            <span class="countdown-sep text-danger" style="font-size: 0.85rem; font-weight: 700;">:</span>
                            <div class="countdown-unit">
                                <span class="countdown-val camp-cd-mins" style="font-size: 0.85rem; color: #FFF; font-weight: 700;">00</span>
                                <span class="countdown-label text-danger" style="font-size: 7px; font-weight: 800;">M</span>
                            </div>
                            <span class="countdown-sep text-danger" style="font-size: 0.85rem; font-weight: 700;">:</span>
                            <div class="countdown-unit">
                                <span class="countdown-val camp-cd-secs" style="font-size: 0.85rem; color: #FFF; font-weight: 700;">00</span>
                                <span class="countdown-label text-danger" style="font-size: 7px; font-weight: 800;">S</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Navigation Tree -->
                <div class="nav flex-column gap-1">
                    @if($staff->isAdmin())
                        <div class="small text-uppercase text-muted fw-bold px-2 mt-1 mb-1" style="font-size: 11px; letter-spacing: .5px;">Administration</div>
                        <a href="{{ route('backoffice.admin.dashboard') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill text-danger"></i> Admin Overview
                        </a>
                        <a href="{{ route('backoffice.admin.database') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.admin.database') ? 'active' : '' }}">
                            <i class="bi bi-search text-info"></i> Database Search
                        </a>
                        <a href="{{ route('backoffice.admin.seasons') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.admin.seasons') ? 'active' : '' }}">
                            <i class="bi bi-calendar-event text-warning"></i> Camp Seasons
                        </a>
                        <a href="{{ route('backoffice.admin.users') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.admin.users') ? 'active' : '' }}">
                            <i class="bi bi-people-fill text-success"></i> Users &amp; PINs
                        </a>
                    @endif

                    @if($staff->isPastor() || $staff->isAdmin())
                        <div class="small text-uppercase text-muted fw-bold px-2 mt-3 mb-1" style="font-size: 11px; letter-spacing: .5px;">Pastoral &amp; Aid</div>
                        <a href="{{ route('backoffice.pastor.dashboard') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.pastor.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-shaded text-primary"></i> Pastor Portal
                        </a>
                        <a href="{{ route('backoffice.adopt.index') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.adopt.*') ? 'active' : '' }}">
                            <i class="bi bi-heart-pulse-fill text-danger"></i> Adopt-a-Teen Aid
                            @if($sidebarPendingAid > 0)
                                <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 10px;">{{ $sidebarPendingAid }}</span>
                            @endif
                        </a>
                    @endif

                    @if($staff->isRegistration() || $staff->isAdmin())
                        <div class="small text-uppercase text-muted fw-bold px-2 mt-3 mb-1" style="font-size: 11px; letter-spacing: .5px;">Registration Desk</div>
                        <a href="{{ route('backoffice.registration.dashboard') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.registration.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer text-info"></i> Reg Dashboard
                        </a>
                        <a href="{{ route('backoffice.registration.desk') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.registration.desk') ? 'active' : '' }}">
                            <i class="bi bi-person-plus-fill text-danger"></i> Desk Intake (2-Min)
                        </a>
                        <a href="{{ route('backoffice.registration.signin') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.registration.signin') ? 'active' : '' }}">
                            <i class="bi bi-check2-circle text-success"></i> Camp-Day Sign-In
                        </a>
                        <a href="{{ route('backoffice.forms.index') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.forms.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text text-warning"></i> Form Builder &amp; Hub
                        </a>
                        <a href="{{ route('backoffice.packing.index') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.packing.*') ? 'active' : '' }}">
                            <i class="bi bi-backpack text-danger"></i> Packing Lists
                        </a>
                    @endif

                    @if($staff->isCampaign() || $staff->isCampaignHead() || $staff->isAdmin())
                        <div class="small text-uppercase text-muted fw-bold px-2 mt-3 mb-1" style="font-size: 11px; letter-spacing: .5px;">Fundraising</div>
                        <a href="{{ route('backoffice.campaign.dashboard') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.campaign.*') ? 'active' : '' }}">
                            <i class="bi bi-cart4 text-success"></i> Campaign Sales &amp; POS
                        </a>
                    @endif

                    <div class="small text-uppercase text-muted fw-bold px-2 mt-3 mb-1" style="font-size: 11px; letter-spacing: .5px;">Account &amp; Security</div>
                    <a href="{{ route('backoffice.profile') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.profile') ? 'active' : '' }}">
                        <i class="bi bi-person-gear text-danger"></i> Profile &amp; PIN
                    </a>
                    <a href="{{ route('backoffice.logout') }}" class="backoffice-nav-link rounded text-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    @endauth

    <!-- Main Container with Sidebar -->
    <div class="d-flex flex-grow-1" style="min-width: 0; max-width: 100vw; overflow-x: clip;">
        <!-- Desktop Sidebar Navigation (>= 768px) -->
        @auth('staff')
            <aside class="backoffice-sidebar d-none d-md-block p-3 flex-shrink-0" style="width: 240px;">
                <div class="small text-uppercase text-muted fw-bold px-3 mb-2">Navigation</div>
                <div class="nav flex-column gap-1">
                    @if($staff->isAdmin())
                        <a href="{{ route('backoffice.admin.dashboard') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill"></i> Admin Overview
                        </a>
                        <a href="{{ route('backoffice.admin.database') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.admin.database') ? 'active' : '' }}">
                            <i class="bi bi-search"></i> Database Search
                        </a>
                        <a href="{{ route('backoffice.admin.seasons') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.admin.seasons') ? 'active' : '' }}">
                            <i class="bi bi-calendar-event"></i> Camp Seasons
                        </a>
                        <a href="{{ route('backoffice.admin.users') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.admin.users') ? 'active' : '' }}">
                            <i class="bi bi-people-fill"></i> Users &amp; PINs
                        </a>
                    @endif

                    @if($staff->isPastor() || $staff->isAdmin())
                        <div class="small text-uppercase text-muted fw-bold px-3 mt-3 mb-2">Pastoral &amp; Aid</div>
                        <a href="{{ route('backoffice.pastor.dashboard') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.pastor.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-shaded"></i> Pastor Portal
                        </a>
                        <a href="{{ route('backoffice.adopt.index') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.adopt.*') ? 'active' : '' }}">
                            <i class="bi bi-heart-pulse-fill text-danger"></i> Adopt-a-Teen
                            @if($sidebarPendingAid > 0)
                                <span class="badge bg-danger rounded-pill ms-auto" style="font-size: 10px;">{{ $sidebarPendingAid }}</span>
                            @endif
                        </a>
                    @endif

                    @if($staff->isRegistration() || $staff->isAdmin())
                        <div class="small text-uppercase text-muted fw-bold px-3 mt-3 mb-2">Registration</div>
                        <a href="{{ route('backoffice.registration.dashboard') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.registration.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer"></i> Reg Dashboard
                        </a>
                        <a href="{{ route('backoffice.registration.desk') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.registration.desk') ? 'active' : '' }}">
                            <i class="bi bi-person-plus-fill text-danger"></i> Desk Intake (2-Min)
                        </a>
                        <a href="{{ route('backoffice.registration.signin') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.registration.signin') ? 'active' : '' }}">
                            <i class="bi bi-check2-circle"></i> Camp-Day Sign-In
                        </a>
                        <a href="{{ route('backoffice.forms.index') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.forms.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text"></i> Form Builder
                        </a>
                        <a href="{{ route('backoffice.packing.index') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.packing.*') ? 'active' : '' }}">
                            <i class="bi bi-backpack"></i> Packing Lists
                        </a>
                    @endif

                    @if($staff->isCampaign() || $staff->isCampaignHead() || $staff->isAdmin())
                        <div class="small text-uppercase text-muted fw-bold px-3 mt-3 mb-2">Fundraising</div>
                        <a href="{{ route('backoffice.campaign.dashboard') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.campaign.*') ? 'active' : '' }}">
                            <i class="bi bi-cart4"></i> Campaign Sales &amp; POS
                        </a>
                    @endif

                    <div class="small text-uppercase text-muted fw-bold px-3 mt-3 mb-2">My Account</div>
                    <a href="{{ route('backoffice.profile') }}" class="backoffice-nav-link rounded {{ request()->routeIs('backoffice.profile') ? 'active' : '' }}">
                        <i class="bi bi-person-gear text-danger"></i> Profile &amp; PIN
                    </a>
                    <a href="{{ route('backoffice.logout') }}" class="backoffice-nav-link rounded text-danger">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </aside>
        @endauth

        <!-- Body Content -->
        <main class="flex-grow-1 p-3 p-lg-4" style="min-width: 0; max-width: 100%; overflow-x: hidden; background-color: var(--camp-bg);">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <div>{{ session('success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                    <div>{{ session('warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-dark alert-dismissible fade show border-0 shadow-sm d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-info-circle-fill text-danger fs-5"></i>
                    <div>{{ session('info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                        <strong>Please review the following:</strong>
                    </div>
                    <ul class="mb-0 ps-4">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         MOBILE BOTTOM NAVIGATION BAR FOR BACKOFFICE (< 768px)
    ══════════════════════════════════════════════════════════ -->
    @auth('staff')
        <nav class="camp-mobile-bottom-bar d-md-none" aria-label="Staff Mobile Navigation">
            <a href="{{ route('backoffice.admin.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Overview</span>
            </a>
            <a href="{{ route('backoffice.registration.desk') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.registration.desk') ? 'active' : '' }}">
                <i class="bi bi-person-plus-fill"></i>
                <span>Intake</span>
            </a>
            <a href="{{ route('backoffice.admin.database') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.admin.database') ? 'active' : '' }}">
                <i class="bi bi-search"></i>
                <span>Database</span>
            </a>
            <a href="{{ route('backoffice.forms.index') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.forms.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i>
                <span>Forms</span>
            </a>
            <button type="button" class="mobile-nav-item bg-transparent border-0" data-bs-toggle="offcanvas" data-bs-target="#backofficeMobileDrawer" aria-controls="backofficeMobileDrawer">
                <i class="bi bi-list fs-5"></i>
                <span>Menu</span>
            </button>
        </nav>
    @endauth

    <!-- Global Loading Overlay -->
    <div class="loading-overlay" id="camp-loading-overlay">
        <div class="d-flex flex-column align-items-center gap-3">
            <div class="camp-spinner"></div>
            <div class="text-white fw-bold tracking-wide">Processing...</div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS with Conditional Fallback -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        if (typeof bootstrap === 'undefined') {
            document.write('<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"><\/script>');
        }
    </script>
    <!-- Local Camp JS -->
    <script src="{{ asset('js/camp-theme.js') }}"></script>

    @if($currentSeason)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                window.initCampCountdown("{{ $currentSeason->start_date->toIso8601String() }}");
            });
        </script>
    @endif

    @stack('scripts')
</body>
</html>
