@extends('layouts.app')

@section('title', 'Form Submission: ' . $form->title)

@section('content')
<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="camp-card p-3 p-md-4 p-lg-5">
                
                {{-- Header --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                    <div>
                        <a href="{{ $backRoute ?? url()->previous() }}" class="text-danger small text-decoration-none mb-2 d-inline-flex align-items-center gap-1 fw-bold">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger text-uppercase px-2 py-1" style="font-size: 10px;">Form Record</span>
                            @if($submission->status === 'submitted')
                                <span class="badge bg-success py-1 px-2" style="font-size: 10px;">
                                    <i class="bi bi-patch-check-fill me-1"></i> Officially Submitted
                                </span>
                            @elseif($submission->status === 'pending_parent_review')
                                <span class="badge bg-warning text-dark py-1 px-2 fw-bold" style="font-size: 10px;">
                                    <i class="bi bi-hourglass-split me-1"></i> Awaiting Parent Sign-off
                                </span>
                            @elseif($submission->status === 'returned')
                                <span class="badge bg-danger py-1 px-2" style="font-size: 10px;">
                                    <i class="bi bi-arrow-repeat me-1"></i> Returned for Correction
                                </span>
                            @endif
                        </div>
                        <h3 class="fw-black text-white mb-1" style="letter-spacing: -.5px;">{{ $form->title }}</h3>
                        <p class="text-white-50 small mb-0">{{ $form->description }}</p>
                    </div>

                    <div class="text-end">
                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm py-2 px-3 d-inline-flex align-items-center gap-1">
                            <i class="bi bi-printer-fill"></i> Print
                        </button>
                    </div>
                </div>

                {{-- Submission Metadata Card --}}
                <div class="p-3 mb-4 rounded panel-well" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);">
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <span class="text-white-50 d-block" style="font-size: 11px;">SUBMITTED BY:</span>
                            <strong class="text-white">{{ $submission->user?->name ?? 'User' }}</strong>
                            <span class="badge bg-secondary ms-1" style="font-size: 10px;">{{ ucfirst($submission->user?->role ?? '') }}</span>
                        </div>
                        @if($submission->teen)
                            <div class="col-sm-6">
                                <span class="text-white-50 d-block" style="font-size: 11px;">CAMPER BENEFICIARY:</span>
                                <strong class="text-danger">{{ $submission->teen->name }}</strong>
                            </div>
                        @endif
                        <div class="col-sm-6">
                            <span class="text-white-50 d-block" style="font-size: 11px;">SUBMITTED AT:</span>
                            <span class="text-white">{{ $submission->submitted_at ? $submission->submitted_at->format('M d, Y • h:i A') : ($submission->created_at->format('M d, Y • h:i A')) }}</span>
                        </div>
                        @if($submission->reviewed_by_parent_id)
                            <div class="col-sm-6">
                                <span class="text-white-50 d-block" style="font-size: 11px;">PARENT SIGN-OFF:</span>
                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Approved by {{ $submission->reviewedByParent?->name ?? 'Parent' }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Admin Feedback Alert --}}
                @if($submission->admin_feedback)
                    <div class="alert alert-danger mb-4 border-0 p-3 rounded" style="background: rgba(239, 68, 68, 0.15); color: #FCA5A5;">
                        <div class="fw-bold small text-uppercase mb-1">
                            <i class="bi bi-exclamation-octagon-fill me-1"></i> Camp Administration Feedback / Instructions:
                        </div>
                        <p class="mb-0 small text-white">{{ $submission->admin_feedback }}</p>
                    </div>
                @endif

                {{-- Parent Feedback Alert --}}
                @if($submission->parent_feedback)
                    <div class="alert alert-warning mb-4 border-0 p-3 rounded" style="background: rgba(234, 179, 8, 0.12); color: #FDE047;">
                        <div class="fw-bold small text-uppercase mb-1"><i class="bi bi-chat-quote-fill me-1"></i> Parent Sign-Off Feedback / Notes:</div>
                        <p class="mb-0 small text-white">{{ $submission->parent_feedback }}</p>
                    </div>
                @endif

                {{-- Submitted Answers List --}}
                <div class="d-flex flex-column gap-3 mb-4">
                    @php
                        $valuesByField = $submission->values->keyBy('form_field_id');
                    @endphp

                    @forelse($form->fields as $field)
                        @php
                            $val = $valuesByField->get($field->id);
                        @endphp
                        <div class="p-3 rounded" style="background: rgba(0,0,0,0.25); border: 1px solid rgba(255,255,255,0.06);">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-white-50 small">{{ $field->label }}</span>
                                <span class="badge bg-dark border border-secondary text-secondary" style="font-size: 10px;">{{ ucfirst(str_replace('_', ' ', $field->field_type)) }}</span>
                            </div>
                            
                            <div class="fs-6 text-white fw-semibold mt-1">
                                @if($field->field_type === 'file_upload')
                                    @if($val && $val->file_path)
                                        <a href="{{ asset('storage/' . $val->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger py-1 px-3 d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-file-earmark-arrow-down-fill"></i> Download Attached Document ({{ $val->value }})
                                        </a>
                                    @else
                                        <span class="text-muted fst-italic">No document attached</span>
                                    @endif
                                @elseif($field->field_type === 'checkbox')
                                    @php
                                        $parsed = is_string($val?->value) ? json_decode($val->value, true) : (is_array($val?->value) ? $val->value : []);
                                    @endphp
                                    @if(!empty($parsed) && is_array($parsed))
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            @foreach($parsed as $item)
                                                <span class="badge bg-danger-subtle text-danger border border-danger">{{ $item }}</span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-white">{{ $val?->value ?: 'None selected' }}</span>
                                    @endif
                                @else
                                    <span>{{ $val?->value ?: '—' }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small">No questions defined for this form.</p>
                    @endforelse
                </div>

                {{-- Action Bar --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-3 border-top border-secondary border-opacity-25">
                    <a href="{{ $backRoute ?? url()->previous() }}" class="btn btn-outline-secondary btn-sm px-3">
                        &larr; Return to Dashboard
                    </a>

                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        {{-- Parent Actions on Pending Teen Submission --}}
                        @if($isParent && $submission->status === 'pending_parent_review')
                            <form action="{{ route('parent.forms.approve', $submission) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm px-3 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Approve &amp; Sign
                                </button>
                            </form>

                            <button type="button" class="btn btn-outline-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#returnInspectorModal">
                                <i class="bi bi-arrow-return-left me-1"></i> Return to Camper
                            </button>

                            {{-- Return Modal --}}
                            <div class="modal fade" id="returnInspectorModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content" style="background: #141418; color: #fff; border: 1px solid rgba(255,255,255,.1);">
                                        <form action="{{ route('parent.forms.return', $submission) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-secondary border-opacity-25">
                                                <h5 class="modal-title fw-bold">Return Form to {{ $submission->teen->name }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="small text-white-50">Provide guidance or adjustments needed before you can sign off.</p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold text-white">Corrections Required</label>
                                                    <textarea class="form-control bg-dark text-white border-secondary" name="parent_feedback" rows="3" required placeholder="e.g. Please update your dietary preferences or cabin requests."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-secondary border-opacity-25">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger btn-sm">Return Form</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Revision / Edit Buttons --}}
                        @if($isParent && in_array($form->target_role, ['parent', 'both']))
                            <a href="{{ route('parent.forms.show', $form) }}{{ $submission->teen_id ? '?teen_id=' . $submission->teen_id : '' }}" class="btn btn-camp-red btn-sm px-3">
                                <i class="bi bi-pencil-square me-1"></i> {{ $submission->status === 'returned' ? 'Revise & Resubmit' : 'Edit Form Responses' }}
                            </a>
                        @elseif(!$isParent && in_array($form->target_role, ['teen', 'both']) && $submission->status === 'returned')
                            <a href="{{ route('teen.forms.show', $form) }}" class="btn btn-camp-red btn-sm px-3">
                                <i class="bi bi-pencil-square me-1"></i> Revise &amp; Resubmit
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
