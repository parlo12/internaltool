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
use App\Models\NumberPool;
use App\Models\SendingServer;
use App\Services\SMSService;
use App\Services\MMSService;
use App\Services\OfferService;
use App\Services\CallService;
use App\Services\EmailService;
use App\Services\RetellService;
use Illuminate\Support\Facades\Http;

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
        $statuses = Contact::where('workflow_id', $id)->select('status')->distinct()->pluck('status')->toArray();
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
        // Keep the commented code for the purpose of local testing.
        // Log::info("scheduled task running: process_workflows");
        //             ini_set('memory_limit', '300M');
        // $contacts = Contact::where('current_step', null)->get(); // Retrieve all contacts
        // $now = Carbon::now();
        // foreach ($contacts as $contact) {
        //     // Initializ current step if it's not set
        //     $workflow = Workflow::find($contact->workflow_id);
        //     if ($workflow && $workflow->active) {
        //         $steps_flow_array = explode(',', $workflow->steps_flow);
        //         $first_step = $steps_flow_array[0] ?? null;

        //         if ($first_step) {
        //             $step = Step::find($first_step);
        //             if ($step) {
        //                 $contact->update([
        //                     'current_step' => $first_step,
        //                     'can_send_after' => $now->copy()->addSeconds($step->delay * 60)->toDateTimeString(),
        //                 ]);
        //             }
        //         }
        //     }
        // }
    }

    //here get all contacts whose can_send=1
    //qeuaue them for send by passing contact info, content, messagetype,step
    //if contact is qeuaued successfully set can_send=0
    public function queaue_messages_from_workflows()
    { //Keep the commented code for the purpose of local testing. 
        //Uncomment when testing workflows offline
        //production uses a copy in routes/console.php so comment when pushing to prod
        // Log::info("Scheduled Task Running: prepare-messages");
        // ini_set('max_execution_time', 0);
        // ini_set('memory_limit', '256M');
        // $steps = Step::where('created_at', '>=', now()->subWeek())->get();

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
        //         // foreach ($contacts as $contact) {
        //         //     Log::info("Got $contact->id of workflow $contact->workflow_id") ;
        //         // }

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
        //             foreach ($chunk as $contact) {
        //                 $existingJob = DB::table('jobs')
        //                     ->where('payload', 'like', '%PrepareMessageJob%')
        //                     ->where('payload', 'like', "%{$contact->uuid}%")
        //                     ->exists();
        //                 // Dispatch a job to prepare the message without making third-party requests here
        //                 if (!$existingJob) {

        //                     PrepareMessageJob::dispatch(
        //                         $contact->uuid,
        //                         $workflow->group_id,
        //                         $workflow->godspeedoffers_api,
        //                         $step,
        //                         $contact,
        //                         $dispatchTime
        //                     );
        //                     $contact = Contact::find($contact->id);
        //                     $contact->can_send = 0;
        //                     $contact->status = 'Waiting_For_Queau_Job';
        //                     $contact->save();
        //                 } else {
        //                     Log::info("This job exists, skipping");
        //                 }
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
            $contact->status = "$type PENDING";
            $contact->save();
            // Log::info("I have sent this $content to this number: $phone that belongs to workflow with id $workflow_id and is of type: $type ");
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
                case 'AICall':
                    $this->send_AICall($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
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
        if ($workflow->number_pool_id != null) {
            $number = $this->getFirstAvailableNumber($workflow->number_pool_id, 'texting');
            if ($number) {
                $sending_server = SendingServer::find($number->sending_server_id);
                if ($sending_server) {
                    Log::info("Sent with a pool number successfully");
                    $SMSService = new SMSService($sending_server->service_provider);
                    $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
                } else {
                    Log::info("For Pools you must assign number to a sending server");
                    $contactModel = Contact::find($contact_id);
                    $contactModel->status = "ASSIGN TEXTING NUMBER TO POOL";
                    $contactModel->can_send = 0;
                    $contactModel->save();
                }
            } else {
                Log::info('we did not find an available number reqeuing');
                $dispatchTime = Carbon::now()->addMinute();
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
                $contactModel->status = "POOL BUSY REQUEUED";
                $contactModel->can_send = 0;
                $contactModel->save();
            }
        } else { //Workflow is not being handled by a pool
            $texting_number = $workflow->texting_number;
            $number = Number::where('phone_number', $texting_number)
                ->where('organisation_id', $organisation_id)
                ->first();
            $sending_server = SendingServer::find($number->sending_server_id);

            if ($sending_server) { //if the number is attached to a sending server
                Log::info("Senting with $sending_server->service_provider");
                $SMSService = new SMSService($sending_server->service_provider);
                $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
            } else {
                $SMSService = new SMSService($organisation->texting_service);
                $SMSService->sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
            }
        }
    }
    private function send_VoiceMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        if ($workflow->number_pool_id != null) {
            $number = $this->getFirstAvailableNumber($workflow->number_pool_id, 'texting');
            if ($number) {
                $sending_server = SendingServer::find($number->sending_server_id);
                if ($sending_server) {
                    $MMSService = new MMSService($sending_server->service_provider);
                    $MMSService->sendMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
                } else {
                    Log::info("For Pools you must assign number to a sending server");
                    $contactModel = Contact::find($contact_id);
                    $contactModel->status = "ASSIGN TEXTING NUMBER TO POOL";
                    $contactModel->can_send = 0;
                    $contactModel->save();
                }
            } else {
                $dispatchTime = Carbon::now()->addMinute();
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
                $contactModel->status = "POOL BUSY REQUEUED";
                $contactModel->can_send = 0;
                $contactModel->save();
            }
        } else { //mms not being handled by a pool
            $texting_number = $workflow->texting_number;
            $number = Number::where('phone_number', $texting_number)
                ->where('organisation_id', $organisation_id)
                ->first();
            $sending_server = SendingServer::find($number->sending_server_id);
        }
        if ($sending_server) { //if the number is attached to a sending server
            $MMSService = new MMSService($sending_server->service_provider);
            $MMSService->sendMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
        } else {
            $MMSService = new MMSService($organisation->texting_service);
            $MMSService->sendMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
        }
    }
    private function send_Offer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $contact = Contact::find($contact_id);
        if (!$contact || !$contact->offer) {
            $contact->status = "No Offer Amount";
            $contact->save();
            Log::error("Contact with ID $contact_id has no offer field  or missing address.");
            return;
        }
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        if ($workflow->number_pool_id != null) {
            $number = $this->getFirstAvailableNumber($workflow->number_pool_id, 'texting');
            if ($number) {
                $sending_server = SendingServer::find($number->sending_server_id);
                if ($sending_server) {
                    $OfferService = new OfferService($sending_server->service_provider);
                    $OfferService->sendOffer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
                } else {
                    Log::info("For Pools you must assign number to a sending server");
                    $contactModel = Contact::find($contact_id);
                    $contactModel->status = "ASSIGN TEXTING NUMBER TO POOL";
                    $contactModel->can_send = 0;
                    $contactModel->save();
                }
            } else {
                $dispatchTime = Carbon::now()->addMinute();
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
                $contactModel->status = "POOL BUSY REQUEUED";
                $contactModel->can_send = 0;
                $contactModel->save();
            }
        } else {
            $texting_number = $workflow->texting_number;
            $number = Number::where('phone_number', $texting_number)->first();
            $sending_server = SendingServer::find($number->sending_server_id);
            if ($sending_server) {
                $OfferService = new OfferService($sending_server->service_provider);
                $OfferService->sendOffer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
            } else {
                $OfferService = new OfferService($organisation->texting_service);
                $OfferService->sendOffer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $number->phone_number);
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
        } else {
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
        } else {
            if ($organisation->calling_service == 'signalwire') {
                $CallService = new CallService('signalwire'); // Change provider as needed
                $CallService->sendCall($phone, $content, $workflow_id, '3', $contact_id, $organisation_id);
            } else {
                Log::info("Call Provider unsupported");
            }
        }
    }
    private function send_AICall($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $calling_number = $workflow->calling_number;
        $calling_number = Number::where('phone_number', $calling_number)->first();
        $sending_server = SendingServer::find($calling_number->sending_server_id);
        if ($sending_server) {
            if ($sending_server->service_provider == 'retell') {
                $retellService = new RetellService('retell',$sending_server->retell_api); // Change provider as needed
                $retellService->AICall($phone, $content, $workflow_id, '3', $contact_id, $organisation_id);
            } else {
                Log::info("AI Cal with retell only");
            }
        } 
    }
    public function handleCall(Request $request) // this can move to their own controller
    {
        $agent_phone_number = $request->input('agent_phone_number');
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
        return response($response, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');
    }
    public function transferCall(Request $request)
    {

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
    public function test(Request $request)
    {
        // try {
        //     // Find contact (hardcoded for testing)
        //     $contact = Contact::where('phone', '18449062902')->first();

        //     if (!$contact) {
        //         throw new \Exception("Contact not found");
        //     }

        //     // Prepare payload with all possible fields from the example
        //     $payload = [
        //         'agent_id' => 'agent_4db3be0f059314560b06202470',
        //         'from_number' => '+16319190227',
        //         'to_number' => '+' . $contact->phone,
        //         'dynamic_variables' => [
        //             'name' => $contact->contact_name ?? 'N/A',
        //             'zipcode' => $contact->zipcode ?? 'N/A',
        //             'state' => $contact->state ?? 'N/A',
        //             'offer' => $contact->offer ?? 'N/A',
        //             'address' => $contact->address ?? 'N/A',
        //             'gender' => $contact->gender ?? 'N/A',
        //             'lead_score' => $contact->lead_score ?? 'N/A',
        //             'phone' => $contact->phone ?? 'N/A',
        //             'organisation_id' => $contact->organisation_id ?? 'N/A',
        //             'novation' => $contact->novation ?? 'N/A',
        //             'creative_price' => $contact->creative_price ?? 'N/A',
        //             'downpayment' => $contact->downpayment ?? 'N/A',
        //             'monthly' => $contact->monthly ?? 'N/A',
        //         ],
        //         'metadata' => [
        //             'contact_id' => $contact->id,
        //             'call_purpose' => 'follow_up'
        //         ],
        //         'retell_llm_dynamic_variables' => [
        //             'name' => $contact->contact_name ?? 'N/A',
        //             'zipcode' => $contact->zipcode ?? 'N/A',
        //             'state' => $contact->state ?? 'N/A',
        //             'offer' => $contact->offer ?? 'N/A',
        //             'address' => $contact->address ?? 'N/A',
        //             'gender' => $contact->gender ?? 'N/A',
        //             'lead_score' => $contact->lead_score ?? 'N/A',
        //             'phone' => $contact->phone ?? 'N/A',
        //             'organisation_id' => $contact->organisation_id ?? 'N/A',
        //             'novation' => $contact->novation ?? 'N/A',
        //             'creative_price' => $contact->creative_price ?? 'N/A',
        //             'downpayment' => $contact->downpayment ?? 'N/A',
        //             'monthly' => $contact->monthly ?? 'N/A',
        //         ],
        //         'opt_out_sensitive_data_storage' => true
        //     ];

        //     Log::info('Preparing outbound call payload', $payload);

        //     // Initialize cURL with all recommended settings
        //     $ch = curl_init();

        //     curl_setopt_array($ch, [
        //         CURLOPT_URL => 'https://api.retellai.com/v2/create-phone-call',
        //         CURLOPT_RETURNTRANSFER => true,
        //         CURLOPT_ENCODING => '',
        //         CURLOPT_MAXREDIRS => 10,
        //         CURLOPT_TIMEOUT => 30,
        //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        //         CURLOPT_CUSTOMREQUEST => 'POST',
        //         CURLOPT_POSTFIELDS => json_encode($payload),
        //         CURLOPT_HTTPHEADER => [
        //             'Content-Type: application/json',
        //             'Authorization: Bearer ' . env('RETELL_API_KEY'),
        //             'Accept: application/json'
        //         ],
        //     ]);

        //     // Execute request
        //     $response = curl_exec($ch);
        //     $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        //     $error = curl_error($ch);

        //     if ($error) {
        //         throw new \Exception("cURL error: " . $error);
        //     }

        //     $responseData = json_decode($response, true);

        //     // Handle different HTTP status codes
        //     switch ($httpCode) {
        //         case 201: // Success
        //             Log::info('Call initiated successfully', [
        //                 'call_id' => $responseData['call_id'] ?? null,
        //                 'telephony_identifier' => $responseData['telephony_identifier'] ?? null,
        //                 'response' => $responseData
        //             ]);

        //             // Save call details to database if needed
        //             // CallLog::create([
        //             //     'retell_call_id' => $responseData['call_id'],
        //             //     'contact_id' => $contact->id,
        //             //     'status' => $responseData['call_status'] ?? 'initiated',
        //             //     'direction' => 'outbound',
        //             //     'metadata' => $payload['metadata'],
        //             //     'telephony_data' => $responseData['telephony_identifier'] ?? null
        //             // ]);

        //             return response()->json($responseData);

        //         case 400:
        //             throw new \Exception("Bad request: " . ($responseData['message'] ?? 'Invalid parameters'));
        //         case 401:
        //             throw new \Exception("Unauthorized: Check your API key");
        //         case 402:
        //             throw new \Exception("Payment required");
        //         case 422:
        //             throw new \Exception("Validation error: " . ($responseData['errors'] ?? 'Invalid data'));
        //         case 429:
        //             throw new \Exception("Rate limited: " . ($responseData['message'] ?? 'Too many requests'));
        //         case 500:
        //             throw new \Exception("Server error: " . ($responseData['message'] ?? 'Internal server error'));
        //         default:
        //             throw new \Exception("Unexpected response: HTTP $httpCode");
        //     }
        // } catch (\Exception $e) {
        //     Log::error('Outbound call failed', [
        //         'error' => $e->getMessage(),
        //         'trace' => $e->getTraceAsString(),
        //         'payload' => $payload ?? null
        //     ]);

        //     return response()->json([
        //         'error' => $e->getMessage(),
        //         'code' => $httpCode ?? 500
        //     ], $httpCode ?? 500);
        // } finally {
        //     if (isset($ch)) {
        //         curl_close($ch);
        //     }
        // }

        $retellService = new RetellService();
        $agents = $retellService->getAllAgents();
        dd($agents);
    }

    protected function prepareDynamicVariables(Contact $contact): array
    {
        return [
            'customer_name' => $contact->contact_name,
            'account_type' => $contact->account_type ?? 'standard',
            'last_interaction' => $contact->last_contacted_at?->format('Y-m-d') ?? 'never',
            // Add other relevant fields
        ];
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
    private function getFirstAvailableNumber($numberPoolId, $purpose)
    {
        $numberPool = NumberPool::find($numberPoolId);
        if (!$numberPool) {
            return response()->json(['error' => 'Number Pool not found'], 404);
        }

        $numbers = Number::where('number_pool_id', $numberPool->id)
            ->where('purpose', $purpose)
            ->get();

        foreach ($numbers as $number) {
            if ($number->remaining_messages > 0) {
                $remaining_messages =  $number->remaining_messages - 1;
                $number->update(['remaining_messages' => $remaining_messages]);
                return $number;
            }

            if ($number->can_refill_on !== null && now()->greaterThan($number->can_refill_on)) {
                Log::info("$number->phone_number can refill now");
                // Calculate new refill time
                $newCanRefillOn = $this->calculateNewRefillTime(now(), (int)$numberPool->pool_time, $number->pool_time_units);

                // Update number details
                $number->update([
                    'remaining_messages' => $numberPool->pool_messages - 1,
                    'can_refill_on' => $newCanRefillOn
                ]);

                return $number;
            }

            if ($number->can_refill_on === null) {
                Log::info("$number->phone_number does not have an existing refill_on. calculating now");
                $newCanRefillOn = $this->calculateNewRefillTime(now(), (int)$numberPool->pool_time, $number->pool_time_units);

                $number->update([
                    'remaining_messages' => $numberPool->pool_messages - 1,
                    'can_refill_on' => $newCanRefillOn
                ]);

                return $number;
            }
        }

        return false; // No available number found
    }

    /**
     * Calculate new refill time based on pool time and units.
     */
    private function calculateNewRefillTime($currentTime, $poolTime, $poolTimeUnits)
    {
        switch (strtolower($poolTimeUnits)) {
            case 'minutes':
                return $currentTime->addMinutes($poolTime);
            case 'hours':
                return $currentTime->addHours($poolTime);
            case 'days':
                return $currentTime->addDays($poolTime);
            default:
                return $currentTime->addMinutes($poolTime); // Default to minutes
        }
    }
}
