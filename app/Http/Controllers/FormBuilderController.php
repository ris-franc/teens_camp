<?php

namespace App\Http\Controllers;

use App\Models\CampSeason;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormBuilderController extends Controller
{
    public function index()
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();
        $forms = collect();

        if ($season) {
            $forms = Form::where('camp_season_id', $season->id)
                ->withCount(['fields', 'submissions'])
                ->get();
        }

        return view('backoffice.forms.index', [
            'season' => $season,
            'forms' => $forms,
        ]);
    }

    public function create()
    {
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();
        return view('backoffice.forms.create', ['season' => $season]);
    }

    public function store(Request $request)
    {
        $staff = Auth::guard('staff')->user();
        $season = CampSeason::find(session('admin_selected_season_id')) ?? CampSeason::getActive();

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_role' => ['required', 'in:teen,parent,both'],
            'requires_parent_approval' => ['nullable', 'boolean'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.field_type' => ['required', 'in:text,dropdown,multiple_choice,checkbox,file_upload'],
            'fields.*.options' => ['nullable', 'string'],
            'fields.*.is_required' => ['nullable', 'boolean'],
            'fields.*.help_text' => ['nullable', 'string', 'max:255'],
        ]);

        $form = Form::create([
            'camp_season_id' => $season->id,
            'title' => $request->title,
            'description' => $request->description,
            'target_role' => $request->target_role,
            'requires_parent_approval' => $request->boolean('requires_parent_approval'),
            'is_published' => true,
            'created_by' => $staff->id,
        ]);

        foreach ($request->fields as $idx => $fData) {
            $options = null;
            if (!empty($fData['options']) && in_array($fData['field_type'], ['dropdown', 'multiple_choice'])) {
                $options = array_map('trim', explode(',', $fData['options']));
            }

            FormField::create([
                'form_id' => $form->id,
                'label' => $fData['label'],
                'field_type' => $fData['field_type'],
                'options' => $options,
                'is_required' => !empty($fData['is_required']),
                'help_text' => $fData['help_text'] ?? null,
                'order_index' => $idx,
            ]);
        }

        return redirect()->route('backoffice.forms.index')->with('success', "Form '{$form->title}' created and assigned to " . ucfirst($form->target_role) . " successfully!");
    }

    public function show(Form $form)
    {
        $form->load(['fields', 'submissions.user', 'submissions.teen', 'submissions.values.field']);
        return view('backoffice.forms.show', ['form' => $form]);
    }
}
