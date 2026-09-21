<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Teen Camp 2026') - Church Teen Camp</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/church-logo.jpg') }}">

    <!-- Bootstrap 5.3 CSS (CDN + Local fallback) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <!-- Bootstrap Icons (CDN + Local fallback) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <!-- Google Fonts matching church logo serif typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Local Camp Red/Black/White Theme -->
    <link rel="stylesheet" href="{{ asset('css/camp-theme.css') }}">

    @stack('styles')
</head>
<body>
    <!-- Top Bar with Countdown -->
    <div class="camp-topbar">
        <div class="container d-flex align-items-center justify-content-between py-1 gap-2">
            <div class="d-flex align-items-center gap-2 overflow-hidden">
                <i class="bi bi-shield-shaded flex-shrink-0"></i>
                <span class="text-truncate fw-semibold" style="font-size: 13px;">{{ $currentSeason ? $currentSeason->name : 'Teen Camp Management' }}</span>
                @if($currentSeason && $currentSeason->theme)
                    <span class="d-none d-lg-inline opacity-75">| {{ $currentSeason->theme }}</span>
                @endif
            </div>
            
            @if($currentSeason)
                <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
                    <span class="d-none d-md-inline small text-uppercase fw-bold opacity-75">Starts in:</span>
                    <div class="countdown-box" id="camp-countdown">
                        <div class="countdown-unit">
                            <span class="countdown-val" id="camp-cd-days">00</span>
                            <span class="countdown-label">Days</span>
                        </div>
                        <span class="countdown-sep">:</span>
                        <div class="countdown-unit">
                            <span class="countdown-val" id="camp-cd-hours">00</span>
                            <span class="countdown-label">Hrs</span>
                        </div>
                        <span class="countdown-sep">:</span>
                        <div class="countdown-unit">
                            <span class="countdown-val" id="camp-cd-mins">00</span>
                            <span class="countdown-label">Min</span>
                        </div>
                        <span class="countdown-sep">:</span>
                        <div class="countdown-unit">
                            <span class="countdown-val" id="camp-cd-secs">00</span>
                            <span class="countdown-label">Sec</span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Main Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark camp-navbar py-2">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2 fw-bold" href="{{ url('/') }}">
                <img src="{{ asset('images/church-logo.jpg') }}" alt="Church 40th Anniversary" class="rounded bg-white p-1" style="height: 38px; width: auto; object-fit: contain; box-shadow: 0 0 10px rgba(220,53,69,0.35);">
                <span class="text-white">TEEN<span class="text-danger">CAMP</span></span>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#campNavbar">
                <i class="bi bi-list fs-3 text-white"></i>
            </button>

            <div class="collapse navbar-collapse" id="campNavbar">
                <ul class="navbar-nav ms-auto align-items-center gap-2">
                    @auth('web')
                        @php
                            $webUser = Auth::guard('web')->user();
                            $webUnreadCount = $webUnreadCount ?? 0;
                            $webNotifs = $webNotifs ?? collect();
                        @endphp

                        @if($webUser->isTeen())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('teen.dashboard') ? 'active text-danger fw-bold' : '' }}" href="{{ route('teen.dashboard') }}">
                                    <i class="bi bi-speedometer2 me-1"></i> Teen Dashboard
                                </a>
                            </li>
                        @elseif($webUser->isParent())
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active text-danger fw-bold' : '' }}" href="{{ route('parent.dashboard') }}">
                                    <i class="bi bi-people me-1"></i> Parent Dashboard
                                </a>
                            </li>
                        @endif

                        <!-- User Notification Center -->
                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative text-white" href="#" data-bs-toggle="dropdown" title="Notifications">
                                <i class="bi bi-bell-fill fs-5"></i>
                                @if($webUnreadCount > 0)
                                    <span class="position-absolute top-1 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px;">
                                        {{ $webUnreadCount > 9 ? '9+' : $webUnreadCount }}
                                    </span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg border border-danger-subtle bg-dark" style="width: 340px; max-width: 90vw;">
                                <div class="p-3 border-bottom border-secondary d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-bell-fill text-danger"></i>
                                        <span class="fw-bold text-white small text-uppercase">Account Alerts</span>
                                    </div>
                                    @if($webUnreadCount > 0)
                                        <form action="{{ route('notifications.read-all') }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm text-white-50 p-0 text-decoration-none" style="font-size: 11px;">
                                                Mark all read
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <div class="list-group list-group-flush overflow-auto" style="max-height: 320px;">
                                    @forelse($webNotifs as $n)
                                        <div class="list-group-item bg-dark text-white border-secondary p-2 px-3 {{ !$n->is_read ? 'border-start border-3 border-danger' : 'opacity-75' }}">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi {{ $n->icon_class }} fs-5 mt-1"></i>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex justify-content-between align-items-baseline">
                                                        <strong class="small text-white text-truncate">{{ $n->title }}</strong>
                                                        <small class="text-white-50 ms-1" style="font-size: 10px;">{{ $n->created_at ? $n->created_at->diffForHumans(null, true) : 'Just now' }}</small>
                                                    </div>
                                                    <p class="mb-1 text-white-50 small" style="font-size: 11px; line-height: 1.3;">{{ $n->message }}</p>
                                                    @if($n->link)
                                                        <a href="{{ $n->link }}" class="badge bg-danger-subtle text-danger border border-danger text-decoration-none py-1 px-2" style="font-size: 10px;">
                                                            View &rarr;
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-4 text-center text-white-50 small">
                                            <i class="bi bi-check2-circle fs-3 text-success d-block mb-1"></i>
                                            No notifications right now!
                                        </div>
                                    @endforelse
                                </div>
                                <div class="p-2 text-center border-top border-secondary">
                                    <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-danger w-100" style="font-size: 11px;">
                                        View All Alerts &rarr;
                                    </a>
                                </div>
                            </div>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                                @if(Auth::guard('web')->user()->avatar)
                                    <img src="{{ asset('storage/' . Auth::guard('web')->user()->avatar) }}" class="rounded-circle border border-danger" width="28" height="28" alt="Avatar">
                                @else
                                    <span class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; font-size: 12px;">
                                        {{ strtoupper(substr(Auth::guard('web')->user()->name, 0, 1)) }}
                                    </span>
                                @endif
                                <span>{{ Auth::guard('web')->user()->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item" href="{{ route('public.profile') }}">
                                        <i class="bi bi-person-gear me-2"></i> Profile & PIN
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('public.logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('landing') ? 'active' : '' }}" href="{{ route('landing') }}">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-danger btn-sm px-3 fw-bold {{ request()->routeIs('public.register') ? 'active bg-danger text-white' : '' }}" href="{{ route('public.register') }}">
                                <i class="bi bi-pencil-square me-1"></i> Register
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-camp-red btn-sm px-3 fw-bold" href="{{ route('login') }}">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
                            </a>
                        </li>
                    @endauth

                    <!-- Theme Toggle Button -->
                    <li class="nav-item ms-2">
                        <button class="btn btn-outline-secondary btn-sm theme-toggle-btn rounded-circle p-1" onclick="window.toggleCampTheme()" title="Toggle Dark/Light Mode" style="width: 32px; height: 32px;">
                            <i class="bi bi-moon-stars-fill"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alerts / Flash Messages -->
    <div class="container mt-3">
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
                    <strong>Please check the following issues:</strong>
                </div>
                <ul class="mb-0 ps-4">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow-1 py-4">
        @yield('content')
    </main>

    <!-- App-Like Mobile Bottom Navigation Bar (< 768px) -->
    <nav class="camp-mobile-bottom-bar d-md-none" aria-label="Mobile Navigation">
        @auth('web')
            @php
                $mUser = Auth::guard('web')->user();
                $mUnread = $webUnreadCount ?? 0;
            @endphp
            @if($mUser->isParent())
                <a href="{{ route('parent.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('parent.dashboard') && !request()->is('*forms*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('parent.dashboard') }}#forms-section" class="mobile-nav-item {{ request()->is('*forms*') ? 'active' : '' }}">
                    <i class="bi bi-card-checklist"></i>
                    <span>Forms</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="mobile-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell-fill"></i>
                    @if($mUnread > 0)
                        <span class="mobile-nav-badge">{{ $mUnread > 9 ? '9+' : $mUnread }}</span>
                    @endif
                    <span>Alerts</span>
                </a>
                <a href="{{ route('public.profile') }}" class="mobile-nav-item {{ request()->routeIs('public.profile') || request()->routeIs('profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i>
                    <span>Profile</span>
                </a>
            @elseif($mUser->isTeen())
                <a href="{{ route('teen.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('teen.dashboard') && !request()->is('*forms*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
                <a href="{{ route('teen.dashboard') }}#forms" class="mobile-nav-item {{ request()->is('*forms*') ? 'active' : '' }}">
                    <i class="bi bi-card-checklist"></i>
                    <span>Forms</span>
                </a>
                <a href="{{ route('packing-list.pdf') }}" target="_blank" class="mobile-nav-item">
                    <i class="bi bi-backpack-fill"></i>
                    <span>Packing</span>
                </a>
                <a href="{{ route('notifications.index') }}" class="mobile-nav-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <i class="bi bi-bell-fill"></i>
                    @if($mUnread > 0)
                        <span class="mobile-nav-badge">{{ $mUnread > 9 ? '9+' : $mUnread }}</span>
                    @endif
                    <span>Alerts</span>
                </a>
                <a href="{{ route('public.profile') }}" class="mobile-nav-item {{ request()->routeIs('public.profile') || request()->routeIs('profile') ? 'active' : '' }}">
                    <i class="bi bi-person-circle"></i>
                    <span>Profile</span>
                </a>
            @endif
        @elseauth('staff')
            @php
                $sUser = Auth::guard('staff')->user();
                $sUnread = $staffUnreadCount ?? 0;
            @endphp
            <a href="{{ route('backoffice.admin.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Admin</span>
            </a>
            <a href="{{ route('backoffice.registration.desk') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.registration.*') ? 'active' : '' }}">
                <i class="bi bi-person-plus-fill"></i>
                <span>Reg Desk</span>
            </a>
            <a href="{{ route('backoffice.adopt.index') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.adopt.*') ? 'active' : '' }}">
                <i class="bi bi-heart-pulse-fill"></i>
                <span>Adopt</span>
            </a>
            <a href="{{ route('backoffice.notifications.index') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.notifications.*') ? 'active' : '' }}">
                <i class="bi bi-bell-fill"></i>
                @if($sUnread > 0)
                    <span class="mobile-nav-badge">{{ $sUnread > 9 ? '9+' : $sUnread }}</span>
                @endif
                <span>Alerts</span>
            </a>
            <a href="{{ route('backoffice.profile') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>
        @else
            <a href="{{ route('landing') }}" class="mobile-nav-item {{ request()->routeIs('landing') ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('public.register') }}" class="mobile-nav-item {{ request()->routeIs('public.register') ? 'active' : '' }}">
                <i class="bi bi-stopwatch-fill"></i>
                <span>Register</span>
            </a>
            <a href="{{ route('login') }}" class="mobile-nav-item {{ request()->routeIs('login') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Sign In</span>
            </a>
            <a href="{{ route('backoffice.login') }}" class="mobile-nav-item {{ request()->routeIs('backoffice.login') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Staff</span>
            </a>
        @endauth
    </nav>

    <!-- Footer -->
    <footer class="py-4 border-top mt-auto" style="background-color: var(--camp-surface); border-color: var(--camp-border) !important;">
        <div class="container text-center text-muted small">
            <div class="d-flex justify-content-center align-items-center gap-2 mb-2">
                <i class="bi bi-shield-check text-danger"></i>
                <span class="fw-bold text-uppercase tracking-wider">Teen Camp Management System</span>
            </div>
            <p class="mb-2">
                &copy; {{ date('Y') }} Church Teen Camp. Safe, Secure & Season-Isolated.
            </p>
            <div class="pt-2 border-top border-secondary border-opacity-25 d-flex justify-content-center align-items-center gap-3 flex-wrap">
                <a href="{{ route('landing') }}" class="text-muted text-decoration-none small">Home</a>
                <span class="text-muted small">&bull;</span>
                <a href="{{ route('public.register') }}" class="text-muted text-decoration-none small">Register</a>
                <span class="text-muted small">&bull;</span>
                <a href="{{ route('login') }}" class="text-muted text-decoration-none small">Sign In</a>
                <span class="text-muted small">&bull;</span>
                <a href="{{ route('backoffice.login') }}" class="text-danger fw-semibold text-decoration-none small">
                    <i class="bi bi-shield-lock-fill me-1"></i> Admin Log In
                </a>
            </div>
        </div>
    </footer>

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
