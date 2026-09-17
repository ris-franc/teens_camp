@extends('layouts.backoffice')

@section('title', 'Dynamic Form Builder')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0">Dynamic Form Builder & Assignments</h3>
            <p class="text-muted small mb-0">Design custom forms, assign target roles (teen/parent/both), and configure parent sign-off oversight.</p>
        </div>
        <a href="{{ route('backoffice.forms.create') }}" class="btn btn-camp-red btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Build New Form
        </a>
    </div>

    <div class="camp-card p-4">
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($forms as $f)
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $f->title }}</div>
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
                            <td>{{ $f->created_at->format('M d, Y') }}</td>
                            <td>
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
