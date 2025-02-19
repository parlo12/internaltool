<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactResource;
use App\Jobs\CalculateCostJob;
use App\Jobs\PrepareMessageJob;
use App\Jobs\QueaueMessagesJob;
use App\Models\CallsSent;
use App\Models\ClosedDeal;
use App\Models\Contact;
use App\Models\executedContracts;
use App\Models\offers;
use App\Models\Organisation;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use App\Models\Step;
use App\Models\TextSent;
use App\Models\ValidLead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client as TwilioClient;
use SignalWire\Rest\Client as SignalWireClient;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContactsExport;
use App\Models\Number;
use App\Models\SendingServer;
use App\Services\SMSService;
use App\Services\MMSService;
use App\Services\OfferService;
use App\Services\CallService;
use App\Services\EmailService;

class ContactController extends Controller
{
    public function index($id)
    {
        $query = Contact::where('workflow_id', $id);
        $sortField = request("sort_field", 'created_at');
        $sortDirection = request("sort_direction", "asc");
        if (request("phone")) {
            $query->where("phone", "like", "%" . request("phone") . "%");
        }
        if (request("contact_name")) {
            $query->where("contact_name", "like", "%" . request("contact_name") . "%");
        }
        if (request("queaue_status")) {
            $query->where("status", "like", "%" . request("queaue_status") . "%");
        }
        if (request("response")) {
            $query->where("response", "like", "%" . request("response") . "%");
        }
        $contacts = $query->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->onEachSide(1);
        $statuses = Contact::select('status')->distinct()->pluck('status')->toArray();
        return inertia("Contacts/Index", [
            'success' => session('success'),
            'error' => session('error'),
            'queryParams' => request()->query() ?: null,
            'contacts' => ContactResource::collection($contacts),
            'workflow' => Workflow::find($id),
            'statuses' => $statuses
        ]);
    }
    public function export($id)
    {
        $workflow = Workflow::find($id);
        if ($workflow === null) {
            return redirect()->back()->with('error', 'Workflow does not exist.');
        }
        if ($workflow != null) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            $last_step = end($steps_flow_array); // Get the last step
            // Fetch contacts in the last step with 'No' response
            $contacts = Contact::where('workflow_id', $id)
                ->where('response', 'No')
                ->where('current_step', $last_step) // Ensure contact is in the last step
                ->select('id', 'phone', 'contact_name', 'status', 'response', 'zipcode', 'city', 'state', 'created_at', 'updated_at') // Specify the columns to include
                ->get();
            // Check if there are any contacts
            if ($contacts->isEmpty()) {
                return redirect()->back()->with('error', 'No contact is in the last step.');
            }
            return Excel::download(new ContactsExport($id, $last_step), 'contacts.xlsx');
        }

