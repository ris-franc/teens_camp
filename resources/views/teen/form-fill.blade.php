@extends('layouts.app')

@section('title', 'Fill Form: ' . $form->title)

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="camp-card p-4 p-md-5">
                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                    <div>
                        <a href="{{ route('teen.dashboard') }}" class="text-muted small text-decoration-none mb-1 d-inline-block">
                            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                        </a>
                        <h3 class="fw-bold mb-1">{{ $form->title }}</h3>
                        <p class="text-muted small mb-0">{{ $form->description }}</p>
                    </div>
                    @if($form->requires_parent_approval)
                        <span class="badge bg-warning text-dark p-2">
                            <i class="bi bi-shield-check me-1"></i> Parent Sign-Off Required
                        </span>
                    @endif
                </div>

                @if($submission && $submission->status === 'returned')
                    <div class="alert alert-danger mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Form Returned by Parent</h6>
                        <p class="mb-0 small"><strong>Parent Notes:</strong> {{ $submission->parent_feedback }}</p>
                    </div>
                @endif

                <form action="{{ route('teen.forms.submit', $form) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    @php
                        $valuesByField = $submission ? $submission->values->keyBy('form_field_id') : collect();
                    @endphp

                    @foreach($form->fields as $field)
                        @php
                            $savedVal = $valuesByField->get($field->id);
                            $fieldVal = $savedVal ? $savedVal->value : null;
                        @endphp

                        <div class="mb-4 pb-3 border-bottom">
                            <label class="form-label fw-bold">
                                {{ $field->label }}
                                @if($field->is_required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @if($field->help_text)
                                <div class="form-text text-muted small mb-2">{{ $field->help_text }}</div>
                            @endif

                            @if($field->field_type === 'text')
                                <input type="text" class="form-control" name="field_{{ $field->id }}" 
                                       value="{{ old("field_{$field->id}", $fieldVal) }}" 
                                       {{ $field->is_required ? 'required' : '' }}>

                            @elseif($field->field_type === 'dropdown')
                                <select class="form-select" name="field_{{ $field->id }}" {{ $field->is_required ? 'required' : '' }}>
                                    <option value="">-- Select an option --</option>
                                    @if(is_array($field->options))
                                        @foreach($field->options as $opt)
                                            <option value="{{ $opt }}" {{ old("field_{$field->id}", $fieldVal) == $opt ? 'selected' : '' }}>
                                                {{ $opt }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>

                            @elseif($field->field_type === 'multiple_choice')
                                <div class="d-flex flex-column gap-2 mt-2">
                                    @if(is_array($field->options))
                                        @foreach($field->options as $opt)
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="field_{{ $field->id }}" 
                                                       id="field_{{ $field->id }}_{{ $loop->index }}" 
                                                       value="{{ $opt }}" 
                                                       {{ old("field_{$field->id}", $fieldVal) == $opt ? 'checked' : '' }} 
                                                       {{ $field->is_required ? 'required' : '' }}>
                                                <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">
                                                    {{ $opt }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                            @elseif($field->field_type === 'checkbox')
                                @php
                                    $savedArray = is_string($fieldVal) ? json_decode($fieldVal, true) : (is_array($fieldVal) ? $fieldVal : []);
                                    if (!is_array($savedArray)) $savedArray = [$fieldVal];
                                @endphp
                                <div class="d-flex flex-column gap-2 mt-2">
                                    @if(is_array($field->options))
                                        @foreach($field->options as $opt)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="field_{{ $field->id }}[]" 
                                                       id="field_{{ $field->id }}_{{ $loop->index }}" 
                                                       value="{{ $opt }}" 
                                                       {{ in_array($opt, $savedArray) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="field_{{ $field->id }}_{{ $loop->index }}">
                                                    {{ $opt }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                            @elseif($field->field_type === 'file_upload')
                                <input type="file" class="form-control" name="field_{{ $field->id }}" {{ $field->is_required && !$savedVal ? 'required' : '' }}>
                                @if($savedVal && $savedVal->file_path)
                                    <div class="mt-2 small text-success">
                                        <i class="bi bi-file-earmark-check me-1"></i> Current file: {{ $savedVal->value }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="{{ route('teen.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-camp-red px-4 py-2">
                            <i class="bi bi-send-fill me-1"></i>
                            {{ $form->requires_parent_approval ? 'Submit for Parent Approval' : 'Submit Form to Camp' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
