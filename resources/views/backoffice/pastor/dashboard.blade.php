@extends('layouts.backoffice')

@section('title', 'Pastor Portal')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Pastoral Oversight Portal</h3>
            <p class="text-muted small mb-0">Read-only camp statistics, pastoral adopt-a-teen approvals, and system-wide announcements.</p>
        </div>
        <button type="button" class="btn btn-camp-red btn-sm w-100 w-md-auto d-flex align-items-center justify-content-center gap-1 py-2 px-3" data-bs-toggle="modal" data-bs-target="#broadcastModal">
            <i class="bi bi-megaphone-fill me-1"></i> Send System-Wide Announcement
        </button>
    </div>

    <!-- Read-Only Camp Statistics (Responsive 2x2 grid on mobile) -->
    <div class="row g-2 g-md-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="camp-card p-3 h-100">
                <span class="text-muted small text-uppercase fw-bold" style="font-size: 11px;">Total Registered</span>
                <div class="fs-4 fs-md-3 fw-bold mt-1 text-white">{{ $stats['totalRegistered'] ?? 0 }}</div>
                <div class="small text-muted text-truncate" style="font-size: 11px;">{{ $season ? $season->name : 'N/A' }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="camp-card p-3 h-100">
                <span class="text-muted small text-uppercase fw-bold" style="font-size: 11px;">Signed In at Camp</span>
                <div class="fs-4 fs-md-3 fw-bold text-success mt-1">{{ $stats['totalSignedIn'] ?? 0 }}</div>
                <div class="small text-muted" style="font-size: 11px;">Live campsite headcount</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="camp-card p-3 h-100">
                <span class="text-muted small text-uppercase fw-bold" style="font-size: 11px;">Camp Fees Collected</span>
                <div class="fs-4 fs-md-3 fw-bold mt-1 text-white">KES {{ number_format($stats['totalRevenue'] ?? 0, 0) }}</div>
                <div class="small text-muted" style="font-size: 11px;">Direct + Aid Payments</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="camp-card p-3 border-danger h-100">
                <span class="text-muted small text-uppercase fw-bold" style="font-size: 11px;">Adopt Kitty Pool</span>
                <div class="fs-4 fs-md-3 fw-bold text-danger mt-1">KES {{ number_format($stats['kittyBalance'] ?? 0, 0) }}</div>
                <div class="small text-muted" style="font-size: 11px;">Available sponsorship</div>
            </div>
        </div>
    </div>

    <!-- Adopt-a-Teen Pastoral Review Queue -->
    <div class="camp-card p-3 p-md-4 mb-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-3 border-bottom pb-3">
            <div>
                <h5 class="fw-bold mb-0 fs-6 fs-md-5"><i class="bi bi-heart-pulse-fill text-danger me-2"></i> Pastoral Financial Aid Approvals</h5>
                <p class="text-muted small mb-0">Review family applications for camp fee sponsorship from the Kitty fund.</p>
            </div>
            <span class="badge bg-danger">{{ $pendingAdoptRequests->count() }} Awaiting Review</span>
        </div>

        <div class="row g-3">
            @forelse($pendingAdoptRequests as $req)
                <div class="col-12 col-lg-6">
                    <div class="mobile-data-card h-100 p-3">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <h6 class="fw-bold mb-1 text-white fs-6">{{ $req->teen->name }}</h6>
                                <div class="small text-muted">
                                    Parent: <strong class="text-white">{{ $req->parent->name }}</strong>
                                </div>
                                @if($req->parent->phone)
                                    <a href="tel:{{ $req->parent->phone }}" class="text-info text-decoration-none small d-inline-flex align-items-center gap-1 mt-1">
                                        <i class="bi bi-telephone-fill"></i> {{ $req->parent->phone }}
                                    </a>
                                @endif
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div class="fs-5 fw-bold text-danger">KES {{ number_format($req->amount_requested, 2) }}</div>
                                <span class="badge bg-{{ $req->status === 'approved-awaiting-funds' ? 'warning text-dark' : 'secondary' }} mt-1">
                                    {{ ucfirst(str_replace('-', ' ', $req->status)) }}
                                </span>
                            </div>
                        </div>

                        <div class="p-2 rounded small mb-3 border" style="background: rgba(255, 255, 255, 0.025); border-color: rgba(255, 255, 255, 0.08) !important;">
                            <strong class="text-danger">Reason:</strong> {{ $req->reason }}
                        </div>

                        <form action="{{ route('backoffice.pastor.adopt.review', $req) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" name="decision_notes" placeholder="Pastoral blessing / internal notes">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-1 py-2" 
                                        {{ ($stats['kittyBalance'] ?? 0) < $req->amount_requested ? 'disabled' : '' }}>
                                    <i class="bi bi-check-lg"></i> Approve
                                </button>
                                <button type="submit" name="action" value="approve_awaiting_funds" class="btn btn-warning btn-sm flex-grow-1 d-flex align-items-center justify-content-center gap-1 py-2">
                                    Await Funds
                                </button>
                                <button type="submit" name="action" value="deny" class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center px-3 py-2">
                                    Deny
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-4 text-muted">
                    <i class="bi bi-check-circle text-success fs-3 mb-2 d-block"></i>
                    <p class="small mb-0">No pending Adopt-a-Teen applications require review at this time.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Broadcast Announcement Modal -->
    <div class="modal fade" id="broadcastModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('backoffice.pastor.broadcast') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold">Send Pastoral Camp Announcement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Target Audience</label>
                            <select class="form-select" name="target_role" required>
                                <option value="all">Everyone (Teens, Parents & Staff)</option>
                                <option value="parent">Parents Only</option>
                                <option value="teen">Teens Only</option>
                                <option value="staff">Staff Only</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Announcement Title</label>
                            <input type="text" class="form-control" name="title" required placeholder="e.g. Prayer Focus & What to Bring">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Message</label>
                            <textarea class="form-control" name="message" rows="4" required placeholder="Write announcement message..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-camp-red btn-sm">Broadcast Announcement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
