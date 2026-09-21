@extends('layouts.app')

@section('title', 'Fill Camp Form: ' . $form->title)

@section('content')
<div class="container py-3 py-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="camp-card p-3 p-md-4 p-lg-5">
                
                {{-- Header & Back button --}}
                <div class="d-flex align-items-center justify-content-between border-bottom border-secondary border-opacity-25 pb-3 mb-4">
                    <div>
                        <a href="{{ route('parent.dashboard') }}" class="text-danger small text-decoration-none mb-2 d-inline-flex align-items-center gap-1 fw-bold">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-danger text-uppercase px-2 py-1" style="font-size: 10px;">Parent Consent &amp; Survey</span>
                            @if($submission)
                                <span class="badge bg-success py-1 px-2" style="font-size: 10px;">
                                    <i class="bi bi-check2 me-1"></i> Previously Submitted
                                </span>
                            @endif
                        </div>
                        <h3 class="fw-black text-white mb-1" style="letter-spacing: -.5px;">{{ $form->title }}</h3>
                        <p class="text-white-50 small mb-0">{{ $form->description ?: 'Please provide accurate information for camp leadership and staff.' }}</p>
                    </div>
                </div>

                @if($submission && $submission->status === 'returned')
                    <div class="alert alert-danger mb-4 border-0 p-3 rounded" style="background: rgba(239, 68, 68, 0.15); color: #FCA5A5;">
                        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Form Returned for Revision</h6>
                        @if($submission->admin_feedback)
                            <div class="small text-white mb-1"><strong>Camp Admin Feedback:</strong> {{ $submission->admin_feedback }}</div>
                        @endif
                        @if($submission->parent_feedback)
                            <div class="small text-white"><strong>Feedback:</strong> {{ $submission->parent_feedback }}</div>
                        @endif
                        <div class="small text-white-50 mt-1">Please review the instructions above, update your answers, and resubmit.</div>
                    </div>
                @endif

                <form action="{{ route('parent.forms.submit', $form) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Camper Selector if Parent has Multiple Campers --}}
                    @if($teens->count() > 1)
                        <div class="p-3 mb-4 rounded border border-danger border-opacity-30" style="background: rgba(239, 68, 68, 0.05);">
                            <label class="form-label fw-bold text-white small mb-1">
                                <i class="bi bi-person-badge text-danger me-1"></i> Which camper is this form for?
                            </label>
                            <select name="teen_id" class="form-select bg-dark text-white border-secondary" onchange="window.location.href='{{ route('parent.forms.show', $form) }}?teen_id=' + this.value">
                                @foreach($teens as $t)
                                    <option value="{{ $t->id }}" {{ $selectedTeenId == $t->id ? 'selected' : '' }}>
                                        {{ $t->name }} (Camper)
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-white-50">Select a camper to view or save answers specifically for them.</div>
                        </div>
                    @elseif($teens->count() === 1)
                        <input type="hidden" name="teen_id" value="{{ $teens->first()->id }}">
                        <div class="mb-3 text-white-50 small">
                            <i class="bi bi-person-check text-danger me-1"></i> Submitting form for camper: <strong class="text-white">{{ $teens->first()->name }}</strong>
                        </div>
                    @endif

                    @php
                        $valuesByField = $submission ? $submission->values->keyBy('form_field_id') : collect();
                    @endphp

                    {{-- Dynamic Form Fields --}}
                    @foreach($form->fields as $field)
                        @php
                            $savedVal = $valuesByField->get($field->id);
                            $fieldVal = $savedVal ? $savedVal->value : null;
                        @endphp

                        <div class="mb-4 pb-3 border-bottom border-secondary border-opacity-15">
                            <label class="form-label fw-bold text-white mb-1">
                                {{ $field->label }}
                                @if($field->is_required)
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            @if($field->help_text)
                                <div class="form-text text-white-50 small mb-2">{{ $field->help_text }}</div>
                            @endif

                            @if($field->field_type === 'text')
                                <input type="text" class="form-control bg-dark text-white border-secondary" name="field_{{ $field->id }}" 
                                       value="{{ old("field_{$field->id}", $fieldVal) }}" 
                                       placeholder="Enter your response..."
                                       {{ $field->is_required ? 'required' : '' }}>

                            @elseif($field->field_type === 'dropdown')
                                <select class="form-select bg-dark text-white border-secondary" name="field_{{ $field->id }}" {{ $field->is_required ? 'required' : '' }}>
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
                                            <div class="form-check p-2 rounded" style="background: rgba(255,255,255,0.02);">
                                                <input class="form-check-input ms-0 me-2" type="radio" 
                                                       name="field_{{ $field->id }}" 
                                                       id="field_{{ $field->id }}_{{ $loop->index }}" 
                                                       value="{{ $opt }}" 
                                                       style="width: 20px; height: 20px; accent-color: #EF4444;"
                                                       {{ old("field_{$field->id}", $fieldVal) == $opt ? 'checked' : '' }} 
                                                       {{ $field->is_required ? 'required' : '' }}>
                                                <label class="form-check-label text-white small" for="field_{{ $field->id }}_{{ $loop->index }}">
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
                                            <div class="form-check p-2 rounded" style="background: rgba(255,255,255,0.02);">
                                                <input class="form-check-input ms-0 me-2" type="checkbox" 
                                                       name="field_{{ $field->id }}[]" 
                                                       id="field_{{ $field->id }}_{{ $loop->index }}" 
                                                       value="{{ $opt }}" 
                                                       style="width: 20px; height: 20px; accent-color: #EF4444;"
                                                       {{ in_array($opt, $savedArray) ? 'checked' : '' }}>
                                                <label class="form-check-label text-white small" for="field_{{ $field->id }}_{{ $loop->index }}">
                                                    {{ $opt }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>

                            @elseif($field->field_type === 'file_upload')
                                <input type="file" class="form-control bg-dark text-white border-secondary" name="field_{{ $field->id }}" {{ $field->is_required && !$savedVal ? 'required' : '' }}>
                                @if($savedVal && $savedVal->file_path)
                                    <div class="mt-2 small text-success">
                                        <i class="bi bi-file-earmark-check me-1"></i> Current file: {{ $savedVal->value }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach

                    {{-- Bottom Action Buttons --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-2">
                        <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-secondary px-4 py-2">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-camp-red px-4 py-2 fw-bold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-send-fill"></i>
                            <span>{{ $submission ? 'Update Form Responses' : 'Submit Form to Camp Administration' }}</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
