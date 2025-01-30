<?php

namespace App\Http\Controllers;
use App\Jobs\CreateWorkflowContactsJob;
use App\Jobs\FillContactDetails;
use App\Models\CallsSent;
use App\Models\Contact;
use App\Models\Folder;
use App\Models\Number;
use App\Models\Organisation;
use App\Models\Spintax;
use App\Models\Step;
use App\Models\Workflow;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use Twilio\TwiML\VoiceResponse;

class WorkflowController extends Controller
{
    public function store(Request $request)
    {
        $organisationId = auth()->user()->organisation_id;
        if (!$organisationId) {
            return redirect()->route('create-workflow')
                ->with('error', 'You must belong to an organisation.');
        }
        ini_set('max_execution_time', 0);
        $request->validate([
            'name' => 'required|string|max:255',
            'contact_group' => 'required|string|max:255',
            'calling_number' => 'required|string|max:255',
            'texting_number' => 'required|string|max:255'
        ]);
        if (!$this->group_has_contacts($request->contact_group, auth()->user()->godspeedoffers_api)) {
            return redirect()->route('create-workflow')
                ->with('error', 'The group must have atleast one contact. Add from godspeed offers.');
        }
        $group_name = $this->get_group_name($request->contact_group, auth()->user()->godspeedoffers_api);
        $contacts = $this->get_all_contacts($request->contact_group, auth()->user()->godspeedoffers_api);
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
                'organisation_id' => $organisationId,
                'godspeedoffers_api' => auth()->user()->godspeedoffers_api,
                'user_id' => auth()->user()->id,

            ]
        );
        //create bins redirecting the texting and calling numbers to the agent number

        foreach ($contacts as $contact) {
            $organisationId = auth()->user()->organisation_id;
            CreateWorkflowContactsJob::dispatch($contact['uid'], $request->contact_group, $workflow->id, $contact['phone'], $organisationId)
                ->delay(now()->addSeconds(30));
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
        $first_name = $this->get_contact($contact_uid, $contact_group, $workflow->godspeedoffers_api)['custom_fields']['FIRST_NAME'];
        $last_name = $this->get_contact($contact_uid, $contact_group, $workflow->godspeedoffers_api)['custom_fields']['LAST_NAME'];
        $contact=Contact::create(
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
        FillContactDetails::dispatch($contact);
    }
    public function create()
    {
        if (!auth()->user()->godspeedoffers_api) {
            return redirect()->route('admin.index')
                ->with('error', 'Add a working godspeedoffers key first.');
        }
        $organisationId = auth()->user()->organisation_id;
        $contactGroups = $this->get_contact_groups(auth()->user()->godspeedoffers_api);
        if (!isset($contactGroups['data'])) {
            return redirect()->route('admin.index')
                ->with('error', 'Add a working godspeedoffers key first.');
        }
        $contact_groups = $this->get_contact_groups(auth()->user()->godspeedoffers_api)['data'];
        $voices = $this->getVoices();
        $workflows = Workflow::where('organisation_id', $organisationId)->get();
        $folders = Folder::where('organisation_id', $organisationId)->get();
        $calling_numbers = Number::where('purpose', 'calling')
            ->where('organisation_id', $organisationId)
            ->get();
        $texting_numbers = Number::where('purpose', 'texting')
            ->where('organisation_id', $organisationId)
            ->get();
        $current_org = Organisation::where('id', auth()->user()->organisation_id)->first();
        return inertia("Workflows/Create", [
            'success' => session('success'),
            'contactGroups' => $contact_groups,
            'workflows' => $workflows,
            'voices' => $voices,
            'calling_numbers' => $calling_numbers,
            'texting_numbers' => $texting_numbers,
            'folders' => $folders,
            'organisation' => $current_org
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
            'agent_phone_number' => 'nullable|string|max:255',
            'calling_number' => 'nullable|string|max:255',
            'texting_number' => 'nullable|string|max:255',
        ]);
        $workflow = Workflow::findOrFail($id);
        $workflow->update($validatedData);
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
        Log::info("This is the called  number $called_number");
        Log::info("This is the calling  number $calling_number");
        $contact = Contact::firstWhere('phone', ltrim($calling_number, '+'));
        $call_sent = CallsSent::firstWhere('phone', $calling_number);
        if ($call_sent) {
            $call_sent->response = "Yes";
            $call_sent->save();
            Log::info("call sent response for $calling_number set to $contact->response");

        }
        if ($contact) {
            $contact->response = 'yes';
            $contact->save();
            Log::info("response for $calling_number set to $contact->response");
        }
        
        $numberToDial = $workflow->agent_number;
        Log::info("This is the number to dial $numberToDial");
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
        Log::info("This is the called  number $called_number");
        Log::info("This is the calling  number $calling_number");
        $contact = Contact::firstWhere('phone', $calling_number);
        $call_sent = CallsSent::firstWhere('phone', $calling_number);
        if ($call_sent) {
            $call_sent->response = "Yes";
            $call_sent->save();
            Log::info("Call sent response for $calling_number set to $contact->response");

        }
        if ($contact) {
            $contact->response = 'Yes';
            $contact->save();
            Log::info("response for $calling_number set to $contact->response");
        }
        if (!$workflow) {
            return response('Workflow not found', 404);
        }
        $numberToDial = $workflow->agent_number;
        Log::info("This is the number to dial $numberToDial");
        $response = new VoiceResponse();
        $response->dial($numberToDial);
        return response($response)->header('Content-Type', 'text/xml');
    }
    private function send_customer_data($to, $from, $token)
    {
        $data = [
            'to' => $to,
            'from' => $from,
            // Add other necessary fields based on the API requirements
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
    // public function send_message()
    // {
    //     $recipientNumber = '(407) 581-2918'; // The recipient's phone number
    //     $messageBody = 'This means that we can change the format.'; // The message body
    //     $sid = env('TWILIO_ACCOUNT_SID');
    //     $token = env('TWILIO_AUTH_TOKEN');
    //     $twilioNumber = '(866) 530-2257';
    //     $client = new TwilioClient($sid, $token);
    //     try {
    //         $message = $client->messages->create(
    //             $recipientNumber,
    //             [
    //                 'from' => $twilioNumber,
    //                 'body' => $messageBody,
    //             ]
    //         );
    //         return response()->json(['message' => 'Message sent successfully', 'sid' => $message->sid]);
    //     } catch (\Exception $e) {
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }
    public function copy(Request $request)
    {
        try {
            Log::info("Trying to copy workflow");
    
            $organisation_id = auth()->user()->organisation_id;
    
            // Validate request
            $validatedData = $request->validate([
                'workflow_name' => 'required|string|max:255',
                'id' => 'integer',
                'contact_group' => 'required|string|max:255'
            ]);
    
            // Fetch the old workflow
            $old_workflow = Workflow::find($validatedData['id']);
            if (!$old_workflow) {
                Log::error("Workflow with ID {$validatedData['id']} not found.");
                return redirect()->route('create-workflow')->withErrors('Workflow not found.');
            }
    
            // Fetch group name and contacts
            $group_name = $this->get_group_name($request->contact_group, $old_workflow->godspeedoffers_api);
            $contacts = $this->get_all_contacts($request->contact_group, $old_workflow->godspeedoffers_api);
    
            // Create new workflow
            $new_workflow = Workflow::create([
                'name' => $request->workflow_name,
                'contact_group' => $group_name,
                'active' => 0,
                'group_id' => $request->contact_group,
                'voice' => $old_workflow->voice,
                'agent_number' => $old_workflow->agent_number,
                'texting_number' => $old_workflow->texting_number,
                'calling_number' => $old_workflow->calling_number,
                'folder_id' => $old_workflow->folder_id,
                'organisation_id' => $organisation_id,
                'godspeedoffers_api' => $old_workflow->godspeedoffers_api,
                'user_id' => auth()->user()->id
            ]);
    
            Log::info("New workflow created successfully with ID: {$new_workflow->id}");
    
            // Dispatch jobs for contacts
            foreach ($contacts as $contact) {
                try {
                    CreateWorkflowContactsJob::dispatch($contact['uid'], $request->contact_group, $new_workflow->id, $contact['phone'], $organisation_id)
                        ->delay(now()->addSeconds(30));
                } catch (\Exception $e) {
                    Log::error("Error dispatching contact job for UID {$contact['uid']}: {$e->getMessage()}");
                }
            }
    
            // Copy steps if available
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
                            'days_of_week' => $step_to_copy->days_of_week
                        ]);
    
                        $new_steps_flow = $new_workflow->steps_flow ? explode(',', $new_workflow->steps_flow) : [];
                        $new_steps_flow[] = $new_step->id;
                        $new_workflow->steps_flow = implode(',', $new_steps_flow);
                        $new_workflow->save();
    
                        Log::info("Step ID {$step_id} copied to new step ID {$new_step->id}");
                    } catch (\Exception $e) {
                        Log::error("Error copying step ID {$step_id}: {$e->getMessage()}");
                    }
                }
            }
    
            return redirect()->route('create-workflow')
                ->with('success', 'Workflow copied successfully.');
    
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
        $place_holders = $this->get_placeholders($workflow->group_id, $workflow->godspeedoffers_api);
        $spintaxes = Spintax::all();
        $voices = $this->getVoices();
        $calling_numbers = Number::where('purpose', 'calling')->get();
        $texting_numbers = Number::where('purpose', 'texting')->get();
        $steps = array();
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            foreach ($steps_flow_array as $step_flow_array) {
                array_push($steps, Step::findorfail($step_flow_array));
            }
        }

        return inertia("Workflows/AddSteps", [
            'success' => session('success'),
            'workflow' => $workflow,
            'steps' => $steps,
            'placeholders' => $place_holders,
            'spintaxes' => $spintaxes,
            'voices' => $voices,
            'calling_numbers' => $calling_numbers,
            'texting_numbers' => $texting_numbers
        ]);
    }
    private function get_group_name($group_id, $godspeedoffers_apikey)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/show';
        $token = $godspeedoffers_apikey;

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            if ($statusCode == 200) {
                $data = json_decode($body, true);
                if (isset($data['data']['name'])) {
                    return $data['data']['name'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
    private function get_all_contacts($group_id, $godspeedoffers_apikey)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/all';
        $token = $godspeedoffers_apikey;
        $allContacts = [];
        $currentPage = 1;
        $totalPages = 1;
        do {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => [
                    'page' => $currentPage,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if ($data['status'] == 'success') {
                $allContacts = array_merge($allContacts, $data['data']['data']);
                $currentPage++;
                $totalPages = $data['data']['last_page'];
            } else {
                break;
            }
        } while ($currentPage <= $totalPages);
        $contacts = array_map(function ($contact) {
            return [
                'uid' => $contact['uid'],
                'phone' => $contact['phone'],
            ];
        }, $allContacts);
        return $contacts;
    }
    private function get_contact_groups($godspeedoffers_apikey)
    {
        $url = 'https://www.godspeedoffers.com/api/v3/contacts';
        $token = $godspeedoffers_apikey;
        $client = new Client();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
            $body = $response->getBody();
            $data = json_decode($body, true);
            if ($data['status'] == 'success') {
                return $data['data'];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Failed to retrieve contacts'
                ];
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    private function get_contact($contact_uid, $group_id, $godspeedoffers_apikey)
    {
        $url = "https://www.godspeedoffers.com/api/v3/contacts/{$group_id}/search/{$contact_uid}";
        $token = $godspeedoffers_apikey;
        $client = new Client();
        $response = $client->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);
        $data = json_decode($response->getBody(), true);
        if ($data['status'] == 'success') {
            return $data['data'];
        } else {
            throw new \Exception('Failed to retrieve contact');
        }
    }
    private function get_placeholders($group_id, $godspeedoffers_apikey)
    {
        $contact = $this->getFirstContact($group_id, $godspeedoffers_apikey);
        $contact_info = $this->get_contact($contact['uid'], $group_id, $godspeedoffers_apikey);
        $placeholders = $this->create_placeholders($contact_info, $godspeedoffers_apikey);
        $placeholderKeys = array_keys($placeholders);
        return $placeholderKeys;
    }
    private function create_placeholders($contact, $godspeedoffers_apikey)
    {
        $placeholders = [
            '{{phone}}' => $contact['phone'],
        ];
        foreach ($contact['custom_fields'] as $key => $value) {
            $placeholders['{{' . $key . '}}'] = $value;
        }
        return $placeholders;
    }

    private function getFirstContact($group_id, $godspeedoffers_apikey)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/all';
        $token = $godspeedoffers_apikey;
        $currentPage = 1;
        $totalPages = 1;
        do {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => [
                    'page' => $currentPage,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if ($data['status'] == 'success') {
                $contacts = $data['data']['data'];
                if (!empty($contacts)) {
                    $firstContact = [
                        'uid' => $contacts[0]['uid'],
                        'phone' => $contacts[0]['phone'],
                    ];
                    return $firstContact;
                }
                $currentPage++;
                $totalPages = $data['data']['last_page'];
            } else {
                // Handle the error as per your application's requirement
                break;
            }
        } while ($currentPage <= $totalPages);
        return null;
    }

    private function group_has_contacts($group_id, $godspeedoffers_apikey)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/all';
        $token = $godspeedoffers_apikey;
        $currentPage = 1;
        $totalPages = 1;

        do {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => [
                    'page' => $currentPage,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] == 'success') {
                $contacts = $data['data']['data'];
                if (!empty($contacts)) {
                    return true; // The group has contacts
                }
                $currentPage++;
                $totalPages = $data['data']['last_page'];
            } else {
                // Handle the error as per your application's requirement
                break;
            }
        } while ($currentPage <= $totalPages);

        return false; // The group has no contacts
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
}
