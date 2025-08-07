<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\ScheduledMessages;
use App\Models\Step;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StepController extends Controller
{
    public function store(Request $request)
    {

        $messages = [
            'daysOfWeek.*' => 'The :attribute must be a valid day of the week.',
        ];
        
        $validated_data = $request->validate([
            'stepName' => 'required|string|max:255',
            'content' => 'required|string',
            'delay' => 'required|max:255',
            'delayUnit' => 'required|max:255',
            'type' => 'required|string|max:255',
            'workflow' => 'required|integer',
            'endTime' => 'nullable|string|max:255',
            'startTime' => 'nullable|string|max:255',
            'batchSize' => 'nullable|string|max:255',
            'batchDelay' => 'nullable|string|max:255',
            'batchDelayUnit' => 'nullable|max:255',
            'offerExpiry' => 'nullable|max:255',
            'emailSubject' => 'nullable|max:255',
            'isCustomSending' => 'nullable|integer|max:1',
            'make_second_call'=> 'nullable',
        ]);
          $daysOfWeek = $request->daysOfWeek;
    if (is_string($daysOfWeek)) {
        $daysOfWeek = json_decode($daysOfWeek, true);
    }
        if (empty($daysOfWeek)) {
            $validated_data['daysOfWeek'] = null;
        } else {

            $filteredDaysOfWeek = array_filter($daysOfWeek, function ($selected) {
                return $selected;
            });
            $validated_data['daysOfWeek'] = $filteredDaysOfWeek;
        }
        // Handle template files upload
        $templateFilePaths = [];
        if ($request->hasFile('templateFiles')) {
            foreach ($request->file('templateFiles') as $file) {
                $path = $file->store('templates', 'public');
                $templateFilePaths[] = '/storage/' . $path;
            }
        }

        $step = Step::create([
            'workflow_id' => $validated_data['workflow'],
            'type' => $validated_data['type'],
            'content' => $request->input('content'),
            'delay' => $this->convertToMinutes($validated_data['delay'], $validated_data['delayUnit']),
            'name' => $validated_data['stepName'],
            'custom_sending' => $validated_data['isCustomSending'],
            'start_time' => $validated_data['startTime'],
            'end_time' => $validated_data['endTime'],
            'batch_size' => $validated_data['batchSize'],
            'offer_expiry' => $validated_data['offerExpiry'],
            'email_subject' => $validated_data['emailSubject'],
            'batch_delay' => $this->convertToMinutes($validated_data['batchDelay'], $validated_data['batchDelayUnit']),
            'step_quota_balance' => $validated_data['batchSize'],
            'days_of_week' => json_encode($validated_data['daysOfWeek']),
            'make_second_call'=> $validated_data['make_second_call'] ? 1 : 0,
            'template_files' => !empty($templateFilePaths) ? json_encode($templateFilePaths) : null,
        ]);
        $workflow = Workflow::findorfail($request->workflow);
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
        } else {
            $steps_flow_array = [];
        }
        $new_step = $step->id;
        array_push($steps_flow_array, $new_step);
        $workflow->steps_flow = implode(',', $steps_flow_array);
        $workflow->save();
        $steps = array();
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            foreach ($steps_flow_array as $step_flow_array) {
                array_push($steps, Step::findorfail($step_flow_array));
            }
        }
        return response()->json([
            'success' => session('success'),
            'workflow' => $workflow,
            'request' => $request->file('templateFiles'),
            'step' => $step,
            'steps' => $steps
        ], 200);
    }
    public function update(Request $request)
    {
        $messages = [
            'daysOfWeek.*' => 'The :attribute must be a valid day of the week.',
        ];

        $validated_data = $request->validate([
            'stepName' => 'required|string|max:255',
            'content' => 'required|string',
            'delay' => 'required|max:255',
            'delayUnit' => 'required|max:255',
            'type' => 'required|string|max:255',
            'workflow' => 'required|integer',
            'endTime' => 'nullable|string|max:255',
            'startTime' => 'nullable|string|max:255',
            'batchSize' => 'nullable|string|max:255',
            'batchDelay' => 'nullable|max:255',
            'batchDelayUnit' => 'nullable|max:255',
            'custom_sending' => 'nullable',
            'offerExpiry' => 'nullable|max:255',
            'emailSubject' => 'nullable|max:255',
            'make_second_call'=> 'nullable',

        ]);
        // Filter daysOfWeek to include only the selected days (true)
        $filteredDaysOfWeek = array();
        // Check if daysOfWeek is empty
        if (empty($request->daysOfWeek)) {
            $validated_data['daysOfWeek'] = null;
        } else {
            // Filter daysOfWeek to include only the selected days (true)

            $filteredDaysOfWeek = array_filter($request->daysOfWeek, function ($selected) {
                return $selected;
            });
            //dd($filteredDaysOfWeek);
            if (empty($filteredDaysOfWeek)) {
                $validated_data['daysOfWeek'] = null;
            } else {
                $validated_data['daysOfWeek'] = json_encode($filteredDaysOfWeek);
            }
        }
        $step = Step::findOrFail($request->id);
        $step->update([
            'workflow_id' => $validated_data['workflow'],
            'type' => $validated_data['type'],
            'content' => $request->input('content'),
            'delay' => $this->convertToMinutes($validated_data['delay'], $validated_data['delayUnit']),
            'name' => $validated_data['stepName'],
            'custom_sending' => $validated_data['custom_sending'],
            'start_time' => $validated_data['startTime'],
            'end_time' => $validated_data['endTime'],
            'batch_size' => $validated_data['batchSize'],
            'batch_delay' => $this->convertToMinutes($validated_data['batchDelay'], $validated_data['batchDelayUnit']),
            'days_of_week' => $validated_data['daysOfWeek'],
            'offer_expiry' => $validated_data['offerExpiry'],
            'email_subject' => $validated_data['emailSubject'],
            'make_second_call'=> $validated_data['make_second_call'] ? 1 : 0,

        ]);

        $workflow = Workflow::findOrFail($request->workflow);
        $steps = [];
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            foreach ($steps_flow_array as $step_flow_array) {
                array_push($steps, Step::findOrFail($step_flow_array));
            }
        }

        //edit existing workflows
        $contacts = ScheduledMessages::where('workflow_id', $workflow->id)->get();
        foreach ($contacts as $contact) {
            $contact_in_contacts = Contact::find($contact->contact_id);
            $contact_in_contacts->can_send=1;
            $contact_in_contacts->save();
            $contact->delete();
            Log::info("Deleted scheduled message for contact: " . $contact->contact_id);
        }

        return response()->json([
            'success' => session('success'),
            'workflow' => $workflow,
            'steps' => $steps,
            'days' => $filteredDaysOfWeek
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $workflow = Workflow::findorfail($request->workflow);
        $workflow_steps_flow = explode(',', $workflow->steps_flow);
        $step_to_delete = array_search($id, $workflow_steps_flow);
        $new_workflow_steps_flow = array();
        if ($step_to_delete !== false) {
            unset($workflow_steps_flow[$step_to_delete]);
            $new_workflow_steps_flow = array_values($workflow_steps_flow);
        }
        $new_workflow_steps_flow = implode(',', $new_workflow_steps_flow);
        $workflow->steps_flow = $new_workflow_steps_flow;
        $workflow->save();
        $step = Step::findOrFail($id);
        $step->delete();
        $steps = array();
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            foreach ($steps_flow_array as $step_flow_array) {
                array_push($steps, Step::findorfail($step_flow_array));
            }
        }
        return response()->json([
            'success' => session('success'),
            'workflow' => $workflow,
            'steps' => $steps
        ], 200);
    }
    private function convertToMinutes($delay, $delay_units)
    {
        switch ($delay_units) {
            case 'seconds':
                return $delay / 60;
            case 'minutes':
                return $delay; // No conversion needed
            case 'hours':
                return $delay * 60; // Convert hours to minutes
            case 'days':
                return $delay * 1440; // Convert days to minutes (24 hours * 60 minutes)
            default:
                return $delay; // Default to returning original delay if units are unrecognized
        }
    }
    public function step_responses($id)
    {
        $contacts_count = Contact::where('current_step', $id)
            ->where('response', 'Yes')
            ->count();

        return response()->json(['count' => $contacts_count]);
    }
}
