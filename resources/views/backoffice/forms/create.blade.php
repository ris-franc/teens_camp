@extends('layouts.backoffice')

@section('title', 'Build Custom Form')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold mb-0">Build Custom Form</h3>
            <p class="text-muted small mb-0">Create fields, options, and assignment parameters.</p>
        </div>
        <a href="{{ route('backoffice.forms.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    <form action="{{ route('backoffice.forms.store') }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Form Parameters -->
            <div class="col-lg-4">
                <div class="camp-card p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2">Form Settings</h5>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Form Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" placeholder="e.g. Dietary & Activity Preferences" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Instructions / Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Brief instructions for respondents"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Target Audience</label>
                        <select class="form-select" name="target_role" required>
                            <option value="both" selected>Both Teens and Parents</option>
                            <option value="teen">Teens Only</option>
                            <option value="parent">Parents Only</option>
                        </select>
                    </div>

                    <div class="form-check p-3 border rounded bg-body-tertiary mb-3">
                        <input class="form-check-input" type="checkbox" name="requires_parent_approval" value="1" id="requires_parent_approval" checked>
                        <label class="form-check-label fw-semibold" for="requires_parent_approval">
                            Require Parent Sign-Off
                        </label>
                        <div class="form-text small">
                            If checked, teen-filled forms will route to the parent for review and approval before camp staff review.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-camp-red w-100 py-2">
                        <i class="bi bi-save me-1"></i> Publish Form
                    </button>
                </div>
            </div>

            <!-- Dynamic Fields Builder -->
            <div class="col-lg-8">
                <div class="camp-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <h5 class="fw-bold mb-0">Form Fields</h5>
                        <button type="button" class="btn btn-camp-outline-red btn-sm" onclick="addFormField()">
                            <i class="bi bi-plus-lg me-1"></i> Add Question Field
                        </button>
                    </div>

                    <div id="form-fields-container" class="d-flex flex-column gap-3">
                        <!-- Initial Default Field -->
                        <div class="field-item p-3 border rounded" style="background-color: var(--camp-surface-secondary); border-color: var(--camp-border) !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-danger">Field #1</span>
                                <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeField(this)" title="Remove Field">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <label class="form-label small fw-bold">Question / Label <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="fields[0][label]" placeholder="e.g. Do you have any dietary restrictions?" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold">Field Type</label>
                                    <select class="form-select form-select-sm" name="fields[0][field_type]" onchange="toggleOptionsInput(this)">
                                        <option value="text">Text Input</option>
                                        <option value="dropdown">Dropdown Select</option>
                                        <option value="multiple_choice">Multiple Choice (Radio)</option>
                                        <option value="checkbox">Checkboxes (Multi)</option>
                                        <option value="file_upload">File Upload</option>
                                    </select>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[0][is_required]" value="1" id="req_0" checked>
                                        <label class="form-check-label small" for="req_0">Required</label>
                                    </div>
                                </div>
                                <div class="col-12 options-row" style="display: none;">
                                    <label class="form-label small text-muted">Options (comma-separated):</label>
                                    <input type="text" class="form-control form-control-sm" name="fields[0][options]" placeholder="e.g. Vegetarian, Vegan, Gluten Free, None">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    let fieldCount = 1;

    function addFormField() {
        const container = document.getElementById('form-fields-container');
        const idx = fieldCount++;

        const div = document.createElement('div');
        div.className = 'field-item p-3 border rounded';
        div.style.backgroundColor = 'var(--camp-surface-secondary)';
        div.style.borderColor = 'var(--camp-border)';
        div.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-danger">Field #${idx + 1}</span>
                <button type="button" class="btn btn-outline-danger btn-sm p-1" onclick="removeField(this)" title="Remove Field">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-7">
                    <label class="form-label small fw-bold">Question / Label <span class="text-danger">*</span></label>
                    <input type="text" class="form-control form-control-sm" name="fields[${idx}][label]" placeholder="e.g. Camp T-Shirt Size" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">Field Type</label>
                    <select class="form-select form-select-sm" name="fields[${idx}][field_type]" onchange="toggleOptionsInput(this)">
                        <option value="text">Text Input</option>
                        <option value="dropdown">Dropdown Select</option>
                        <option value="multiple_choice">Multiple Choice (Radio)</option>
                        <option value="checkbox">Checkboxes (Multi)</option>
                        <option value="file_upload">File Upload</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="fields[${idx}][is_required]" value="1" id="req_${idx}">
                        <label class="form-check-label small" for="req_${idx}">Required</label>
                    </div>
                </div>
                <div class="col-12 options-row" style="display: none;">
                    <label class="form-label small text-muted">Options (comma-separated):</label>
                    <input type="text" class="form-control form-control-sm" name="fields[${idx}][options]" placeholder="e.g. Small, Medium, Large, XL">
                </div>
            </div>
        `;
        container.appendChild(div);
    }

    function removeField(btn) {
        const item = btn.closest('.field-item');
        if (document.querySelectorAll('.field-item').length > 1) {
            item.remove();
        } else {
            alert('A form must have at least one field.');
        }
    }

    function toggleOptionsInput(select) {
        const row = select.closest('.field-item').querySelector('.options-row');
        if (select.value === 'dropdown' || select.value === 'multiple_choice' || select.value === 'checkbox') {
            row.style.display = 'block';
        } else {
            row.style.display = 'none';
        }
    }
</script>
@endpush
@endsection
