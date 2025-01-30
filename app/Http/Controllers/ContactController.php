<?php

namespace App\Http\Controllers;

use App\Http\Resources\ContactResource;
use App\Jobs\CalculateCostJob;
use App\Jobs\PrepareMessageJob;
use App\Jobs\ResponseCheckJob;
use App\Jobs\QueaueMessagesJob;
use App\Models\CallsSent;
use App\Models\ClosedDeal;
use App\Models\Contact;
use App\Models\executedContracts;
use App\Models\Number;
use App\Models\offers;
use App\Models\Organisation;
use App\Models\Workflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\Models\Report;
use App\Models\Step;
use App\Models\TextSent;
use App\Models\ValidLead;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Queue\Worker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tidy;
use Twilio\Rest\Client as TwilioClient;
use SignalWire\Rest\Client as SignalWireClient;
use ElephantIO\Client as ElephantClient;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Ogg;
use OpenAI;
use function Illuminate\Events\queueable;
use AndroidSmsGateway\Client as AndroidSMSGateway;
use AndroidSmsGateway\Encryptor;
use AndroidSmsGateway\Domain\Message;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContactsExport;
use App\Jobs\FillContactDetails;
use App\Mail\ContactEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Imagick;

class ContactController extends Controller
{
    private function generate_offer_card($address, $expiry, $price, $agent)
    {
        try {
            Log::info("The address is $address");
            Log::info("The expiry is $expiry");
            Log::info("The price is $price");
            Log::info("The agent is $agent");

            // Load the image from the public directory
            $imagePath = public_path('uploads/offer_template.png');

            // Check if the template image exists
            if (!file_exists($imagePath)) {
                Log::error("Offer template image not found: $imagePath");
                return false;
            }

            // Generate a unique file name for the output
            $randomFileName = 'offer_template_' . uniqid() . '.png';
            $outputPath = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads/' . $randomFileName;

            // Check if the output directory is writable
            if (!is_writable(dirname($outputPath))) {
                Log::error("Upload directory is not writable: " . dirname($outputPath));
                return false;
            }

            // Create an Imagick object for the image
            $imagick = new Imagick($imagePath);

            // Get today's date
            $today = Carbon::now()->format('Y-m-d');

            // Format expiry date
            try {
                $valid = Carbon::parse($expiry)->format('m/d'); // Automatically parses various formats
            } catch (\Exception $e) {
                Log::error("Invalid expiry date format: $expiry");
                return false;
            }

            // Create a new ImagickDraw object to draw the text
            $draw = new \ImagickDraw();
            $draw->setFillColor('black');
            $draw->setFontSize(36);

            $draw_on_black = new \ImagickDraw();
            $draw_on_black->setFillColor('white');
            $draw_on_black->setFontSize(36);

            $draw_price = new \ImagickDraw();
            $draw_price->setFillColor('white');
            $draw_price->setFontSize(100);

            // X and Y coordinates for text positions
            $coordinates = [
                ['draw' => $draw, 'x' => 450, 'y' => 87, 'text' => $address],
                ['draw' => $draw, 'x' => 450, 'y' => 233, 'text' => $expiry],
                ['draw' => $draw_on_black, 'x' => 772, 'y' => 588, 'text' => $valid],
                ['draw' => $draw_price, 'x' => 325, 'y' => 850, 'text' => $price],
                ['draw' => $draw, 'x' => 800, 'y' => 1463, 'text' => $today],
                ['draw' => $draw, 'x' => 450, 'y' => 379, 'text' => $price],
                ['draw' => $draw_on_black, 'x' => 272, 'y' => 588, 'text' => $agent],
            ];

            // Annotate the image with text
            foreach ($coordinates as $coordinate) {
                $imagick->annotateImage(
                    $coordinate['draw'],
                    $coordinate['x'],
                    $coordinate['y'],
                    0,
                    $coordinate['text']
                );
            }

            // Try to save the image
            if (!$imagick->writeImage($outputPath)) {
                Log::error("Failed to save offer image to: $outputPath");
                return false;
            }

            // Free resources
            $imagick->clear();
            $imagick->destroy();

            // Return the publicly accessible URL of the generated image
            return 'https://internaltools.godspeedoffers.com/uploads/' . $randomFileName;
        } catch (\Exception $e) {
            Log::error('Error generating offer card: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return false;
        }
    }


    public function test_gen()
    {
        $path = $this->generate_offer_card('Zimmerman Base Road', '11/11/2024', '$200000', "Al Adams");
        echo $path;
    }
    public function test()
    {
                    $client = ElephantClient::create('https://coral-app-cazak.ondigitalocean.app/?apiKey=692c2be16f7cb78700c969da90002582');
                    $client->connect();
                    Log::info('Connected to Websocket API');
                            $client->emit('outgoingSMS',  [
                                'deviceId' => '8379adda7f41172d',
                                'receiver' =>'+18449062902',
                                'content' => 'hello from workflow tool',
                            ]);
                    echo "success";  
    }


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
        // Check if the workflow exists
        if ($workflow === null) {
            return redirect()->back()->with('error', 'Workflow does not exist.');
        }

        // Check if the workflow is active
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

            // Proceed to export contacts if there are qualifying contacts
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
    //here we check if a contact is due for scheduling, we schedule
    //check if the delay for moving the contact to the next step has expired
    //if delay has expired check if there was a response
    //if there was a response mark can_send=0 at this point response must be 1
    //if there was no response update the current_step to the next step in the workflow. if no more steps do not update.
    //can_send should be set to 1.
    //next_step_on should be the date we can move to the next step. calculated from the delay for this step
    public function process_workflows1()
    {
        ini_set('memory_limit', '256M');
        //$this->fill_reponses();
        // Log::info("I tried to process workflows");
        $contacts = Contact::all();
        foreach ($contacts as $contact) {
            if (empty($contact->current_step)) {
                $workflow = Workflow::find($contact->workflow_id);
                if ($workflow != null && $workflow->active) {
                    $steps_flow_array = explode(',', $workflow->steps_flow);
                    $first_step = $steps_flow_array[0];
                    $step = Step::find($first_step);
                    $currentDateTime = Carbon::now();
                    $newDateTime = $currentDateTime->addSeconds((int)$step->delay * 60);
                    $contact->current_step = $first_step;
                    $contact->can_send_after = $newDateTime->toDateTimeString();
                    $contact->save();
                }
            }
        }

        $contacts_for_processing = [];
        foreach ($contacts as $contact) {
            if (!$contact->can_send) {
                $contacts_for_processing[] = $contact;
            }
        }
        $contacts_with_active_workflow = [];
        foreach ($contacts_for_processing as $contact_for_processing) {
            $workflow = Workflow::find($contact_for_processing->workflow_id);
            if ($workflow != null && $workflow->active) {
                $contacts_with_active_workflow[] = $contact_for_processing;
            }
        }

        $contacts_with_delay_expired = [];
        foreach ($contacts_with_active_workflow as $contact_with_active_workflow) {
            if (Carbon::now() > $contact_with_active_workflow->can_send_after) {
                $contacts_with_delay_expired[] = $contact_with_active_workflow;
            }
        }
        //dd($contacts_with_delay_expired);
        foreach ($contacts_with_delay_expired as $contact_with_delay_expired) {
            if ($contact_with_delay_expired->response == "No") {
                $workflow_id = $contact_with_delay_expired->workflow_id;
                $current_step = $contact_with_delay_expired->current_step;
                $workflow = Workflow::findOrFail($workflow_id);
                if ($workflow->active) {
                    $steps_flow_array = explode(',', $workflow->steps_flow);
                    $key = array_search($current_step, $steps_flow_array);
                    if ($key !== false && $key < count($steps_flow_array) - 1) {
                        $next_step = $steps_flow_array[$key + 1];
                        $step = Step::findOrFail($next_step);
                        $step_delay = (int)$step->delay;
                        $currentDateTime = Carbon::now();
                        $currentDateTime->addSeconds($step_delay * 60);
                        $next_step_after = $currentDateTime->toDateTimeString();
                        $contact = Contact::findOrFail($contact_with_delay_expired->id);
                        $contact->current_step = $next_step;
                        $contact->can_send = 1;
                        $contact->can_send_after = $next_step_after;
                        $contact->status = "WAITING_FOR_QUEUE";
                        $contact->save();
                        //Log::info("contact $contact_with_delay_expired->phone can send now");
                    } elseif ($key === count($steps_flow_array) - 1) {
                        //Log::info("$current_step is the last step.");
                    } else {
                        //Log::info("$current_step is not available.");
                    }
                } else {
                    Log::info("The Workflow Is Paused");
                }
            }
        }
    }
    public function process_workflows()
    {
        ini_set('memory_limit', '300M');

        // Fetch all contacts with their workflows and steps in one query
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

        // Filter contacts ready for processing
        // $contacts_for_processing = $contacts->filter(function ($contact) use ($now) {
        //     return !$contact->can_send 
        //         && $contact->workflow 
        //         && $contact->workflow->active 
        //         && $now > $contact->can_send_after;
        // });

        // foreach ($contacts_for_processing as $contact) {
        //     if ($contact->response === "No") {
        //         $workflow = $contact->workflow;
        //         $steps_flow_array = explode(',', $workflow->steps_flow);
        //         $current_step = $contact->current_step;
        //         $current_step_key = array_search($current_step, $steps_flow_array);

        //         if ($current_step_key !== false && $current_step_key < count($steps_flow_array) - 1) {
        //             // Move to the next step
        //             $next_step = $steps_flow_array[$current_step_key + 1];
        //             $step = Step::find($next_step);

        //             if ($step) {
        //                 $contact->update([
        //                     'current_step' => $next_step,
        //                     'can_send' => 1,
        //                     'can_send_after' => $now->copy()->addSeconds($step->delay * 60)->toDateTimeString(),
        //                     'status' => "WAITING_FOR_QUEUE",
        //                 ]);
        //             }
        //         } elseif ($current_step_key === count($steps_flow_array) - 1) {
        //             Log::info("{$current_step} is the last step for contact {$contact->id}.");
        //         } else {
        //             Log::info("Step {$current_step} is not available in workflow {$workflow->id}.");
        //         }
        //     }
        // }
    }

    //here get all contacts whose can_send=1
    //qeuaue them for send by passing contact info, content, messagetype,step
    //if contact is qeuaued successfully set can_send=0
    public function queaue_messages_from_workflows()
     {
    //         ini_set('max_execution_time', 0);
    //         ini_set('memory_limit', '256M');
    //         // Log::info("I Tried to Queue");
    //         $steps = Step::where('created_at', '>=', now()->subMonth())->get();

    //         foreach ($steps as $step) {
    //             $workflow = Workflow::find($step->workflow_id);
    //             $days_of_week = json_decode($step->days_of_week, true);

    //             if ($workflow != null && $workflow->active) {
    //                 $contacts = DB::table('contacts')
    //                     ->where('response', 'No')
    //                     ->where('can_send', 1)
    //                     ->where('subscribed', 1)
    //                     ->where('current_step', $step->id)
    //                     ->get();
    //                     // foreach ($contacts as $contact) {
    //                     //     Log::info("Got $contact->id of workflow $contact->workflow_id") ;
    //                     // }

    //                 $start_time = $step->start_time ?: '08:00';
    //                 $end_time = $step->end_time ?: '20:00';
    //                 $chunk_size = $step->batch_size?:'20';
    //                 $interval = (int) $step->batch_delay * 60;
    //                 $contactsChunks = $contacts->chunk($chunk_size);

    //                 $now = Carbon::now();
    //                 $startTime = Carbon::today()->setTimeFromTimeString($start_time);
    //                 $endTime = Carbon::today()->setTimeFromTimeString($end_time);

    //                 if ($now->between($startTime, $endTime)) {
    //                     $startTime = $now;
    //                 } elseif ($now->isAfter($endTime)) {
    //                     $startTime = Carbon::tomorrow()->setTimeFromTimeString($start_time);
    //                     $endTime = Carbon::tomorrow()->setTimeFromTimeString($end_time);
    //                 }

    //                 while (($days_of_week[$startTime->format('l')] ?? 0) == 0) {
    //                     $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
    //                     $endTime = $endTime->addDay();
    //                 }

    //                 foreach ($contactsChunks as $chunk) {
    //                     if ($startTime->greaterThanOrEqualTo($endTime)) {
    //                         do {
    //                             $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
    //                             $endTime = $endTime->addDay();
    //                         } while (($days_of_week[$startTime->format('l')] ?? 0) == 0);
    //                     }

    //                     $dispatchTime = $startTime->copy();
    //                     Log::info("here");
    //                     foreach ($chunk as $contact) {
    //                         Log::info("Got $contact->id of workflow $contact->workflow_id") ;

    //                         // Dispatch a job to prepare the message without making third-party requests here
    //                         PrepareMessageJob::dispatch(
    //                             $contact->uuid,
    //                             $workflow->group_id,
    //                             $workflow->godspeedoffers_api,
    //                             $step,
    //                             $contact,
    //                             $dispatchTime
    //                         );
    //                         $contact = Contact::find($contact->id);
    //                         $contact->can_send = 0;
    //                         $contact->status = 'Waiting_For_Queau_Job';
    //                         $contact->save();
    //                     }

    //                     $startTime->addSeconds($interval);

    //                     while (($days_of_week[$startTime->format('l')] ?? 0) == 0) {
    //                         $startTime = $startTime->addDay()->setTimeFromTimeString($start_time);
    //                         $endTime = $endTime->addDay();
    //                     }
    //                 }
    //             }
    //         }
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
        } else {
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
            // Unmark as valid lead
            $contact->valid_lead = 0;
            $validLead = ValidLead::where('contact_id', $contact->id)->first();
            if ($validLead) {
                $validLead->delete();
            }
            $contact->save();

            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name Unmarked as a valid lead");
        } else {
            // Retrieve the contact's information from API
            $workflow = Workflow::find($contact->workflow_id);
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

            // Extract the custom fields for city, state, and zipcode
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;

            // Mark as valid lead and save
            $contact->valid_lead = 1;
            $contact->save();

            // Create a valid lead entry with the relevant fields
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
    public function updateContacts(Request $request)
    {
        // Fetch the workflow for which you want to update contacts
        $workflows = Workflow::all();

        // Fetch all contacts associated with the workflow
        foreach ($workflows as $workflow) {
            # code...
            $contacts = Contact::where('workflow_id', $workflow->id)
                ->where('address', null)
                ->get();

            // Dispatch the job for each contact
            foreach ($contacts as $contact) {
                FillContactDetails::dispatch($contact);
            }
        }
    }
    public function mark_offer($contact_id)
    {
        $contact = Contact::find($contact_id);

        if ($contact->offer_made == 1) {
            // Unmark as offer
            $contact->offer_made = 0;
            $offer = Offers::where('contact_id', $contact->id)->first();
            if ($offer) {
                $offer->delete();
            }
            $contact->save();

            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name Unmarked as an offer");
        } else {
            // Retrieve the contact's information from API
            $workflow = Workflow::find($contact->workflow_id);
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

            // Extract the custom fields for city, state, and zipcode
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;

            // Mark as offer and save
            $contact->offer_made = 1;
            $contact->save();

            // Create an offer entry with the relevant fields
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
            // Unmark as executed contract
            $contact->contract_executed = 0;
            $contract_executed = ExecutedContracts::where('contact_id', $contact->id)->first();
            if ($contract_executed) {
                $contract_executed->delete();
            }
            $contact->save();

            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name Unmarked as an executed contract");
        } else {
            // Retrieve the contact's information from API
            $workflow = Workflow::find($contact->workflow_id);
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

            // Extract the custom fields for city, state, and zipcode
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;

            // Mark as executed contract and save
            $contact->contract_executed = 1;
            $contact->save();

            // Create an executed contract entry with the relevant fields
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
            // Unmark as deal closed
            $contact->deal_closed = 0;
            $deal_closed = ClosedDeal::where('contact_id', $contact->id)->first();
            if ($deal_closed) {
                $deal_closed->delete();
            }
            $contact->save();

            return redirect()->route('contacts.index', $contact->workflow_id)
                ->with('success', "$contact->contact_name Unmarked as a closed deal");
        } else {
            // Retrieve the contact's information from API
            $workflow = Workflow::find($contact->workflow_id);
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

            // Extract the custom fields for city, state, and zipcode
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;

            // Mark as closed deal and save
            $contact->deal_closed = 1;
            $contact->save();

            // Create a closed deal entry with the relevant fields
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

    private function composeMessage($contact, $messageTemplate)
    {
        $message = $this->replacePlaceholders($messageTemplate, $contact);
        return  $message;
    }

    private function replacePlaceholders($template, $contact)
    {
        $placeholders = $this->create_placeholders($contact);
        foreach ($placeholders as $key => $value) {
            $template = str_replace($key, $value, $template);
        }
        // Log::info('Final Template: ' . $template);
        return $template;
    }
    private function create_placeholders($contact)
    {
        $placeholders = [
            '{{phone}}' => $contact['phone'],
        ];
        foreach ($contact['custom_fields'] as $key => $value) {
            $placeholders['{{' . $key . '}}'] = $value;
        }
        return $placeholders;
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
        // Log::info("I  Reached SMS sending function");
        $organisation = Organisation::find($organisation_id);
        Log::info("Org texting with $organisation->texting_service");
        if ($organisation->texting_service == 'twilio') {
            Log::info("Texting service is twilio");
            $workflow = Workflow::find($workflow_id);
            $texting_number = $workflow->texting_number;
            $sid = $organisation->twilio_texting_account_sid;
            $token = $organisation->twilio_texting_auth_token;
            $twilio = new TwilioClient($sid, $token);
            $message = $twilio->messages->create(
                $phone,
                [
                    'from' => $texting_number,
                    'body' => $content
                ]
            );
            $contact = Contact::find($contact_id);
            if ($contact) {
                $communication_ids_array = [];
                $communication_ids = $contact->contact_communication_ids;
                if (!empty($communication_ids)) {
                    $communication_ids_array = explode(',', $communication_ids);
                }
                $message_sid = $message->sid;
                array_push($communication_ids_array, $message_sid);
                $new_contact_communication_ids = implode(',', $communication_ids_array);
                $contact->contact_communication_ids = $new_contact_communication_ids;
                $contact->save();
                Log::info("Message sent with SID: $message_sid");
                $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

                // Extract the custom fields for city, state, and zipcode
                $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
                $city = $contact_info['custom_fields']['CITY'] ?? null;
                $state = $contact_info['custom_fields']['STATE'] ?? null;
                if ($message_sid) {
                    $text_sent = TextSent::create([
                        'name' => $contact->contact_name,
                        'contact_id' => $contact->id,
                        'contact_communication_id' => $message_sid,
                        'organisation_id' => $organisation_id,
                        'marketing_channel' => 'SMS',
                        'sending_number' => $texting_number,
                        'zipcode' => $zipcode,
                        'state' => $state,
                        'city' => $city,
                        'user_id' => $workflow->user_id,
                        'response' => 'No'
                    ]);
                } else {
                    $contact->status = "SMS FAILED";
                    $contact->save();
                }
            } else {
                Log::error("Contact with ID $contact_id not found.");
            }
        }elseif($organisation->texting_service == 'websockets-api'){
            $contact = Contact::find($contact_id);
            if($contact){
                $workflow = Workflow::find($workflow_id);
                $texting_number = $workflow->texting_number;
                $api_url=$organisation->api_url;
                $auth_token=$organisation->auth_token;
                $device_id=$organisation->device_id;
                $client = ElephantClient::create('https://coral-app-cazak.ondigitalocean.app/?apiKey=692c2be16f7cb78700c969da90002582');
                $client->connect();
                Log::info('Connected to Websocket API');
                $client->emit('outgoingSMS', [
                    'deviceId' => $device_id,
                    'receiver' => $phone,
                    'content' => $content,
                ]);
                    //if ($packet = $client->wait(null, 1)) {
                        Log::info("Message sent with websockets to: $phone");
                        $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
        
                        // Extract the custom fields for city, state, and zipcode
                        $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
                        $city = $contact_info['custom_fields']['CITY'] ?? null;
                        $state = $contact_info['custom_fields']['STATE'] ?? null;
                            $text_sent = TextSent::create([
                                'name' => $contact->contact_name,
                                'contact_id' => $contact->id,
                                'contact_communication_id' => 'websockets',
                                'organisation_id' => $organisation_id,
                                'marketing_channel' => 'SMS',
                                'sending_number' => $texting_number,
                                'zipcode' => $zipcode,
                                'state' => $state,
                                'city' => $city,
                                'user_id' => $workflow->user_id,
                                'response' => 'No',
                                'cost'=>0
                            ]);
                    // }else{
                    //     $contact->status = "SMS FAILED";
                    //     $contact->save();
                    // }
            }
          
        }
         else {
            //send with signalwire
            $projectID = $organisation->signalwire_texting_project_id;
            $authToken = $organisation->signalwire_texting_api_token;
            $signalwireSpaceUrl = $organisation->signalwire_texting_space_url; // Example: example.signalwire.com
            $workflow = Workflow::find($workflow_id);
            $texting_number = $workflow->texting_number;
            // Create a new SignalWire Client
            $client = new SignalWireClient($projectID, $authToken, [
                'signalwireSpaceUrl' => $signalwireSpaceUrl
            ]);

            // Send an SMS
            $message = $client->messages->create(
                $phone, // Destination phone number (in E.164 format)
                [
                    'from' => $texting_number, // Your SignalWire phone number (in E.164 format)
                    'body' => $content
                ]
            );
            $contact = Contact::find($contact_id);
            if ($contact) {
                $communication_ids_array = [];
                $communication_ids = $contact->contact_communication_ids;
                if (!empty($communication_ids)) {
                    $communication_ids_array = explode(',', $communication_ids);
                }
                $message_sid = $message->sid;
                array_push($communication_ids_array, $message_sid);
                $new_contact_communication_ids = implode(',', $communication_ids_array);
                $contact->contact_communication_ids = $new_contact_communication_ids;
                $contact->save();
                Log::info("Message sent with SID: $message_sid");
                $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

                // Extract the custom fields for city, state, and zipcode
                $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
                $city = $contact_info['custom_fields']['CITY'] ?? null;
                $state = $contact_info['custom_fields']['STATE'] ?? null;
                if ($message_sid) {
                    $text_sent = TextSent::create([
                        'name' => $contact->contact_name,
                        'contact_id' => $contact->id,
                        'contact_communication_id' => $message_sid,
                        'organisation_id' => $organisation_id,
                        'zipcode' => $zipcode,
                        'state' => $state,
                        'city' => $city,
                        'marketing_channel' => 'SMS',
                        'sending_number' => $texting_number,
                        'user_id' => $workflow->user_id,
                        'response' => 'No'
                    ]);
                } else {
                    $contact->status = "SMS FAILED";
                    $contact->save();
                }
            } else {
                Log::error("Contact with ID $contact_id not found.");
            }
        }
    }
    private function send_VoiceMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        // Log::info("I  Reached SMS sending function");
        $organisation = Organisation::find($organisation_id);
        if ($organisation->texting_service == 'twilio') {
            $workflow = Workflow::find($workflow_id);
            $texting_number = $workflow->texting_number;
            $sid = $organisation->twilio_texting_account_sid;
            $token = $organisation->twilio_texting_auth_token;
            $twilio = new TwilioClient($sid, $token);
            $workflow = Workflow::find($workflow_id);
            $voice = $workflow->voice;
            $userId = 1;
            $messageId = Str::uuid();
            // if (!empty($voice)) {
            //     $path = $this->textToSpeech($content, $userId, $messageId, $voice);
            // } else {
            //     $path = $this->textToSpeech($content, $userId, $messageId, 'knrPHWnBmmDHMoiMeP3l');
            // }
            $contact = Contact::find($contact_id);

            $path = $this->text_to_speech_alt($content, $messageId, $organisation->openAI);
            if (!$path) {
                $contact->status = "OpenAI ERROR";
                $contact->save();
            }
            try {
                $mediaUrl = [$path];
                $message = $twilio->messages->create(
                    $phone,
                    [
                        'from' => $texting_number,
                        //'body' => $content,
                        'mediaUrl' => $mediaUrl
                    ]
                );
                Log::info("I sent an MMS with thi $message->sid");
                if ($contact) {
                    $communication_ids_array = [];
                    $communication_ids = $contact->contact_communication_ids;
                    if (!empty($communication_ids)) {
                        $communication_ids_array = explode(',', $communication_ids);
                    }
                    $message_sid = $message->sid;
                    array_push($communication_ids_array, $message_sid);
                    $new_contact_communication_ids = implode(',', $communication_ids_array);
                    $contact->contact_communication_ids = $new_contact_communication_ids;
                    $contact->save();
                    Log::info("Message sent with SID: $message_sid");
                    $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

                    // Extract the custom fields for city, state, and zipcode
                    $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
                    $city = $contact_info['custom_fields']['CITY'] ?? null;
                    $state = $contact_info['custom_fields']['STATE'] ?? null;
                    if ($message_sid) {
                        $text_sent = TextSent::create([
                            'name' => $contact->contact_name,
                            'contact_id' => $contact->id,
                            'contact_communication_id' => $message_sid,
                            'organisation_id' => $organisation_id,
                            'zipcode' => $zipcode,
                            'state' => $state,
                            'city' => $city,
                            'marketing_channel' => 'VoiceMMS',
                            'sending_number' => $texting_number,
                            'user_id' => $workflow->user_id,
                            'response' => 'No'

                        ]);
                    } {
                        $contact->status = "MMS FAILED";
                        $contact->save();
                    }
                } else {
                    Log::error("Contact with ID $contact_id not found.");
                }
            } catch (\Exception $e) {
                $contact->status = "MMS FAILED";
                $contact->save();
                Log::info("Error:" . $e->getMessage());
            }
        } else {
            $projectID = $organisation->signalwire_texting_project_id;
            $authToken = $organisation->signalwire_texting_api_token;
            $signalwireSpaceUrl = $organisation->signalwire_texting_space_url; // Example: example.signalwire.com
            $workflow = Workflow::find($workflow_id);
            $texting_number = $workflow->texting_number; // Example: example.signalwire.com

            // Create a new SignalWire Client
            $client = new SignalWireClient($projectID, $authToken, [
                'signalwireSpaceUrl' => $signalwireSpaceUrl
            ]);

            // Define the audio file URL
            $messageId = Str::uuid();
            $path = $this->text_to_speech_alt($content, $messageId, $organisation->openAI);
            // Send an MMS
            $contact = Contact::find($contact_id);
            if (!$path) {
                $contact->status = "OpenAI ERROR";
                $contact->save();
            }
            try {
                $message = $client->messages->create(
                    $phone, // Destination phone number (in E.164 format)
                    [
                        'from' => $texting_number, // Your SignalWire phone number (in E.164 format)
                        //'body' => $content,
                        'mediaUrl' => [$path] // Media URL array containing the audio file URL
                    ]
                );
                Log::info("I sent an MMS with this $message->sid");
                if ($contact) {
                    $communication_ids_array = [];
                    $communication_ids = $contact->contact_communication_ids;
                    if (!empty($communication_ids)) {
                        $communication_ids_array = explode(',', $communication_ids);
                    }
                    $message_sid = $message->sid;
                    array_push($communication_ids_array, $message_sid);
                    $new_contact_communication_ids = implode(',', $communication_ids_array);
                    $contact->contact_communication_ids = $new_contact_communication_ids;
                    $contact->save();
                    Log::info("Message sent with SID: $message_sid");
                    $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

                    // Extract the custom fields for city, state, and zipcode
                    $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
                    $city = $contact_info['custom_fields']['CITY'] ?? null;
                    $state = $contact_info['custom_fields']['STATE'] ?? null;
                    if ($message_sid) {
                        $text_sent = TextSent::create([
                            'name' => $contact->contact_name,
                            'contact_id' => $contact->id,
                            'contact_communication_id' => $message_sid,
                            'organisation_id' => $organisation_id,
                            'zipcode' => $zipcode,
                            'state' => $state,
                            'city' => $city,
                            'marketing_channel' => 'VoiceMMS',
                            'sending_number' => $texting_number,
                            'user_id' => $workflow->user_id,
                            'response' => 'No'

                        ]);
                    } else {
                        $contact->status = "MMS FAILED";
                        $contact->save();
                    }
                } else {
                    Log::error("Contact with ID $contact_id not found.");
                }
            } catch (\Exception $e) {
                Log::info("Error:" . $e->getMessage());
                $contact->status = "MMS FAILED";
                $contact->save();
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
        if ($organisation->texting_service == 'twilio') {
            $workflow = Workflow::find($workflow_id);
            $texting_number = $workflow->texting_number;
            $sid = $organisation->twilio_texting_account_sid;
            $token = $organisation->twilio_texting_auth_token;
            $twilio = new TwilioClient($sid, $token);
            $workflow = Workflow::find($workflow_id);
            $voice = $workflow->voice;
            $userId = 1;
            $messageId = Str::uuid();
            // if (!empty($voice)) {
            //     $path = $this->textToSpeech($content, $userId, $messageId, $voice);
            // } else {
            //     $path = $this->textToSpeech($content, $userId, $messageId, 'knrPHWnBmmDHMoiMeP3l');
            // }
            $contact = Contact::find($contact_id);
            $step = Step::find($contact->current_step);
            $expiry = $step->offer_expiry;
            Log::info("Step expiry is $expiry");
            $path = $this->generate_offer_card($contact->address, $expiry, $contact->offer, $contact->agent);
            Log::info($path);
            if (!$path) {
                $contact->status = "Image Gen Error";
                $contact->save();
            }
            try {
                $mediaUrl = [$path];
                $message = $twilio->messages->create(
                    $phone,
                    [
                        'from' => $texting_number,
                        //'body' => $content,
                        'mediaUrl' => $mediaUrl
                    ]
                );
                Log::info("I sent an MMS with thi $message->sid");
                if ($contact) {
                    $communication_ids_array = [];
                    $communication_ids = $contact->contact_communication_ids;
                    if (!empty($communication_ids)) {
                        $communication_ids_array = explode(',', $communication_ids);
                    }
                    $message_sid = $message->sid;
                    array_push($communication_ids_array, $message_sid);
                    $new_contact_communication_ids = implode(',', $communication_ids_array);
                    $contact->contact_communication_ids = $new_contact_communication_ids;
                    $contact->save();
                    Log::info("Message sent with SID: $message_sid");
                    $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

                    // Extract the custom fields for city, state, and zipcode
                    $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
                    $city = $contact_info['custom_fields']['CITY'] ?? null;
                    $state = $contact_info['custom_fields']['STATE'] ?? null;
                    if ($message_sid) {
                        $text_sent = TextSent::create([
                            'name' => $contact->contact_name,
                            'contact_id' => $contact->id,
                            'contact_communication_id' => $message_sid,
                            'organisation_id' => $organisation_id,
                            'zipcode' => $zipcode,
                            'state' => $state,
                            'city' => $city,
                            'marketing_channel' => 'OfferMMS',
                            'sending_number' => $texting_number,
                            'user_id' => $workflow->user_id,
                            'response' => 'No'

                        ]);
                    } {
                        $contact->status = "OFFER SENT";
                        $contact->save();
                    }
                } else {
                    Log::error("Contact with ID $contact_id not found.");
                }
            } catch (\Exception $e) {
                $contact->status = "MMS FAILED";
                $contact->save();
                Log::info("Error:" . $e->getMessage());
            }
        } else {
            $projectID = $organisation->signalwire_texting_project_id;
            $authToken = $organisation->signalwire_texting_api_token;
            $signalwireSpaceUrl = $organisation->signalwire_texting_space_url; // Example: example.signalwire.com
            $workflow = Workflow::find($workflow_id);
            $texting_number = $workflow->texting_number; // Example: example.signalwire.com

            // Create a new SignalWire Client
            $client = new SignalWireClient($projectID, $authToken, [
                'signalwireSpaceUrl' => $signalwireSpaceUrl
            ]);

            // Define the audio file URL
            $messageId = Str::uuid();
            $contact = Contact::find($contact_id);
            $step = Step::find($contact->current_step);
            $expiry = $step->offer_expiry;
            $path = $this->generate_offer_card($contact->address, $expiry, $contact->offer, $contact->agent);
            // Send an MMS
            if (!$path) {
                $contact->status = "Imagick Error";
                $contact->save();
            }
            try {
                $message = $client->messages->create(
                    $phone, // Destination phone number (in E.164 format)
                    [
                        'from' => $texting_number, // Your SignalWire phone number (in E.164 format)
                        //'body' => $content,
                        'mediaUrl' => [$path] // Media URL array containing the audio file URL
                    ]
                );
                Log::info("I sent an MMS with this $message->sid");
                if ($contact) {
                    $communication_ids_array = [];
                    $communication_ids = $contact->contact_communication_ids;
                    if (!empty($communication_ids)) {
                        $communication_ids_array = explode(',', $communication_ids);
                    }
                    $message_sid = $message->sid;
                    array_push($communication_ids_array, $message_sid);
                    $new_contact_communication_ids = implode(',', $communication_ids_array);
                    $contact->contact_communication_ids = $new_contact_communication_ids;
                    $contact->save();
                    Log::info("Message sent with SID: $message_sid");
                    $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

                    // Extract the custom fields for city, state, and zipcode
                    $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
                    $city = $contact_info['custom_fields']['CITY'] ?? null;
                    $state = $contact_info['custom_fields']['STATE'] ?? null;
                    if ($message_sid) {
                        $text_sent = TextSent::create([
                            'name' => $contact->contact_name,
                            'contact_id' => $contact->id,
                            'contact_communication_id' => $message_sid,
                            'organisation_id' => $organisation_id,
                            'zipcode' => $zipcode,
                            'state' => $state,
                            'city' => $city,
                            'marketing_channel' => 'VoiceMMS',
                            'sending_number' => $texting_number,
                            'user_id' => $workflow->user_id,
                            'response' => 'No'

                        ]);
                    } else {
                        $contact->status = "OFFER SENT";
                        $contact->save();
                    }
                } else {
                    Log::error("Contact with ID $contact_id not found.");
                }
            } catch (\Exception $e) {
                Log::info("Error:" . $e->getMessage());
                $contact->status = "MMS FAILED";
                $contact->save();
            }
        }
    }
    private function send_Voicemail($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $workflow = Workflow::find($workflow_id);
        $agent_phone_number = $workflow->agent_number;
        $voice = $workflow->voice;
        $userId = 1;
        $messageId = Str::uuid();
        // if (!empty($voice)) {
        //     $path = $this->textToSpeech($content, $userId, $messageId, $voice);
        // } else {
        //     $path = $this->textToSpeech($content, $userId, $messageId, 'knrPHWnBmmDHMoiMeP3l');
        // }
        $organisation = Organisation::find($organisation_id);

        $path = $this->text_to_speech_alt($content, $messageId, $organisation->openAI);
        $contact = Contact::find($contact_id);
        if (!$path) {
            $contact->status = "OpenAI ERROR";
            $contact->save();
        }
        Log::info("I  Reached Voicemail sending function. org is id $organisation_id");
        print_r($organisation);
        if ($organisation->calling_service == 'signalwire') {
            Log::info("The calling service is signalwire");
            $this->place_call($phone, $path, $agent_phone_number, '20', $contact_id, $organisation_id);
        }
    }
    private function send_VoiceCall($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $workflow = Workflow::find($workflow_id);
        $agent_phone_number = $workflow->agent_number;
        $voice = $workflow->voice;
        $userId = 1;
        $messageId = Str::uuid();
        // if (!empty($voice)) {
        //     $path = $this->textToSpeech($content, $userId, $messageId, $voice);
        // } else {
        //     $path = $this->textToSpeech($content, $userId, $messageId, 'knrPHWnBmmDHMoiMeP3l');
        // }
        $organisation = Organisation::find($organisation_id);
        $path = $this->text_to_speech_alt($content, $messageId, $organisation->openAI);
        $contact = Contact::find($contact_id);
        if (!$path) {
            $contact->status = "OpenAI ERROR";
            $contact->save();
        }
        $this->place_call($phone, $path, $agent_phone_number, '3', $contact_id, $organisation_id);
        Log::info("I  Reached VoiceCall sending function");
    }
    private function place_call($phone, $voice_recording, $agent_phone_number, $detection_duration, $contact_id, $organisation_id)
    {
        $contact = Contact::find($contact_id);
        $workflow = Workflow::find($contact->workflow_id);
        $organisation = Organisation::find($organisation_id);
        $calling_number = $workflow->calling_number;
        $signalwire_space_url = $organisation->signalwire_calling_space_url;
        $project_id = $organisation->signalwire_calling_project_id;
        $api_token = $organisation->signalwire_calling_api_token;
        $to_number = $phone;
        $from_number = $calling_number;
        $api_url = "https://$signalwire_space_url/api/laml/2010-04-01/Accounts/$project_id/Calls.json";
        $data_first_call = [
            'Url' => route('answer-workflow-call', ['voice_recording' => $voice_recording, 'agent_phone_number' => $agent_phone_number, 'contact_id' => $contact_id]),
            'To' => $to_number,
            'From' => $from_number,
            'MachineDetection' => 'DetectMessageEnd',
            'MachineDetectionTimeout' => $detection_duration,
        ];
        list($http_code, $response) = $this->make_call($api_url, $data_first_call, $project_id, $api_token);
        $callData = json_decode($response, true);
        if (isset($callData['sid'])) {
            $call_sid = $callData['sid'];
            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
            // Extract the custom fields for city, state, and zipcode
            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
            $city = $contact_info['custom_fields']['CITY'] ?? null;
            $state = $contact_info['custom_fields']['STATE'] ?? null;
            CallsSent::create([
                'name' => $contact->contact_name,
                'contact_id' => $contact->id,
                'contact_communication_id' => $call_sid,
                'organisation_id' => $organisation_id,
                'zipcode' => $zipcode,
                'state' => $state,
                'city' => $city,
                'marketing_channel' => 'VoiceCall',
                'sending_number' => $calling_number,
                'user_id' => $workflow->user_id,
                'response' => 'No'

            ]);
            $contact = Contact::find($contact_id);
            if ($contact) {
                $communication_ids_array = [];
                $communication_ids = $contact->contact_communication_ids;
                if (!empty($communication_ids)) {
                    $communication_ids_array = explode(',', $communication_ids);
                }
                array_push($communication_ids_array, $callData['sid']);
                $new_contact_communication_ids = implode(',', $communication_ids_array);
                $contact->contact_communication_ids = $new_contact_communication_ids;
                $contact->save();
                Log::info('call SID: ' . $callData['sid']);

                Log::info("Message sent with SID: $call_sid");
            } else {
                Log::error("Contact with ID $contact_id not found.");
            }
        } else {
            $contact->status = "CALL FAILED";
            $contact->save();
            Log::info("Failed to call $phone");
        }
        Log::info("Call response: $response, HTTP Code: $http_code");
        return response($response)
            ->header('Content-Type', 'application/json');
    }

    private function make_call($api_url, $data, $project_id, $api_token)
    {
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "$project_id:$api_token");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$http_code, $response];
    }
    public function handleCall(Request $request)
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
    private function textToSpeech($textMessage, $userId, $messageId, $voice)
    {
        $endPointUrl = "https://api.elevenlabs.io/v1/text-to-speech/$voice";
        $apiKey = env('ELEVEN_LABS_API_KEY');
        $headers = [
            "Accept" => "audio/mpeg",
            "Content-Type" => "application/json",
            "xi-api-key" => $apiKey
        ];
        $data = [
            "text" => $textMessage,
            "model_id" => "eleven_monolingual_v1",
            "voice_settings" => [
                "stability" => 0.5,
                "similarity_boost" => 0.5
            ]
        ];
        $response = Http::withHeaders($headers)->post($endPointUrl, $data);
        if ($response->failed()) {
            Log::error('API request failed', ['response' => $response->body()]);
            return false;
        }
        $audioContent = $response->body();
        $baseDir = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads';
        $fileName = "mes_" . $messageId . '_' . rand(0000, 9999) . ".mp3";
        $fullPath = $baseDir . DIRECTORY_SEPARATOR . $fileName;
        if (!is_dir($baseDir)) {
            if (!mkdir($baseDir, 0777, true) && !is_dir($baseDir)) {
                Log::error('Failed to create directory', ['directory' => $baseDir]);
                return false;
            }
        }
        if (file_put_contents($fullPath, $audioContent) !== false) {
            $publicUrl = 'https://internaltools.godspeedoffers.com/uploads/' . $fileName;
            Log::info('File saved', ['path' => $publicUrl]);
            return $publicUrl;
        } else {
            Log::error('Failed to save file', ['path' => $fullPath]);
            return false;
        }
    }
    public function calculate_cost()
    {
        // Log::info("I tried to calculate cost");
        $text_sents = DB::table('text_sents')
            ->where('cost', null)
            ->get();
        foreach ($text_sents as $text_sent) {
            if ($text_sent->cost == null) {
                CalculateCostJob::dispatch(
                    $text_sent
                );
                //Log::info("dispatched cost calculation for $contact");
            }
        }
        $calls_sents =  DB::table('calls_sents')
            ->where('cost', null)
            ->get();
        foreach ($calls_sents as $call_sent) {
            CalculateCostJob::dispatch(
                $call_sent
            );
            //Log::info("dispatched cost calculation for $call_sent->id");
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
                // Log::info("The cost of the SMS is $price $currency.");
            } catch (\Exception $e) {
                return 'Error: ' . $e->getMessage();
            }
        } elseif ($contact->marketing_channel == "SMS") {
            try {
                // Fetch the message details from SignalWire
                $client = new SignalWireClient(
                    $organisation->signalwire_texting_project_id,
                    $organisation->signalwire_texting_api_token,
                    ['signalwireSpaceUrl' => $organisation->signalwire_texting_space_url]
                );
                $message = $client->messages($communication_id)->fetch();
                $price = $message->price;
                $currency = $message->priceUnit;

                // Calculate the cost
                $cost = abs($price);

                // Update the cost in the TextSent model
                $contact = TextSent::find($contact->id);
                if ($contact) {
                    $contact->cost = $cost;
                    $contact->save();
                    // Log the success
                    //Log::info("The cost of the SMS is $cost $currency for contact ID: $contact->id.");
                } else {
                    Log::warning("No TextSent record found for contact ID: $contact->id.");
                }
            } catch (\Exception $e) {
                // Handle exceptions
                Log::error("Error fetching message cost from SignalWire: " . $e->getMessage());
                return 'Error: ' . $e->getMessage();
            }
        } else {
            // Log::info("I reached signalwire cost calc");

            $signalWireSpaceUrl = $organisation->signalwire_calling_space_url;
            $projectId = $organisation->signalwire_calling_project_id;
            $authToken = $organisation->signalwire_calling_api_token;
            $client = new SignalWireClient(
                $projectId,
                $authToken,
                ['signalwireSpaceUrl' => $signalWireSpaceUrl]
            );
            try {
                // Retrieve the call details
                $call = $client->calls($communication_id)->fetch();

                // Get the call cost
                $cost = $call->price;
            } catch (\Exception $e) {
                Log::info("Error $e when fetching call price");
            }

            //Log::info("TOTAL COST for sending $contact->id stands at: " . abs($cost));
            $contact = CallsSent::find($contact->id);
            $contact->cost = abs($cost);
            $contact->save();
        }
    }
    // public function response_check()
    // {
    //     Log::info("I tried to check for contacts with response");
    //     $steps = Step::all();

    //     foreach ($steps as $step) {
    //         $contacts = DB::table('contacts')
    //             ->where('response', 'No')
    //             ->where('can_send', 0)
    //             ->where('current_step', $step->id)
    //             ->get();

    //         foreach ($contacts as $contact) {
    //             $workflow = Workflow::find($contact->workflow_id);
    //             if ($workflow != null && $workflow->active) {
    //                 $steps_flow_array = explode(',', $workflow->steps_flow);
    //                 $last_step = end($steps_flow_array);
    //                 if ($last_step !== $contact->current_step) {
    //                     if ($contact->contact_communication_ids !== null) {
    //                         ResponseCheckJob::dispatch(
    //                             $contact
    //                         )->delay(now()->addSeconds(60));
    //                         //Log::info("dispatched response check for $contact->id");
    //                     }
    //                 } else {
    //                     Log::info("Contact $contact->phone is in the last step. No response check");
    //                 }
    //             }
    //         }
    //     }
    // }
    public function contact_response_check($contact)
    {
        Log::info("I tried to check if $contact->contact_name Responded");
        // $organisation = Organisation::find($contact->organisation_id);
        // if (!empty($contact->contact_communication_ids)) {
        //     $communication_ids_array = explode(',', $contact->contact_communication_ids);
        //     foreach ($communication_ids_array as $communication_id) {
        //         if (strpos($communication_id, 'SM') === 0 || strpos($communication_id, 'MM') === 0) {
        //             $client = new TwilioClient($organisation->twilio_texting_account_sid, $organisation->twilio_texting_auth_token);

        //             try {
        //                 $sentMessage = $client->messages($communication_id)->fetch();
        //                 $sentTo = $sentMessage->to;
        //                 $sentFrom = $sentMessage->from;
        //                 $messages = $client->messages->read([
        //                     'from' => $sentTo,
        //                     'to' => $sentFrom,
        //                     'limit' => 20,
        //                 ]);

        //                 foreach ($messages as $message) {
        //                     if ($message->sid != $communication_id) {
        //                         Log::info("Contact $contact->contact_name responded");
        //                         $contact = Contact::find($contact->id);
        //                         $contact->response = 'Yes';
        //                         $contact->save();
        //                         continue 2; // Move to the next contact immediately
        //                     }
        //                 }

        //                 //Log::info("Contact $contact->contact_name did not respond");
        //             } catch (\Exception $e) {
        //                 Log::info("There was an error: $e");
        //             }
        //         } else {
        //             if ($organisation->texting_server == 'signalwire') {
        //                 try {
        //                     $client = new SignalWireClient($organisation->signalwire_texting_project_id, $organisation->signalwire_texting_api_token, array("signalwireSpaceUrl" => $organisation->signalwire_texting_space_url));
        //                     $sentMessage = $client->messages->read(['MessageSid' => $communication_id])[0];
        //                     $sentTo = $sentMessage->to;
        //                     $sentFrom = $sentMessage->from;
        //                     $messages = $client->messages->read([
        //                         'From' => $sentTo,
        //                         'To' => $sentFrom,
        //                         'Limit' => 20,
        //                     ]);

        //                     foreach ($messages as $message) {
        //                         if ($message->sid != $communication_id) {
        //                             Log::info("Contact $contact->contact_name responded");
        //                             $contact = Contact::find($contact->id);
        //                             $contact->response = 'Yes';
        //                             $contact->save();
        //                             continue 2; // Move to the next contact immediately
        //                         }
        //                     }
        //                 } catch (\Exception $e) {
        //                     Log::info("There was an error: $e");
        //                 }
        //             }
        //         }
        //     }
        // } else {
        //     //Log::info("Contact $contact->contact_name did not respond");
        // }
    }
    public function opt_out_numbers()
    {
        $client = new TwilioClient(env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN'));
        $dateSentAfter = now()->subDay(); // Get messages from the last hour
        $optOutKeywords = ['STOP', 'UNSUBSCRIBE', 'CANCEL', 'END', 'QUIT'];
        $pageSize = 100; // Number of messages to fetch per page
        $pageNumber = 0;
        $numbers = Number::all();
        foreach ($numbers as $number) {
            $messages = $client->messages->read([
                'dateSentAfter' => $dateSentAfter->toDateTimeString(),
                'to' => $number->phone_number
            ], $pageSize);
            while (!empty($messages)) {
                foreach ($messages as $message) {
                    if ($message->direction === 'inbound' && in_array(strtoupper(trim($message->body)), $optOutKeywords)) {
                        $phone = str_replace('+', '', $message->from);
                        Contact::where('phone', $phone)->update(['subscribed' => 0]);
                        // Log::info("$phone used $message->body to opt out.");
                    }
                }
                $pageNumber++;
                $messages = $client->messages->read([
                    'dateSentAfter' => $dateSentAfter->toDateTimeString()
                ], $pageSize, $pageNumber * $pageSize);
            }
        }
    }
    private static function  spintax($text)
    {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*)\}/x',
            function ($text) {
                $text = $text[1];
                $parts = explode('|', $text);
                return $parts[array_rand($parts)];
            },
            $text
        );
    }
    private function text_to_speech_alt($textMessage, $messageId, $openAiApiKey)
    {
        // Temporarily override the OpenAI API key
        //config(['services.openai.key' => $openAiApiKey]);
        $client = OpenAI::client($openAiApiKey);
        try {
            // Call the OpenAI API with the new key
            $result = $client->audio()->speech([
                'model' => 'tts-1',
                'input' => $textMessage,
                'voice' => 'onyx',
            ]);
        } catch (\Exception $e) {
            // Log the error and return false if the OpenAI request fails
            Log::error('OpenAI API request failed', ['error' => $e->getMessage()]);
            return false;
        }

        // Save the audio file
        $baseDir = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads';
        $fileName = "mes_" . $messageId . '_' . rand(0000, 9999) . ".mp3";
        $fullPath = $baseDir . DIRECTORY_SEPARATOR . $fileName;

        if (!is_dir($baseDir)) {
            if (!mkdir($baseDir, 0777, true) && !is_dir($baseDir)) {
                Log::error('Failed to create directory', ['directory' => $baseDir]);
                return false;
            }
        }

        if (file_put_contents($fullPath, $result) !== false) {
            $publicUrl = 'https://internaltools.godspeedoffers.com/uploads/' . $fileName;
            Log::info('File saved', ['path' => $publicUrl]);
            return $publicUrl;
        } else {
            Log::error('Failed to save file', ['path' => $fullPath]);
            return false;
        }
    }


