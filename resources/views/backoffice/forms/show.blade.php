@extends('layouts.backoffice')

@section('title', 'Form Submissions: ' . $form->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0">{{ $form->title }}</h3>
            <p class="text-muted small mb-0">Audience: <strong>{{ ucfirst($form->target_role) }}</strong> | Total Submissions: <strong>{{ $form->submissions->count() }}</strong></p>
        </div>
        <a href="{{ route('backoffice.forms.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Forms
        </a>
    </div>

    <!-- Submissions Table -->
    <div class="camp-card p-4">
        <div class="table-responsive">
            <table class="table table-camp align-middle">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Submission Status</th>
                        @foreach($form->fields as $field)
                            <th>{{ $field->label }}</th>
                        @endforeach
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($form->submissions as $sub)
                        @php $vals = $sub->values->keyBy('form_field_id'); @endphp
                        <tr>
                            <td>
                                <div class="fw-bold">{{ $sub->user->name }}</div>
                                <div class="small text-muted">{{ $sub->user->email }} ({{ ucfirst($sub->user->role) }})</div>
                                @if($sub->teen && $sub->teen->id !== $sub->user->id)
                                    <div class="small text-danger">Camper: {{ $sub->teen->name }}</div>
                                @endif
                            </td>
                            <td>
                                @if($sub->status === 'submitted')
                                    <span class="badge bg-success"><i class="bi bi-check-all"></i> Submitted</span>
                                @elseif($sub->status === 'pending_parent_review')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> Awaiting Parent Review</span>
                                @elseif($sub->status === 'returned')
                                    <span class="badge bg-danger"><i class="bi bi-arrow-return-left"></i> Returned</span>
                                @endif
                            </td>
                            @foreach($form->fields as $field)
                                @php $v = $vals->get($field->id); @endphp
                                <td class="small">
                                    @if($v)
                                        @if($v->file_path)
                                            <a href="{{ asset('storage/' . $v->file_path) }}" target="_blank" class="text-danger">
                                                <i class="bi bi-file-earmark me-1"></i> {{ $v->value }}
                                            </a>
                                        @else
                                            {{ $v->value }}
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="small text-muted">{{ $sub->submitted_at ? $sub->submitted_at->format('M d, Y H:i') : $sub->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 3 + $form->fields->count() }}" class="text-center text-muted py-4">No submissions received for this form yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
