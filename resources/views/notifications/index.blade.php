@extends($user->isStaff() ? 'layouts.backoffice' : 'layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="camp-card p-4">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div>
                        <h4 class="fw-bold mb-0"><i class="bi bi-bell-fill text-danger me-2"></i> Notification Center</h4>
                        <p class="text-muted small mb-0">System updates, payment confirmations, form alerts, and announcements.</p>
                    </div>
                    <form action="{{ route('notifications.read-all') }}" method="POST" class="form-mark-all-read">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-check2-all me-1"></i> Mark All as Read
                        </button>
                    </form>
                </div>

                <div class="list-group list-group-flush">
                    @forelse($notifications as $notif)
                        <div class="list-group-item notif-clickable-item bg-transparent px-0 py-3 border-bottom {{ !$notif->is_read ? 'fw-bold border-start border-3 border-danger ps-2' : 'opacity-75' }}"
                             data-notif-id="{{ $notif->id }}"
                             data-is-read="{{ $notif->is_read ? '1' : '0' }}"
                             data-read-url="{{ route('notifications.read', $notif) }}"
                             data-target-link="{{ $notif->link ?: '' }}"
                             style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="p-2 rounded bg-dark border border-secondary text-white">
                                        <i class="bi {{ $notif->icon_class }}"></i>
                                    </span>
                                    <div>
                                        <h6 class="mb-0 {{ !$notif->is_read ? 'text-danger' : '' }}">{{ $notif->title }}</h6>
                                        <span class="badge bg-dark border border-secondary text-white-50 text-uppercase" style="font-size: 9px;">{{ str_replace('_', ' ', $notif->type) }}</span>
                                    </div>
                                </div>
                                <span class="small text-muted">{{ $notif->created_at ? $notif->created_at->diffForHumans() : 'Just now' }}</span>
                            </div>
                            <p class="mb-2 text-muted small ps-5">{{ $notif->message }}</p>
                            <div class="ps-5 d-flex gap-3 align-items-center">
                                @if($notif->link)
                                    <a href="{{ $notif->link }}" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 11px;">
                                        View Action &rarr;
                                    </a>
                                @endif
                                @if(!$notif->is_read)
                                    <form action="{{ route('notifications.read', $notif) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link btn-sm text-white-50 p-0 text-decoration-none small" style="font-size: 11px;">
                                            Mark as read
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bell-slash fs-2 mb-2 d-block"></i>
                            <h6>No Notifications</h6>
                            <p class="small mb-0">You're all caught up!</p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