    private function send_customer_data()
    {
        $token = '4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0';
        $data = [
            'to' => '18665302257',
            'from' => '14075812918',
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
    public function custom_send()
    {
        $login = 'AKKFO6';
        $password = 'ge4i5ofxlcpgad';

        $client = new AndroidSMSGateway($login, $password);
        // or
        // $encryptor = new Encryptor('your_passphrase');
        // $client = new Client($login, $password, Client::DEFAULT_URL, $httpClient, $encryptor);

        $message = new Message('Your message text here.', ['+254790508982']);

        try {
            $messageState = $client->Send($message);
            echo "Message sent with ID: " . $messageState->ID() . PHP_EOL;
        } catch (Exception $e) {
            echo "Error sending message: " . $e->getMessage() . PHP_EOL;
            die(1);
        }

        try {
            $messageState = $client->GetState($messageState->ID());
            echo "Message state: " . $messageState->State() . PHP_EOL;
        } catch (Exception $e) {
            echo "Error getting message state: " . $e->getMessage() . PHP_EOL;
            die(1);
        }
    }
    public function fill_zipcodes()
    {
        //set_time_limit(0); // Set the execution time to unlimited

        while (true) {
            // Use a chunk to process records in batches of 20
            CallsSent::chunk(20, function ($callSents) {
                foreach ($callSents as $callSent) {
                    $contact = Contact::find($callSent->contact_id);

                    if ($contact && $callSent->zipcode == null) {
                        try {
                            $workflow = Workflow::find($contact->workflow_id);
                            $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

                            // Extract the custom fields for city, state, and zipcode
                            $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
                            $city = $contact_info['custom_fields']['CITY'] ?? null;
                            $state = $contact_info['custom_fields']['STATE'] ?? null;

                            // Update the callSent fields
                            $callSent->zipcode = $zipcode;
                            $callSent->city = $city;
                            $callSent->state = $state;
                            $callSent->save();
                            Log::info("Contact info save success for {$callSent->id}.");
                        } catch (\Exception $e) {
                            // Log the error and skip this contact
                            //Log::error("Error retrieving contact for CallSent ID {$callSent->id}: {$e->getMessage()}");
                        }
                    } else {
                        //Log::info("Contact not found for CallSent ID {$callSent->id}.");
                    }
                }

                // Optionally: Check for a stopping condition if needed
                // For example, you can break out of the loop if there are no more records to process.
                // You can check the count of $callSents and break if it's less than the chunk size.
            });

            // To prevent busy-waiting and give some time between iterations
            sleep(1); // Wait for 1 second before the next chunk
        }
    }

    private function send_Email($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        Log::info('Attempting to send email');

        try {
            $organisation = Organisation::find($organisation_id);
            $sending_email = $organisation->sending_email;
            $password = $organisation->email_password;

            // Log retrieved email credentials
            Log::info("Sending email from: $sending_email");

            // Set the SMTP username and password dynamically
            Config::set('mail.mailers.smtp.username', $sending_email);
            Config::set('mail.mailers.smtp.password', $password);

            $contact = Contact::find($contact_id);
            $step = Step::find($contact->current_step);
            $subject = $step->email_subject ?? 'New Email'; // Fallback to a default subject if not set
            $workflow = Workflow::find($contact->workflow_id);
            $contactInfo = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

            // Compose and spintax the message
            $subject = $this->composeMessage($contactInfo, $subject);
            $subject = $this->spintax($subject);
            // Log contact email and subject
            Log::info("Sending to: {$contact->email}, Subject: $subject");

            // Email details
            $details = [
                'name' => $sending_email,
                'email' => $sending_email,
                'subject' => $subject,
                'message' => $content,
                'from_email' => $sending_email,
                'from_name' => $sending_email
            ];

            // Attempt to send the email
            Mail::to($contact->email)->send(new ContactEmail($details));

            Log::info('Email sent successfully');
            return response()->json(['message' => 'Email sent successfully!'], 200);
        } catch (\Exception $e) {
            // Log the error message with the exception details
            Log::error("Failed to send email: {$e->getMessage()}", [
                'exception' => $e,
                'organisation_id' => $organisation_id,
                'contact_id' => $contact_id,
                'sending_email' => $sending_email ?? null,
                'contact_email' => $contact->email ?? null
            ]);

            return response()->json(['error' => 'Failed to send email', 'details' => $e->getMessage()], 500);
        }
    }


    public function contact_search(Request $request)
    {
        // Validate the phone number input
        $request->validate([
            'phone_number' => 'required|string|max:15',
        ]);

        // Search for the contact by phone number
        $contact = Contact::where('phone', $request->input('phone_number'))->first();

        // Return JSON response
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
