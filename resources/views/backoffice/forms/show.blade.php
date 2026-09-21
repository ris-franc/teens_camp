@extends('layouts.backoffice')

@section('title', 'Form Submissions: ' . $form->title)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-0 text-white">{{ $form->title }}</h3>
            <p class="text-white-50 small mb-0">Audience: <strong>{{ ucfirst($form->target_role) }}</strong> | Total Submissions: <strong>{{ $form->submissions->count() }}</strong></p>
        </div>
        <a href="{{ route('backoffice.forms.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Forms
        </a>
    </div>

    <!-- Submissions Table -->
    <div class="camp-card p-3 p-md-4">
        <div class="table-responsive">
            <table class="table table-camp align-middle">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Status</th>
                        @foreach($form->fields as $field)
                            <th>{{ $field->label }}</th>
                        @endforeach
                        <th>Submitted At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($form->submissions as $sub)
                        @php $vals = $sub->values->keyBy('form_field_id'); @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-white">{{ $sub->user->name }}</div>
                                <div class="small text-white-50">{{ $sub->user->email }} ({{ ucfirst($sub->user->role) }})</div>
                                @if($sub->teen && $sub->teen->id !== $sub->user->id)
                                    <div class="small text-danger fw-semibold">Camper: {{ $sub->teen->name }}</div>
                                @endif
                            </td>
                            <td>
                                @if($sub->status === 'submitted')
                                    <span class="badge bg-success"><i class="bi bi-check-all me-1"></i> Submitted</span>
                                @elseif($sub->status === 'pending_parent_review')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Awaiting Parent Review</span>
                                @elseif($sub->status === 'returned')
                                    <span class="badge bg-danger"><i class="bi bi-arrow-return-left me-1"></i> Returned</span>
                                    @if($sub->returned_to_role)
                                        <div class="small text-white-50 mt-1" style="font-size: 10px;">To: {{ ucfirst($sub->returned_to_role) }}</div>
                                    @endif
                                @endif
                            </td>
                            @foreach($form->fields as $field)
                                @php $v = $vals->get($field->id); @endphp
                                <td class="small">
                                    @if($v)
                                        @if($v->file_path)
                                            <a href="{{ asset('storage/' . $v->file_path) }}" target="_blank" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 11px;">
                                                <i class="bi bi-file-earmark-arrow-down me-1"></i> {{ Str::limit($v->value, 20) }}
                                            </a>
                                        @else
                                            <span class="text-white">{{ Str::limit($v->value, 40) }}</span>
                                        @endif
                                    @else
                                        <span class="text-white-50">-</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="small text-white-50">
                                {{ $sub->submitted_at ? $sub->submitted_at->format('M d, Y • h:i A') : $sub->updated_at->format('M d, Y • h:i A') }}
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex align-items-center gap-1">
                                    @if($sub->status === 'returned')
                                        <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#returnModal-{{ $sub->id }}" title="Inspect or update return instructions">
                                            <i class="bi bi-arrow-repeat me-1"></i> Returned Note
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-outline-warning btn-sm py-1 px-2" data-bs-toggle="modal" data-bs-target="#returnModal-{{ $sub->id }}" title="Return this submission for corrections">
                                            <i class="bi bi-arrow-return-left me-1"></i> Return
                                        </button>
                                    @endif
                                </div>

                                {{-- Return Modal --}}
                                <div class="modal fade text-start" id="returnModal-{{ $sub->id }}" tabindex="-1" aria-labelledby="returnModalLabel-{{ $sub->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-secondary shadow-lg bg-dark text-white">
                                            <div class="modal-header border-secondary border-opacity-50">
                                                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="returnModalLabel-{{ $sub->id }}">
                                                    <i class="bi bi-arrow-return-left text-danger"></i> Return Submission for Revision
                                                </h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('backoffice.forms.submissions.return', $sub) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <div class="p-3 mb-3 rounded bg-black border border-secondary border-opacity-50 small">
                                                        <div class="text-white-50">Participant: <strong class="text-white">{{ $sub->user->name }}</strong> ({{ ucfirst($sub->user->role) }})</div>
                                                        @if($sub->teen)
                                                            <div class="text-white-50">Camper: <strong class="text-danger">{{ $sub->teen->name }}</strong></div>
                                                        @endif
                                                        <div class="text-white-50">Form: <strong class="text-white">{{ $form->title }}</strong></div>
                                                    </div>

                                                    {{-- Target recipient selector --}}
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted">Who should revise / receive this return? <span class="text-danger">*</span></label>
                                                        <select name="return_to" class="form-select bg-black text-white border-secondary" required>
                                                            @if($sub->teen)
                                                                <option value="teen" {{ ($sub->returned_to_role ?? '') === 'teen' ? 'selected' : '' }}>
                                                                    Teen Camper ({{ $sub->teen->name }})
                                                                </option>
                                                                <option value="parent" {{ ($sub->returned_to_role ?? '') === 'parent' ? 'selected' : '' }}>
                                                                    Parent / Guardian
                                                                </option>
                                                                <option value="both" {{ ($sub->returned_to_role ?? '') === 'both' ? 'selected' : '' }}>
                                                                    Both Teen &amp; Parent
                                                                </option>
                                                            @else
                                                                <option value="parent" selected>
                                                                    Parent / Guardian ({{ $sub->user->name }})
                                                                </option>
                                                            @endif
                                                        </select>
                                                        <div class="form-text small text-white-50">The selected party will receive an immediate in-app &amp; email notification with your feedback.</div>
                                                    </div>

                                                    {{-- Revision feedback --}}
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold text-muted">Reason / Corrections Required <span class="text-danger">*</span></label>
                                                        <textarea name="admin_feedback" class="form-control bg-black text-white border-secondary" rows="4" required placeholder="Explain clearly what needs to be changed, corrected, or attached...">{{ old('admin_feedback', $sub->admin_feedback) }}</textarea>
                                                        <div class="form-text small text-white-50">This message is sent directly to the participant and displayed on their dashboard.</div>
                                                    </div>

                                                    @if($sub->status === 'returned' && $sub->returned_at)
                                                        <div class="alert alert-dark small border-secondary py-2 mb-0">
                                                            <i class="bi bi-clock-history me-1 text-warning"></i>
                                                            Previously returned on {{ $sub->returned_at->format('M d, Y • h:i A') }}
                                                            @if($sub->returnedByStaff)
                                                                by {{ $sub->returnedByStaff->name }}
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer border-secondary border-opacity-50">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger btn-sm px-3 fw-bold">
                                                        <i class="bi bi-send-fill me-1"></i> Send Return &amp; Notify
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 4 + $form->fields->count() }}" class="text-center text-white-50 py-4">
                                <i class="bi bi-clipboard-x fs-2 d-block mb-2 text-danger"></i>
                                No submissions received for this form yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