        return redirect()->back()->with('error', 'Workflow is not active.');
    }

    public function start_workflow($workflow_id)
    {
        $workflow = Workflow::findorfail($workflow_id);
        $workflow->active = 1;
        $workflow->save();
        if (!empty($workflow->steps_flow)) {
            $steps_flow_array = explode(',', $workflow->steps_flow);
            $first_step = $steps_flow_array[0];
            $contacts = $workflow->contacts;
            $step = Step::find($first_step);
            $currentDateTime = Carbon::now();
            $newDateTime = $currentDateTime->addSeconds((int)$step->delay * 60);
            foreach ($contacts as $contact) {
                if (empty($contact->current_step)) {
                    $contact->current_step = $first_step;
                    $contact->can_send_after = $newDateTime->toDateTimeString();
                    $contact->save();
                }
            }
        }
        return response()->json([
            'success' => session('success'),
            'workflow' => $workflow,
        ], 200);
    }
    public function pause_workflow($workflow_id)
    {
        $workflow = Workflow::findorfail($workflow_id);
        $workflow->active = 0;
        $workflow->save();
        return response()->json([
            'success' => session('success'),
            'workflow' => $workflow,
        ], 200);
    }
    //Some contacts might not have loaded completely when starting a workflow, 
    //here we check for such contacts and assign them to the first step of the workflow
    public function process_workflows()
    {
        ini_set('memory_limit', '300M');
        $contacts = Contact::where('current_step', null)->get(); // Retrieve all contacts
        $now = Carbon::now();
        foreach ($contacts as $contact) {
            // Initialize current step if it's not set
            $workflow = Workflow::find($contact->workflow_id);
            if (empty($workflow && $contact->current_step)) {
                // Log::info("first_step is empty for $contact->id");
                if ($workflow && $workflow->active) {
                    $steps_flow_array = explode(',', $workflow->steps_flow);
                    $first_step = $steps_flow_array[0] ?? null;

                    if ($first_step) {
                        $step = Step::find($first_step);
                        if ($step) {
                            $contact->update([
                                'current_step' => $first_step,
                                'can_send_after' => $now->copy()->addSeconds($step->delay * 60)->toDateTimeString(),
                            ]);
                        }
                    }
                }
            }
        }
    }

    //here get all contacts whose can_send=1
    //qeuaue them for send by passing contact info, content, messagetype,step
    //if contact is qeuaued successfully set can_send=0
    public function queaue_messages_from_workflows()
    { //Keep the commented code for the purpose of local testing. 
        //Uncomment when testing workflows offline
        //production uses a copy in routes/console.php so comment when pushing to prod
        // ini_set('max_execution_time', 0);
        // ini_set('memory_limit', '256M');
        // $steps = Step::where('created_at', '>=', now()->subMonth())->get();
        // foreach ($steps as $step) {
        //     $workflow = Workflow::find($step->workflow_id);
        //     $days_of_week = json_decode($step->days_of_week, true);

        //     if ($workflow != null && $workflow->active) {
        //         $contacts = DB::table('contacts')
        //             ->where('response', 'No')
        //             ->where('can_send', 1)
        //             ->where('subscribed', 1)
        //             ->where('current_step', $step->id)
        //             ->get();
        //         $start_time = $step->start_time ?: '08:00';
        //         $end_time = $step->end_time ?: '20:00';
        //         $chunk_size = $step->batch_size ?: '20';
        //         $interval = (int) $step->batch_delay * 60;
        //         $contactsChunks = $contacts->chunk($chunk_size);
        //         $now = Carbon::now();
        //         $startTime = Carbon::today()->setTimeFromTimeString($start_time);
        //         $endTime = Carbon::today()->setTimeFromTimeString($end_time);
        //         if ($now->between($startTime, $endTime)) {
        //             $startTime = $now;
        //         } elseif ($now->isAfter($endTime)) {
        //             $startTime = Carbon::tomorrow()->setTimeFromTimeString($start_time);
        //             $endTime = Carbon::tomorrow()->setTimeFromTimeString($end_time);
        //         }
        //         while (($days_of_week[$startTime->format('l')] ?? 0) == 0) {
        //             $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
        //             $endTime = $endTime->addDay();
        //         }
        //         foreach ($contactsChunks as $chunk) {
        //             if ($startTime->greaterThanOrEqualTo($endTime)) {
        //                 do {
        //                     $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
        //                     $endTime = $endTime->addDay();
        //                 } while (($days_of_week[$startTime->format('l')] ?? 0) == 0);
        //             }

        //             $dispatchTime = $startTime->copy();
        //             Log::info("here");
        //             foreach ($chunk as $contact) {
        //                 Log::info("Got $contact->id of workflow $contact->workflow_id");
        //                 // Dispatch a job to prepare the message without making third-party requests here
        //                 PrepareMessageJob::dispatch(
        //                     $contact->uuid,
        //                     $workflow->group_id,
        //                     $workflow->godspeedoffers_api,
        //                     $step,
        //                     $contact,
        //                     $dispatchTime
        //                 );
        //                 $contact = Contact::find($contact->id);
        //                 $contact->can_send = 0;
        //                 $contact->status = 'Waiting_For_Queau_Job';
        //                 $contact->save();
        //             }
        //             $startTime->addSeconds($interval);
        //             while (($days_of_week[$startTime->format('l')] ?? 0) == 0) {
        //                 $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
        //                 $endTime = $endTime->addDay();
        //             }
        //         }
        //     }
        // }
    }

    public function send_message($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $workflow = Workflow::find($workflow_id);
        if ($workflow->active) {
            $contact = Contact::find($contact_id);
            $contact->status = "$type SENT";
            $contact->save();
            Log::info("I have sent this $content to this number: $phone that belongs to workflow with id $workflow_id and is of type: $type ");
            switch ($type) {
                case 'SMS':
                    $this->send_SMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                    break;
                case 'Voicemail':
                    $this->send_Voicemail($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                    break;
                case 'VoiceCall':
                    $this->send_VoiceCall($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                    break;
                case 'VoiceMMS':
                    $this->send_VoiceMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                    break;
                case 'Offer':
                    $this->send_Offer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                    break;
                case 'Email':
                    $this->send_Email($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                    break;
            }
        } else { //if workflow is paused reschedule to the same time the following day
            $dispatchTime = Carbon::now()->addDay();
            QueaueMessagesJob::dispatch(
                '+' . $phone,
                $content,
                $workflow_id,
                $type,
                $contact_id,
                $organisation_id
            )->delay($dispatchTime);
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $dispatchTime);
            $contactModel = Contact::find($contact_id);
            $step_delay = (int)Step::find($contactModel->current_step)->delay;
            $next_step_after = $date->addSeconds($step_delay * 60);
            $contactModel->can_send_after = $next_step_after;
            $contactModel->status = "QUEUED";
            $contactModel->can_send = 0;
            $contactModel->save();
            //Log::info('Rescheduled contact: ' . $contactModel->id . ' at ' . $dispatchTime);
        }
    }

    public function mark_lead($contact_id)
    {
        $contact = Contact::find($contact_id);
        if ($contact->valid_lead == 1) {
            $contact->valid_lead = 0;
            $validLead = ValidLead::where('contact_id', $contact->id)->first();
            if ($validLead) {
                $validLead->delete();
            }
            $contact->save();

            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name Unmarked as a valid lead");
        } else {
            $workflow = Workflow::find($contact->workflow_id);
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;
            $contact->valid_lead = 1;
            $contact->save();
            $valid_deal = ValidLead::create([
                'name' => $contact->contact_name,
                'contact_id' => $contact->id,
                'organisation_id' => $contact->organisation_id,
                'zipcode' => $zipcode,
                'city' => $city,
                'state' => $state,
                'user_id' => $contact->user_id
            ]);

            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name marked as a valid lead");
        }
    }
    public function mark_offer($contact_id)
    {
        $contact = Contact::find($contact_id);
        if ($contact->offer_made == 1) {
            $contact->offer_made = 0;
            $offer = Offers::where('contact_id', $contact->id)->first();
            if ($offer) {
                $offer->delete();
            }
            $contact->save();
            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name Unmarked as an offer");
        } else {
            $workflow = Workflow::find($contact->workflow_id);
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;
            $contact->offer_made = 1;
            $contact->save();
            $offer = Offers::create([
                'name' => $contact->contact_name,
                'contact_id' => $contact->id,
                'organisation_id' => $contact->organisation_id,
                'zipcode' => $zipcode,
                'city' => $city,
                'state' => $state,
                'user_id' => $contact->user_id,
            ]);
            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name marked as an offer");
        }
    }

    public function execute_contract($contact_id)
    {
        $contact = Contact::find($contact_id);

        if ($contact->contract_executed == 1) {
            $contact->contract_executed = 0;
            $contract_executed = ExecutedContracts::where('contact_id', $contact->id)->first();
            if ($contract_executed) {
                $contract_executed->delete();
            }
            $contact->save();
            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name Unmarked as an executed contract");
        } else {
            $workflow = Workflow::find($contact->workflow_id);
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;
            $contact->contract_executed = 1;
            $contact->save();
            $executedContracts = ExecutedContracts::create([
                'name' => $contact->contact_name,
                'contact_id' => $contact->id,
                'organisation_id' => $contact->organisation_id,
                'zipcode' => $zipcode,
                'city' => $city,
                'state' => $state,
                'user_id' => $contact->user_id,
            ]);
            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name marked as an executed contract");
        }
    }


    public function close_deal($contact_id)
    {
        $contact = Contact::find($contact_id);
        if ($contact->deal_closed == 1) {
            $contact->deal_closed = 0;
            $deal_closed = ClosedDeal::where('contact_id', $contact->id)->first();
            if ($deal_closed) {
                $deal_closed->delete();
            }
            $contact->save();
            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name Unmarked as a closed deal");
        } else {
            $workflow = Workflow::find($contact->workflow_id);
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;
            $contact->deal_closed = 1;
            $contact->save();
            $deal_closed = ClosedDeal::create([
                'name' => $contact->contact_name,
                'contact_id' => $contact->id,
                'organisation_id' => $contact->organisation_id,
                'zipcode' => $zipcode,
                'city' => $city,
                'state' => $state,
                'user_id' => $contact->user_id,
            ]);
            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name marked as a closed deal");
        }
    }
    private function get_contact($contact_uid, $group_id, $godspeedoffers_api)
    {
        $url = "https://www.godspeedoffers.com/api/v3/contacts/{$group_id}/search/{$contact_uid}";
        $token = $godspeedoffers_api;
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

    private function send_SMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $texting_number = $workflow->texting_number;
        $texting_number = Number::where('phone_number', $texting_number)
        ->where('organisation_id', $organisation_id)
        ->first();
        Log::info($texting_number);
        $sending_server = SendingServer::find($texting_number->sending_server_id);
        if ($sending_server) { //if the number is attached to a sending server
            Log::info("The workflow texting number $texting_number->phone_number is attached to a sending server");
            Log::info($sending_server);
            if ($sending_server->service_provider == 'twilio') {
                $SMSService = new SMSService('twilio');
                $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            } elseif ($sending_server->service_provider == 'websockets-api') {
                $SMSService = new SMSService('websockets-api');
                $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            } else {
                $SMSService = new SMSService('signalwire');
                $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            }
        } else {
            Log::info("Org texting with $organisation->texting_service");
            if ($organisation->texting_service == 'twilio') {
                $SMSService = new SMSService('twilio');
                $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            } elseif ($organisation->texting_service == 'websockets-api') {
                $SMSService = new SMSService('websockets-api');
                $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            } else {
                $SMSService = new SMSService('signalwire');
                $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            }
        }
    }
    private function send_VoiceMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $texting_number = $workflow->texting_number;
        $texting_number = Number::where('phone_number', $texting_number)->first();
        $sending_server = SendingServer::find($texting_number->sending_server_id);
        if ($sending_server) {
            if ($sending_server->service_provider == 'twilio') {
                $MMSService = new MMSService('twilio');
                $MMSService->sendMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            } else {
                $MMSService = new MMSService('signalwire');
                $MMSService->sendMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            }
        } else {
            if ($organisation->texting_service == 'twilio') {
                $MMSService = new MMSService('twilio');
                $MMSService->sendMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            } else {
                $MMSService = new MMSService('signalwire');
                $MMSService->sendMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            }
        }
    }
    private function send_Offer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        Log::info("I  Reached Offer sending function");
        $contact = Contact::find($contact_id);
        if (!$contact || !$contact->offer) {
            $contact->status = "No Offer Amount";
            $contact->save();
            Log::error("Contact with ID $contact_id has no offer field  or missing address.");
            return;
        }
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $texting_number = $workflow->texting_number;
        $texting_number = Number::where('phone_number', $texting_number)->first();
        $sending_server = SendingServer::find($texting_number->sending_server_id);
        if ($sending_server) {
            if ($sending_server->service_provider == 'twilio') {
                $OfferService = new OfferService('twilio');
                $OfferService->sendOffer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            } else {
                $OfferService = new OfferService('signalwire');
                $OfferService->sendOffer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            }
        }else{
            if ($organisation->texting_service == 'twilio') {
                $OfferService = new OfferService('twilio');
                $OfferService->sendOffer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            } else {
                $OfferService = new OfferService('signalwire');
                $OfferService->sendOffer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
            }
        }
        
    }
    private function send_Voicemail($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $calling_number = $workflow->calling_number;
        $calling_number = Number::where('phone_number', $calling_number)->first();
        $sending_server = SendingServer::find($calling_number->sending_server_id);
        if ($sending_server) {
            if ($sending_server->service_provider == 'signalwire') {
                $CallService = new CallService('signalwire');
                $CallService->sendCall($phone, $content, $workflow_id, '20', $contact_id, $organisation_id);
            } else {
            }
        }else{
            if ($organisation->calling_service == 'signalwire') {
                $CallService = new CallService('signalwire');
                $CallService->sendCall($phone, $content, $workflow_id, '20', $contact_id, $organisation_id);
            } else {
            }
        }
        
    }
    private function send_VoiceCall($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $calling_number = $workflow->calling_number;
        $calling_number = Number::where('phone_number', $calling_number)->first();
        $sending_server = SendingServer::find($calling_number->sending_server_id);
        if ($sending_server) {
            if ($sending_server->service_provider == 'signalwire') {
                $CallService = new CallService('signalwire'); // Change provider as needed
                $CallService->sendCall($phone, $content, $workflow_id, '3', $contact_id, $organisation_id);
            } else {
                Log::info("Call Provider unsupported");
            }
        }else{
            if ($organisation->calling_service == 'signalwire') {
                $CallService = new CallService('signalwire'); // Change provider as needed
                $CallService->sendCall($phone, $content, $workflow_id, '3', $contact_id, $organisation_id);
            } else {
                Log::info("Call Provider unsupported");
            }
        }
        
    }
    public function handleCall(Request $request) // this can move to their own controller
    {
        $agent_phone_number = $request->input('agent_phone_number');
        Log::info("Reached handleCall");
        Log::info($request->all());
        $amd_status = $request->input('AnsweredBy');
        $contact_id = $request->input('contact_id');
        $voice_recording = $request->input('voice_recording');
        $response = '<?xml version="1.0" encoding="UTF-8"?>';
        $response .= '<Response>';

        if ($amd_status == 'human' || $amd_status == 'unknown') {
            $response .= '<Play>' . htmlspecialchars($voice_recording) . '</Play>';
            $actionUrl = route('transfer-workflow-call') . '?agent_phone_number=' . urlencode($agent_phone_number) . '&contact_id=' . urlencode($contact_id);
            $response .= '<Gather numDigits="1" timeout="15" action="' . htmlspecialchars($actionUrl) . '" method="POST">';
            $response .= '<Play>https://internaltools.godspeedoffers.com/mes_068bb820-1385-4092-9f23-1c07e7b94832_744.mp3</Play>';
            $response .= '</Gather>';
        } else if ($amd_status == 'machine_end_other') {
        } else {
            $response .= '<Play>' . htmlspecialchars($voice_recording) . '</Play>';
        }
        $response .= '</Response>';
        Log::info("handleCall Response: " . $response);
        return response($response, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');
    }
    public function transferCall(Request $request)
    {
        Log::info('Reached transferCall');
        Log::info('Digits received: ' . $request->input('Digits'));
        $agent_phone_number = $request->input('agent_phone_number');
        $contact_id = $request->input('contact_id');
        $digits = $request->input('Digits');
        if ($digits == '1') {
            $contact = Contact::find($contact_id);
            $contact->response = 1;
            $contact->save();
            Log::info('Forwarding call to agent.');
            $response = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            $response .= '<Dial><Number>' . $agent_phone_number . '</Number></Dial>';
            $response .= '</Response>';
        } else {
            Log::info('Hanging up call.');
            $response = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            $response .= '<Hangup/>';
            $response .= '</Response>';
        }
        Log::info("transferCall Response: " . $response);
        return response($response, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');
    }
    public function calculate_cost()
    {
        $text_sents = DB::table('text_sents')
            ->where('cost', null)
            ->get();
        foreach ($text_sents as $text_sent) {
            if ($text_sent->cost == null) {
                CalculateCostJob::dispatch(
                    $text_sent
                );
            }
        }
        $calls_sents =  DB::table('calls_sents')
            ->where('cost', null)
            ->get();
        foreach ($calls_sents as $call_sent) {
            CalculateCostJob::dispatch(
                $call_sent
            );
        }
    }
    public function get_and_record_cost($contact)
    {
        $communication_id = $contact->contact_communication_id;
        $cost = 0;
        $organisation = Organisation::find($contact->organisation_id);

        if (strpos($communication_id, 'SM') === 0 || strpos($communication_id, 'MM') === 0) {
            $client = new TwilioClient($organisation->twilio_texting_account_sid, $organisation->twilio_texting_auth_token);
            try {
                $message = $client->messages($communication_id)->fetch();
                $price = $message->price;
                $currency = $message->priceUnit;
                $cost = $cost + $price;
                $contact = TextSent::find($contact->id);
                $contact->cost = abs($cost);
                $contact->save();
            } catch (\Exception $e) {
                return 'Error: ' . $e->getMessage();
            }
        } elseif ($contact->marketing_channel == "SMS") {
            try {
                $client = new SignalWireClient(
                    $organisation->signalwire_texting_project_id,
                    $organisation->signalwire_texting_api_token,
                    ['signalwireSpaceUrl' => $organisation->signalwire_texting_space_url]
                );
                $message = $client->messages($communication_id)->fetch();
                $price = $message->price;
                $currency = $message->priceUnit;
                $cost = abs($price);
                $contact = TextSent::find($contact->id);
                if ($contact) {
                    $contact->cost = $cost;
                    $contact->save();
                } else {
                    Log::warning("No TextSent record found for contact ID: $contact->id.");
                }
            } catch (\Exception $e) {
                Log::error("Error fetching message cost from SignalWire: " . $e->getMessage());
                return 'Error: ' . $e->getMessage();
            }
        } else {
            $signalWireSpaceUrl = $organisation->signalwire_calling_space_url;
            $projectId = $organisation->signalwire_calling_project_id;
            $authToken = $organisation->signalwire_calling_api_token;
            $client = new SignalWireClient(
                $projectId,
                $authToken,
                ['signalwireSpaceUrl' => $signalWireSpaceUrl]
            );
            try {
                $call = $client->calls($communication_id)->fetch();
                $cost = $call->price;
            } catch (\Exception $e) {
                Log::info("Error $e when fetching call price");
            }
            $contact = CallsSent::find($contact->id);
            $contact->cost = abs($cost);
            $contact->save();
        }
    }

    private function send_Email($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $EmailService = new EmailService(); // Change provider as needed
        $EmailService->sendEmail($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
    }
    public function contact_search(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|string|max:15',
        ]);
        $contact = Contact::where('phone', $request->input('phone_number'))->first();
        if ($contact) {
            return response()->json([
                'status' => 'success',
                'contact' => $contact,
            ]);
        } else {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No contact found with this phone number.',
            ]);
        }
    }
}
