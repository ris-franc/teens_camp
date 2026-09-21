@extends('layouts.backoffice')

@section('title', 'Dynamic Form Builder')

@section('content')
<div class="container-fluid px-0 px-md-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold mb-1 text-white">Dynamic Form Builder &amp; Hub</h3>
            <p class="text-white-50 small mb-0">Design custom forms, assign target roles (teen/parent/both), and configure parent sign-off oversight.</p>
        </div>
        <a href="{{ route('backoffice.forms.create') }}" class="btn btn-camp-red btn-sm align-self-start align-self-sm-center">
            <i class="bi bi-plus-circle me-1"></i> Build New Form
        </a>
    </div>

    <!-- Mobile Cards List (< 768px) -->
    <div class="d-md-none mb-4">
        @forelse($forms as $f)
            <div class="camp-card mb-3 p-3 border border-secondary border-opacity-25 shadow-sm">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <div class="fw-bold text-white fs-6">{{ $f->title }}</div>
                        <div class="small text-white-50 mt-1">{{ $f->description ?: 'No description provided' }}</div>
                    </div>
                    <span class="badge bg-dark border text-uppercase" style="font-size: 10px;">
                        {{ $f->target_role }}
                    </span>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2 mb-3 small">
                    <span class="badge bg-danger">{{ $f->submissions_count }} submissions</span>
                    <span class="badge bg-secondary">{{ $f->fields_count }} fields</span>
                    @if($f->requires_parent_approval)
                        <span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i> Parent Sign-Off</span>
                    @else
                        <span class="badge bg-dark border text-muted">Direct Submit</span>
                    @endif
                    <span class="text-white-50 ms-auto" style="font-size: 11px;">
                        <i class="bi bi-calendar3 me-1"></i> {{ $f->created_at->format('M d, Y') }}
                    </span>
                </div>

                <a href="{{ route('backoffice.forms.show', $f) }}" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="bi bi-eye"></i>
                    <span>View Submissions ({{ $f->submissions_count }})</span>
                </a>
            </div>
        @empty
            <div class="camp-card p-4 text-center text-white-50">
                <i class="bi bi-file-earmark-x fs-2 d-block mb-2 text-danger"></i>
                No custom forms created for this season yet.
            </div>
        @endforelse
    </div>

    <!-- Desktop Table View (>= 768px) -->
    <div class="camp-card p-4 d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-camp align-middle">
                <thead>
                    <tr>
                        <th>Form Title</th>
                        <th>Target Audience</th>
                        <th>Parent Sign-Off</th>
                        <th>Fields</th>
                        <th>Submissions</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $f)
                        <tr>
                            <td>
                                <div class="fw-bold text-white">{{ $f->title }}</div>
                                <div class="small text-muted">{{ $f->description ?: 'No description provided' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-dark border text-uppercase">
                                    {{ $f->target_role }}
                                </span>
                            </td>
                            <td>
                                @if($f->requires_parent_approval)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i> Required</span>
                                @else
                                    <span class="text-muted small">Direct Submit</span>
                                @endif
                            </td>
                            <td>{{ $f->fields_count }} fields</td>
                            <td>
                                <span class="badge bg-danger">{{ $f->submissions_count }} submissions</span>
                            </td>
                            <td class="small text-white-50">{{ $f->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('backoffice.forms.show', $f) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-eye me-1"></i> View Submissions
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No custom forms created for this season yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
