<?php

namespace App\Http\Controllers;

use App\Models\CampSeason;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionValue;
use App\Models\Notification;
use App\Models\PackingList;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeenDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        if (!$season) {
            return view('teen.dashboard', [
                'user' => $user,
                'season' => null,
                'registration' => null,
                'packingList' => collect(),
                'forms' => collect(),
                'submissions' => collect(),
            ]);
        }

        $registration = Registration::where('camp_season_id', $season->id)
            ->where('teen_id', $user->id)
            ->first();

        // Packing list items only if released
        $packingList = PackingList::where('camp_season_id', $season->id)
            ->where('is_released', true)
            ->get()
            ->groupBy('category');

        // Forms assigned to teen or both
        $forms = Form::where('camp_season_id', $season->id)
            ->where('is_published', true)
            ->whereIn('target_role', ['teen', 'both'])
            ->with('fields')
            ->get();

        $submissions = FormSubmission::where('camp_season_id', $season->id)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('teen_id', $user->id);
            })
            ->with(['values.field', 'form'])
            ->get()
            ->keyBy('form_id');

        return view('teen.dashboard', [
            'user' => $user,
            'season' => $season,
            'registration' => $registration,
            'packingList' => $packingList,
            'forms' => $forms,
            'submissions' => $submissions,
        ]);
    }

    public function showSubmission(FormSubmission $submission)
    {
        $user = Auth::guard('web')->user();

        if ($submission->teen_id !== $user->id && $submission->user_id !== $user->id) {
            abort(403, 'Unauthorized access to submission.');
        }

        $submission->load(['form.fields', 'values.field', 'teen', 'user', 'reviewedByParent']);

        return view('forms.submission-show', [
            'submission' => $submission,
            'form' => $submission->form,
            'isParent' => false,
            'backRoute' => route('teen.dashboard'),
        ]);
    }

    public function showForm(Form $form)
    {
        $user = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        if ($form->camp_season_id !== $season->id || !in_array($form->target_role, ['teen', 'both'])) {
            abort(403, 'Form not available.');
        }

        $form->load('fields');

        $submission = FormSubmission::where('form_id', $form->id)
            ->where('user_id', $user->id)
            ->with('values')
            ->first();

        return view('teen.form-fill', [
            'form' => $form,
            'submission' => $submission,
            'season' => $season,
            'user' => $user,
        ]);
    }

    public function submitForm(Request $request, Form $form)
    {
        $user = Auth::guard('web')->user();
        $season = CampSeason::getActive();

        $form->load('fields');

        // Check if existing submission was returned or new
        $submission = FormSubmission::firstOrNew([
            'form_id' => $form->id,
            'camp_season_id' => $season->id,
            'user_id' => $user->id,
            'teen_id' => $user->id,
        ]);

        $wasReturned = ($submission->status === 'returned');

        // If form requires parent approval, move to pending_parent_review
        if ($form->requires_parent_approval) {
            $submission->status = 'pending_parent_review';
        } else {
            $submission->status = 'submitted';
            $submission->submitted_at = now();
        }

        $submission->save();

        // Save fields
        foreach ($form->fields as $field) {
            $valRecord = FormSubmissionValue::firstOrNew([
                'form_submission_id' => $submission->id,
                'form_field_id' => $field->id,
            ]);

            if ($field->field_type === 'file_upload') {
                if ($request->hasFile("field_{$field->id}")) {
                    $path = $request->file("field_{$field->id}")->store('form_uploads', 'public');
                    $valRecord->file_path = $path;
                    $valRecord->value = $request->file("field_{$field->id}")->getClientOriginalName();
                }
            } elseif ($field->field_type === 'checkbox') {
                $values = $request->input("field_{$field->id}", []);
                $valRecord->value = is_array($values) ? json_encode($values) : $values;
            } else {
                $valRecord->value = $request->input("field_{$field->id}");
            }

            $valRecord->save();
        }

        if ($form->requires_parent_approval) {
            // Find parent(s) of this teen
            $parents = $user->parents;
            foreach ($parents as $p) {
                Notification::notifyUser(
                    $p->id,
                    $wasReturned ? "Action Required: Revised Camper Form Review" : "Action Required: Child Form Review",
                    $wasReturned 
                        ? "{$user->name} has revised and resubmitted '{$form->title}'. Please review and approve."
                        : "{$user->name} filled out '{$form->title}'. Please review and approve this form.",
                    'form',
                    route('parent.dashboard'),
                    'bi-file-earmark-arrow-up-fill text-warning'
                );
            }

            Notification::notifyUser(
                $user->id,
                "Form Awaiting Parent Sign-off",
                $wasReturned 
                    ? "You revised and resubmitted '{$form->title}'. It was sent to your parent for approval."
                    : "You completed '{$form->title}'. It was sent to your parent for approval.",
                'form',
                route('teen.dashboard'),
                'bi-clock-history text-info'
            );

            $msg = $wasReturned 
                ? 'Form revised! It has been routed back to your parent for review and sign-off.'
                : 'Form filled! It has been routed to your parent for review and sign-off.';
        } else {
            // Direct submission
            Notification::notifyStaff(
                $wasReturned ? "Camper Revised Form Submission" : "New Form Submission",
                $wasReturned 
                    ? "{$user->name} has revised and resubmitted '{$form->title}'."
                    : "{$user->name} submitted '{$form->title}'.",
                'form',
                route('backoffice.forms.show', $form->id),
                'bi-file-earmark-check-fill text-success'
            );

            Notification::notifyUser(
                $user->id,
                "Form Submitted Successfully",
                "Your responses for '{$form->title}' have been submitted to Camp Administration.",
                'form',
                route('teen.dashboard'),
                'bi-check2-circle text-success'
            );

            $msg = $wasReturned ? 'Form revised and submitted successfully!' : 'Form submitted successfully!';
        }

        return redirect()->route('teen.dashboard')->with('success', $msg);
    }
}
