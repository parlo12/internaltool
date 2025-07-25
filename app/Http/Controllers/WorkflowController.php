<?php

namespace App\Http\Controllers;

use App\Jobs\CreateWorkflowContactsJob;
use App\Jobs\FillContactDetails;
use App\Models\CallsSent;
use App\Models\Contact;
use App\Models\Folder;
use App\Models\Number;
use App\Models\NumberPool;
use App\Models\Organisation;
use App\Models\Spintax;
use App\Models\Step;
use App\Models\Workflow;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\TwiML\VoiceResponse;
use App\Services\CRMAPIRequestsService;
use App\Services\DynamicTagsService;

class WorkflowController extends Controller
{
    public function store(Request $request)
    {
        $CRMAPIRequestsService = new CRMAPIRequestsService(auth()->user()->godspeedoffers_api);
        $organisationId = auth()->user()->organisation_id;
        if (!$organisationId) {
            return redirect()->route('create-workflow')
                ->with('error', 'You must belong to an organisation.');
        }
        ini_set('max_execution_time', 0);
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_group' => 'required|string|max:255',
            'calling_number' => 'max:255',
            'texting_number' => 'max:255',
            'number_pool_id' => 'max:255'
        ]);
        if (!$CRMAPIRequestsService->group_has_contacts($request->contact_group)) {
            return redirect()->route('create-workflow')
                ->with('error', 'The group must have atleast one contact. Add from godspeed offers.');
        }
        $group_name = $CRMAPIRequestsService->get_group_name($request->contact_group);
        $contacts = $CRMAPIRequestsService->get_all_contacts($request->contact_group);
        $workflow = Workflow::Create(
            [
                'name' => $request->name,
                'contact_group' => $group_name,
                'active' => 0,
                'group_id' => $request->contact_group,
                'voice' => $request->voice ? $request->voice : 'cjVigY5qzO86Huf0OWal',
                'agent_number' => $request->agent_phone_number,
                'texting_number' => $request->texting_number,
                'calling_number' => $request->calling_number,
                'number_pool_id' => $request->number_pool_id,
                'generated_message' => $request->generated_message,
                'organisation_id' => $organisationId,
                'godspeedoffers_api' => auth()->user()->godspeedoffers_api,
                'user_id' => auth()->user()->id,
            ]
        );
        // *******************************************
        if ($request->generated_message) {
            $step = Step::create([
                'workflow_id' => $workflow->id,
                'type' => 'SMS',
                'content' => 'It is already generated',
                'delay' => $this->convertToMinutes('1', 'days'),
                'name' => 'Generated Step',
                'custom_sending' => 1,
                'start_time' => null,
                'end_time' => null,
                'batch_size' => null,
                'offer_expiry' => null,
                'email_subject' => null,
                'batch_delay' => $this->convertToMinutes('2', 'hours'),
                'step_quota_balance' => '20',
                'days_of_week' => '{"Sunday":true,"Monday":true,"Tuesday":true,"Wednesday":true,"Thursday":true,"Friday":true,"Saturday":true}',
                'generated_message' => 1
            ]);
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
        }
        //****************************************8 */
        foreach ($contacts as $contact) {
            $organisationId = auth()->user()->organisation_id;
            CreateWorkflowContactsJob::dispatch($contact['uid'], $request->contact_group, $workflow->id, $contact['phone'], $organisationId)
                ->onQueue('Contacts');
        }
        $steps = array();
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            foreach ($steps_flow_array as $step_flow_array) {
                array_push($steps, Step::findorfail($step_flow_array));
            }
        }
        return redirect()->route('add_steps', ['workflow' => $workflow->id])
            ->with('success', 'Workflow created successfulyy.');
    }



    public function create_contacts_for_workflows($contact_uid, $contact_group, $workflow_id, $contact_phone, $organisationId)
    {
        $workflow = Workflow::find($workflow_id);
        $CRMAPIRequestsService = new CRMAPIRequestsService($workflow->godspeedoffers_api);
        $first_name = $CRMAPIRequestsService->get_contact($contact_uid, $contact_group)['custom_fields']['FIRST_NAME'];
        $last_name = $CRMAPIRequestsService->get_contact($contact_uid, $contact_group)['custom_fields']['LAST_NAME'];
        $contact = Contact::create(
            [
                'uuid' => $contact_uid,
                'workflow_id' => $workflow_id,
                'phone' => $contact_phone,
                'can_send' => 1,
                'response' => 'No',
                'contact_name' => $first_name . " " . $last_name,
                'status' => 'WAITING_FOR_QUEAUE',
                'cost' => 0,
                'subscribed' => 1,
                'organisation_id' => $organisationId,
                'user_id' => $workflow->user_id
            ]
        );
        FillContactDetails::dispatch($contact)->onQueue('Contacts');
    }

    public function create(Request $request)
    {
        // dd($request->all());
        // if (!auth()->user()->godspeedoffers_api) {
        //     return redirect()->route('admin.index')
        //         ->with('error', 'Add a working godspeedoffers key first.');
        // }
        $organisationId = auth()->user()->organisation_id;
        $CRMAPIRequestsService = new CRMAPIRequestsService(auth()->user()->godspeedoffers_api);
        // $contactGroups = $CRMAPIRequestsService->get_contact_groups();
        // if (!isset($contactGroups['data'])) {
        //     return redirect()->route('admin.index')
        //         ->with('error', 'Add a working godspeedoffers key first.');
        // }
        $contact_groups = $CRMAPIRequestsService->get_contact_groups()['data'];
        $voices = $this->getVoices();

        $query = Folder::query();
        if ($request->search_folder) {
            $query->where('name', 'like', '%' . $request->search_folder . '%');
        }
        $folders = $query->where('organisation_id', $organisationId)->get();

        $calling_numbers = Number::where('purpose', 'calling')
            ->where('organisation_id', $organisationId)
            ->get();
        $texting_numbers = Number::where('purpose', 'texting')
            ->where('organisation_id', $organisationId)
            ->get();
        $number_pools = NumberPool::where('organisation_id', $organisationId)
            ->get();
        $current_org = Organisation::where('id', auth()->user()->organisation_id)->first();
        $query = Workflow::query();
        $query->where('organisation_id', $organisationId);
        $query->whereNull('folder_id');

        if ($request->search_name) {
            $query->where('name', 'like', '%' . $request->search_name . '%');
        }
        $query->orderBy('created_at', 'desc');

        $workflows = $query->paginate(10);

        if ($request->has('search_name')) {
            $workflows->appends(['search_name' => $request->search_name]);
        }

        return inertia("Workflows/Create", [
            'success' => session('success'),
            'contactGroups' => $contact_groups,
            'workflows' => $workflows,
            'voices' => $voices,
            'calling_numbers' => $calling_numbers,
            'texting_numbers' => $texting_numbers,
            'folders' => $folders,
            'organisation' => $current_org,
            'numberPools' => $number_pools,
        ]);
    }

    public function destroy($id)
    {
        $workflow = Workflow::findorfail($id);
        $step_flow_array = array();
        Contact::where('workflow_id', $workflow->id)->delete();
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            foreach ($steps_flow_array as $step_flow_array) {
                $step = Step::find($step_flow_array);
                $step->delete();
            }
        }
        $workflow->delete();
        return redirect()->route('create-workflow')
            ->with('success', 'Workflow deleted successfully.');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'voice' => 'nullable|string|max:255',
            'agent_number' => 'nullable|string|max:255',
            'calling_number' => 'nullable|string|max:255',
            'texting_number' => 'nullable|string|max:255',
            'number_pool_id' => 'nullable|max:255',
            'generated_message' => 'nullable|max:255'
        ]);
        $workflow = Workflow::findOrFail($id);

        $workflow->update([
            'name' => $validatedData['name'],
            'voice' => $validatedData['voice'] ?? $workflow->voice,
            'agent_number' => $validatedData['agent_number'] ?? $workflow->agent_number,
            'calling_number' => $validatedData['calling_number'] ?? $workflow->calling_number,
            'texting_number' => $validatedData['texting_number'] ?? $workflow->texting_number,
            'number_pool_id' => $validatedData['number_pool_id'] ?? $workflow->number_pool_id,
            'generated_message' => $validatedData['generated_message'] ?? $workflow->generated_message
        ]);
        return response()->json([
            'message' => 'Workflow updated successfully',
            'workflow' => $workflow
        ]);
    }

    public function redirect_twilio_Call(Request $request)
    {
        $workflow = Workflow::where('texting_number', $request->input('To'))->first();
        $this->send_customer_data($request->input('To'), $request->input('From'), $workflow->godspeedoffers_api);
        $called_number = ltrim($request->input('To'), '+');
        $calling_number = ltrim($request->input('From'), '+');

        $contact = Contact::firstWhere('phone', ltrim($calling_number, '+'));
        $call_sent = CallsSent::firstWhere('phone', $calling_number);
        if ($call_sent) {
            $call_sent->response = "Yes";
            $call_sent->save();
        }
        if ($contact) {
            $contact->response = 'yes';
            $contact->save();
        }

        $numberToDial = $workflow->agent_number;
        $response = new VoiceResponse();
        $response->dial($numberToDial);
        return response($response)->header('Content-Type', 'text/xml');
    }

    public function redirect_signalwire_Call(Request $request)
    {
        $workflow = Workflow::where('texting_number', $request->input('To'))->first();
        $this->send_customer_data($request->input('To'), $request->input('From'), $workflow->godspeedoffers_api);
        $called_number = ltrim($request->input('To'), '+');
        $calling_number = ltrim($request->input('From'), '+');
        $contact = Contact::firstWhere('phone', $calling_number);
        $call_sent = CallsSent::firstWhere('phone', $calling_number);
        if ($call_sent) {
            $call_sent->response = "Yes";
            $call_sent->save();
        }
        if ($contact) {
            $contact->response = 'Yes';
            $contact->save();
        }
        if (!$workflow) {
            return response('Workflow not found', 404);
        }
        $numberToDial = $workflow->agent_number;
        $response = new VoiceResponse();
        $response->dial($numberToDial);
        return response($response)->header('Content-Type', 'text/xml');
    }

    private function send_customer_data($to, $from, $token)
    {
        $data = [
            'to' => $to,
            'from' => $from,
        ];
        $response = Http::withToken($token)->post('https://godspeedoffers.com/api/v3/sms/create-chatbox-entry', $data);
        if ($response->successful()) {
            return response()->json([
                'status' => 'success',
                'data' => $response->json()
            ], 200);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send the request',
            'error' => $response->body()
        ], $response->status());
    }

    public function copy(Request $request)
    {
        try {
            $organisation_id = auth()->user()->organisation_id;
            $validatedData = $request->validate([
                'workflow_name' => 'required|string|max:255',
                'id' => 'integer',
                'contact_group' => 'required|string|max:255'
            ]);
            $old_workflow = Workflow::find($validatedData['id']);
            if (!$old_workflow) {
                Log::error("Workflow with ID {$validatedData['id']} not found.");
                return redirect()->route('create-workflow')->withErrors('Workflow not found.');
            }
            $CRMAPIRequestsService = new CRMAPIRequestsService(auth()->user()->godspeedoffers_api);
            $group_name = $CRMAPIRequestsService->get_group_name($request->contact_group);
            $contacts = $CRMAPIRequestsService->get_all_contacts($request->contact_group);
            $new_workflow = Workflow::create([
                'name' => $request->workflow_name,
                'contact_group' => $group_name,
                'active' => 0,
                'group_id' => $request->contact_group,
                'voice' => $old_workflow->voice,
                'agent_number' => $old_workflow->agent_number,
                'texting_number' => $old_workflow->texting_number,
                'calling_number' => $old_workflow->calling_number,
                'number_pool_id' => $old_workflow->number_pool_id,
                'folder_id' => $old_workflow->folder_id,
                'organisation_id' => $organisation_id,
                'godspeedoffers_api' => $old_workflow->godspeedoffers_api,
                'generated_message' => $old_workflow->generated_message,
                'user_id' => auth()->user()->id
            ]);

            foreach ($contacts as $contact) {
                try {
                    CreateWorkflowContactsJob::dispatch($contact['uid'], $request->contact_group, $new_workflow->id, $contact['phone'], $organisation_id)
                        ->delay(now()->addSeconds(30));
                } catch (\Exception $e) {
                    Log::error("Error dispatching contact job for UID {$contact['uid']}: {$e->getMessage()}");
                }
            }
            if (!empty($old_workflow->steps_flow)) {
                $steps_flow_array = explode(',', $old_workflow->steps_flow);
                foreach ($steps_flow_array as $step_id) {
                    try {
                        $step_to_copy = Step::findOrFail($step_id);
                        $new_step = Step::create([
                            'workflow_id' => $new_workflow->id,
                            'type' => $step_to_copy->type,
                            'content' => $step_to_copy->content,
                            'delay' => $step_to_copy->delay,
                            'name' => $step_to_copy->name,
                            'custom_sending' => $step_to_copy->custom_sending,
                            'start_time' => $step_to_copy->start_time,
                            'end_time' => $step_to_copy->end_time,
                            'batch_size' => $step_to_copy->batch_size,
                            'batch_delay' => $step_to_copy->batch_delay,
                            'step_quota_balance' => $step_to_copy->step_quota_balance,
                            'days_of_week' => $step_to_copy->days_of_week,
                            'generated_message' => $step_to_copy->generated_message,
                            'email_subject' => $step_to_copy->email_subject

                        ]);
                        $new_steps_flow = $new_workflow->steps_flow ? explode(',', $new_workflow->steps_flow) : [];
                        $new_steps_flow[] = $new_step->id;
                        $new_workflow->steps_flow = implode(',', $new_steps_flow);
                        $new_workflow->save();
                    } catch (\Exception $e) {
                        Log::error("Error copying step ID {$step_id}: {$e->getMessage()}");
                    }
                }
            }
            return redirect()->route('add_steps', ['workflow' => $new_workflow->id])
                ->with('success', 'Workflow created successfulyy.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Validation error: " . $e->getMessage());
            return redirect()->back()->withErrors($e->errors());
        } catch (\Exception $e) {
            Log::error("An unexpected error occurred: " . $e->getMessage());
            return redirect()->route('create-workflow')->withErrors('An unexpected error occurred while copying the workflow.');
        }
    }

    public function add_steps(Workflow $workflow)
    {
        $DynamicTagsService = new DynamicTagsService($workflow->godspeedoffers_api);
        $place_holders = $DynamicTagsService->get_placeholders($workflow->group_id);
        $spintaxes = Spintax::all();
        $voices = $this->getVoices();
        $organisationId = auth()->user()->organisation_id;
        $calling_numbers = Number::where('purpose', 'calling')
            ->where('organisation_id', $organisationId)
            ->get();
        $texting_numbers = Number::where('purpose', 'texting')
            ->where('organisation_id', $organisationId)
            ->get();
        $number_pools = NumberPool::where('organisation_id', $organisationId)
            ->get();
        $steps = array();
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            foreach ($steps_flow_array as $step_flow_array) {
                array_push($steps, Step::findorfail($step_flow_array));
            }
        }
        $referer = url()->previous(); // Gets full previous URL
        return inertia("Workflows/AddSteps", [
            'success' => session('success'),
            'workflow' => $workflow,
            'steps' => $steps,
            'placeholders' => $place_holders,
            'spintaxes' => $spintaxes,
            'voices' => $voices,
            'calling_numbers' => $calling_numbers,
            'texting_numbers' => $texting_numbers,
            'numberPools' => $number_pools,
            'refererr' => $referer,
        ]);
    }

    public function delete_multiple_workflows(Request $request)
    {
        $workflowIds = $request->input('ids');
        if (empty($workflowIds)) {
            return redirect()->route('create-workflow')
                ->with('error', 'No workflows selected for deletion.');
        }
        foreach ($workflowIds as $id) {
            $workflow = Workflow::find($id);
            if ($workflow) {
                $workflow->delete();
                Log::info("Workflow with ID {$id} deleted successfully.");
            } else {
                Log::warning("Workflow with ID {$id} not found for deletion.");
            }
        }
        return redirect()->route('create-workflow')
            ->with('success', 'Selected workflows deleted successfully.');
    }

    private function getVoices()
    {
        $url = "https://api.elevenlabs.io/v1/voices";
        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $voices = $response->json()['voices'];
                $filteredVoices = array_map(function ($voice) {
                    return [
                        'name' => $voice['name'],
                        'gender' => $voice['labels']['gender'],
                        'preview_url' => $voice['preview_url'],
                        'voice_id' => $voice['voice_id']
                    ];
                }, $voices);
                return $filteredVoices;
            } else {
                return ['error' => 'Request failed with status: ' . $response->status()];
            }
        } catch (\Exception $e) {
            return ['error' => 'Request failed with error: ' . $e->getMessage()];
        }
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
}
