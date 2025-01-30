<?php

namespace App\Http\Controllers;

use App\Jobs\SendSmsReplyJob;
use App\Models\AI_Lead;
use App\Models\Assistant;
use App\Models\ClosedDeal;
use App\Models\Contact;
use App\Models\executedContracts;
use App\Models\TextSent;
use App\Models\CallsSent;
use App\Models\offers;
use App\Models\Step;
use App\Models\Thread;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\ValidLead;
use App\Models\Workflow;
use Carbon\Carbon;
use Database\Seeders\TextSentSeeder;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;
use App\Models\Organisation;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    public function get_user_and_orgs()
    {
        // Retrieve all users with their associated organizations
        $users = User::with('organisation')->get();

        // Create an array to store the user and organization details
        $userAndOrgs = $users->map(function ($user) {
            // Check if the user has an organization
            return [
                'user_name' => $user->name,
                'organisation_name' => $user->organisation ? $user->organisation->organisation_name : 'No Organisation',
                'organisation_id' => $user->organisation ? $user->organisation->id : null
            ];
        });

        // Return the array of user and organization details
        return $userAndOrgs->toArray();
    }
    public function update_lead_status(Request $request)
    {
        Log::info('I am here');
        // Retrieve the query parameters
        $first_name = $request->query('first_name');
        $last_name = $request->query('last_name');
        $phone = $request->query('phone');
        $userOrg = $request->query('user_org');
        $lead_status = $request->query('lead_status');
        $existing_contact = Contact::firstWhere('phone', $phone);
        $workflow = Workflow::find($existing_contact->workflow_id);
        $contact_info = $this->get_contact($existing_contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);

        // Extract the custom fields for city, state, and zipcode
        $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
        $city = $contact_info['custom_fields']['CITY'] ?? null;
        $state = $contact_info['custom_fields']['STATE'] ?? null;

        $contact = Contact::firstOrCreate(
            ['phone' => $phone],
            [
                'uuid' => '333',
                'workflow_id' => '333wx',
                'can_send' => 0,
                'response' => 'No',
                'contact_name' => $first_name . " " . $last_name,
                'status' => 'WAITING_FOR_QUEAUE',
                'cost' => 0,
                'subscribed' => 0,
                'organisation_id' => $userOrg,
                'user_id' => $existing_contact->user_id,
                'zipcode' => $zipcode,
                'city' => $city,
                'state' => $state,
            ]
        );

        // Switch case for handling lead status
        switch ($lead_status) {
            case 1:
                // Check if the offer already exists
                $existingOffer = Offers::where('contact_id', $existing_contact->id)
                    ->where('organisation_id', $userOrg)
                    ->first();
                if (!$existingOffer) {
                    $offer = Offers::create([
                        'name' => $first_name . " " . $last_name,
                        'contact_id' => $existing_contact->id,
                        'organisation_id' => $userOrg,
                        'zipcode' => $zipcode,
                        'city' => $city,
                        'state' => $state,
                        'user_id' => $existing_contact->user_id,
                    ]);
                }
                break;
            case 2:
                // Check if the valid lead already exists
                $existingValidLead = ValidLead::where('contact_id', $existing_contact->id)
                    ->where('organisation_id', $userOrg)
                    ->first();
                if (!$existingValidLead) {
                    $valid_deal = ValidLead::create([
                        'name' => $first_name . " " . $last_name,
                        'contact_id' => $existing_contact->id,
                        'organisation_id' => $userOrg,
                        'zipcode' => $zipcode,
                        'city' => $city,
                        'state' => $state,
                        'user_id' => $existing_contact->user_id,
                    ]);
                }
                break;
            case 3:
                // Check if the executed contract already exists
                $existingExecutedContract = executedContracts::where('contact_id', $existing_contact->id)
                    ->where('organisation_id', $userOrg)
                    ->first();
                if (!$existingExecutedContract) {
                    $executedContracts = executedContracts::create([
                        'name' => $first_name . " " . $last_name,
                        'contact_id' => $existing_contact->id,
                        'organisation_id' => $userOrg,
                        'zipcode' => $zipcode,
                        'city' => $city,
                        'state' => $state,
                        'user_id' => $existing_contact->user_id,
                    ]);
                }
                break;
            case 4:
                // Check if the closed deal already exists
                $existingClosedDeal = ClosedDeal::where('contact_id', $existing_contact->id)
                    ->where('organisation_id', $userOrg)
                    ->first();
                if (!$existingClosedDeal) {
                    $deal_closed = ClosedDeal::create([
                        'name' => $first_name . " " . $last_name,
                        'contact_id' => $existing_contact->id,
                        'organisation_id' => $userOrg,
                        'zipcode' => $zipcode,
                        'city' => $city,
                        'state' => $state,
                        'user_id' => $existing_contact->user_id,
                    ]);
                }
                break;
            default:
                break;
        }

        // Return a success message
        return response()->json(['message' => 'Lead status updated successfully.']);
    }

    public function save_response($phone)
    {
        // Update the Contact model
        $contacts = Contact::where('phone', $phone)->get(); // Get all contacts with the same phone number
        if ($contacts->isNotEmpty()) {
            foreach ($contacts as $contact) {
                $contact->response = 'Yes';
                $contact->save();
                Log::info("I saved response for $contact->contact_name");
                // Update the CallsSents model
                $callsSents = CallsSent::where('contact_id', $contact->id)->get(); // Get all call sent records with the same phone number
                if ($callsSents->isNotEmpty()) {
                    foreach ($callsSents as $call) {
                        $call->response = 'Yes';
                        $call->save();
                        Log::info("I saved response for call sent record with ID: $call->id");
                    }
                } else {
                    Log::info("No call sent records found with the phone number: $phone");
                }

                // Update the TextSents model
                $textSents = TextSent::where('contact_id', $contact->id)->get(); // Get all text sent records with the same phone number
                if ($textSents->isNotEmpty()) {
                    foreach ($textSents as $text) {
                        $text->response = 'Yes';
                        $text->save();
                        Log::info("I saved response for text sent record with ID: $text->id");
                    }
                } else {
                    Log::info("No text sent records found with the phone number: $phone");
                }
            }
        } else {
            Log::info("No contacts found with the phone number: $phone");
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
        Log::info('Final Template: ' . $template);
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
    public function get_message(string $phone)
    {
        // Retrieve the contact based on the phone number
        $contact = DB::table('contacts')->where('phone', $phone)->first();

        // Check if the contact exists
        if (!$contact) {
            return response()->json(['error' => 'Contact not found'], 404);
        }

        // Get the current step from the contact
        $current_step = $contact->current_step;

        // Find the corresponding step
        $step = Step::find($current_step);

        // Check if the step exists
        if (!$step) {
            return response()->json(['error' => 'Step not found'], 404);
        }

        // Get the content of the step
        $content = $step->content;
        $workflow = Workflow::find($step->workflow_id);
        $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
        $content = $this->composeMessage($contact_info, $content);
        // Return the content in the response
        return response()->json(['message' => $content]);
    }
    public function get_AI_reply(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'message' => 'required|string',
            'sending_number' => 'required|string',
            'phone' => 'required|string',
            'openAI_id' => 'required|string'

        ]);

        $sending_number = $request->sending_number;
        $phone = $request->phone;
        $workflow_message=$this->get_workflow_message($phone);
        $workflow_message = $workflow_message ?? "N/A";
        $userInput = $request->message;
        Log::info("user input is $userInput");
        if($this->containsKeywords($userInput)){
            Log::info("$userInput is an invalid keyword. Ceasing operation immediately");
            return;
        }
        if($this->containsEmoji($userInput)){
            Log::info("$userInput has an emoji. Ceasing operation immediately");
            return;
        }


        $assistant_id = $request->openAI_id;
        // Check if a thread exists for the given phone
        $exists = Thread::where('phone', $phone)->first();
        $contact = Contact::where('phone', $phone)->first();
        $contactContext = [
            'name' => $contact->contact_name ?? 'N/A',
            'zipcode' => $contact->zipcode ?? 'N/A',
            'state' => $contact->state ?? 'N/A',
            'offer' => $contact->offer ?? 'N/A',
            'address' => $contact->address ?? 'N/A',
            'gender' => $contact->gender ?? 'N/A',
            'lead_score' => $contact->lead_score ?? 'N/A',
            'phone' => $contact->phone ?? 'N/A',
            'organisation_id' => $contact->organisation_id ?? 'N/A',
            'novation' => $contact->novation ?? 'N/A',
            'creative_price' => $contact->creative_price ?? 'N/A',
            'downpayment' => $contact->downpayment ?? 'N/A',
            'monthly' => $contact->monthly ?? 'N/A',
        ];

        $assistant = Assistant::where('openAI_id', $request->openAI_id)->first();
        $wait_time = $this->calculateWaitTime($assistant->min_wait_time, $assistant->max_wait_time);
        Log::info("The calculated random wait time is $wait_time");
        $delay = (int) $this->convertToMinutes($wait_time, $assistant->wait_time_units);
        $api_key = $assistant->openAI;
        Log::info("I am using this api key $api_key");
        try {
            if ($exists) {
                Log::info("thread exists");
                if ($contact) {
                    $workflow = Workflow::find($contact->workflow_id);
                    $bearerToken = $workflow->godspeedoffers_api;
                    Log::info("Here $bearerToken");
                } else {
                    $bearerToken = "4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0";
                }

                $thread_id = $exists->thread_id;
                Log::info("Here");
                $message_count = $this->get_no_of_thread_messages($thread_id, $api_key);
                $is_awake = $this->sendWakeTimeRequest($phone, $sending_number, $bearerToken)['wake_time'];
                Log::info($is_awake);
                if ($is_awake) {
                    if (Carbon::now()->lessThan($is_awake)) {
                        // Still sleeping
                        Log::info("The assistant is sleeping");
                        return;
                    }
                } else {
                    Log::info("assistant is active");
                }

                Log::info("This is the message count $message_count");
                if ($assistant->maximum_messages <= $message_count) {
                    Log::info("Message limit reached");
                    return;
                }
                $this->addMessage($thread_id, $userInput, $api_key);
                $threadRun = $this->RunThread($thread_id, $assistant_id, $api_key);
                $answer = $this->loadAnswer($threadRun, $api_key);
                if ($answer === $userInput) {
                    Log::info("openAI did not process");
                    return;
                }
                Log::info("The delay is $delay");
                Log::info("Answer is $answer");
                if (stripos($answer, 'Sarcastic reply') !== false) {
                    Log::info("$phone made a sarcastic reply.");
                    return;
                } else if (stripos($answer,  "offensive language") !== false) {
                    Log::info("$phone made a offensive reply");
                    return;
                } 
                else if (stripos($answer,  "“irrelevant reply”") !== false) {
                    Log::info("$phone made an irrelevant reply");
                    return;
                }
                else if (stripos($answer,  "No further action needed. Archive this lead") !== false) {
                    Log::info("$phone made a disinterested reply");
                    return;
                } 
                else if (stripos($answer,  "Out of Topic") !== false) {
                    Log::info("$phone made a Out of Topic reply");
                    return;
                }
                else if (stripos($answer, 'qualified lead') !== false) {
                    Log::info("$phone is a qualified lead");
                    AI_Lead::Create($contactContext);
                    $this->star_lead($phone, $sending_number);
                } else {
                    $delay = $this->checkDateTime(now()->addMinutes($delay));
                    // $delay=now()->addMinutes($delay);
                    Log::info("Will sent on $delay");
                    SendSmsReplyJob::dispatch($phone, $answer, $sending_number)
                        ->delay($delay);
                }


                //$this->sendSmsReply($phone, $answer, $sending_number);

                return response()->json([
                    'status' => 'success',
                    'message' => $answer,
                    'thread_id' => $thread_id,
                ]);
            } else {
                // Create a new thread if none exist
                $thread = $this->createAndRunThread($userInput, $assistant_id, $contactContext, $api_key,$workflow_message);
                // Log::info($thread);
                $data = [
                    'phone' => $phone,
                    'thread_id' => $thread->threadId,
                ];
                Thread::create($data);
                $answer = $this->loadAnswer($thread, $api_key);
                Log::info($answer);
                if ($answer === $userInput) {
                    Log::info("openAI did not process");
                    return;
                }
                Log::info("The delay is $delay");
                if (stripos($answer, 'Sarcastic reply') !== false) {
                    Log::info("$phone made a sarcastic reply.");
                    return;
                } else if (stripos($answer,  "offensive language") !== false) {
                    Log::info("$phone made a offensive reply");
                    return;
                } 
                else if (stripos($answer,  "Out of Topic") !== false) {
                    Log::info("$phone made a Out of Topic reply");
                    return;
                }
                else if (stripos($answer,  "“irrelevant reply”") !== false) {
                    Log::info("$phone made an irrelevant reply");
                    return;
                }
                else if (stripos($answer,  "No further action needed. Archive this lead") !== false) {
                    Log::info("$phone made a disinterested reply");
                    return;
                } else if (stripos($answer, 'qualified lead') !== false) {
                    Log::info("$phone is a qualified lead");
                    AI_Lead::Create($contactContext);
                    $this->star_lead($phone, $sending_number);
                } else {
                    $delay=now()->addMinutes($delay);
                    //$delay = $this->checkDateTime(now()->addMinutes($delay));
                    SendSmsReplyJob::dispatch($phone, $answer, $sending_number)
                        ->delay($delay);
                }


                // $this->sendSmsReply($phone, $answer, $sending_number);

                return response()->json([
                    'status' => 'success',
                    'message' => $answer,
                    'thread_id' => $thread->threadId,
                ]);
            }
        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    private function get_workflow_message(string $phone)
    {
        // Retrieve the contact based on the phone number
        $contact = DB::table('contacts')->where('phone', $phone)->first();

        // Check if the contact exists
        if (!$contact) {
            return '';
        }

        // Get the current step from the contact
        $current_step = $contact->current_step;

        // Find the corresponding step
        $step = Step::find($current_step);

        // Check if the step exists
        if (!$step) {
            return '';
        }

        // Get the content of the step
        $content = $step->content;
        $workflow = Workflow::find($step->workflow_id);
        $contact_info = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
        $content = $this->composeMessage($contact_info, $content);
        // Return the content in the response
        return $content;
    }

    /**
     * Create and run a thread with OpenAI
     *
     * @param string $question The user's question.
     * @param string $assistant_id The ID of the assistant.
     * @param array $contactContext The context of the homeowner and their property.
     * @return ThreadRunResponse
     * @throws Exception If the API call fails.
     */
    private function createAndRunThread(string $question, string $assistant_id, array $contactContext, string $api_key,string $workflow_message): ThreadRunResponse
    {
        // Ensure the script doesn't timeout
        set_time_limit(0);

        // Log the contact context for debugging
        Log::info('Contact Context: ', $contactContext);
        Log::info("The workflow message is $workflow_message");
        $client = OpenAI::client($api_key);
        try {
            // Create and run the thread with OpenAI
            return $client->threads()->createAndRun([
                'assistant_id' => $assistant_id,
                'thread' => [
                    'messages' => [
                        [
                            'role' => 'assistant',
                            'content' => 'Here is the homeowner information and their property information: ' . json_encode($contactContext, JSON_PRETTY_PRINT),
                        ],
                        [
                            'role' => 'assistant',
                            'content' => 'This is the workflow message that we sent to the homeowner. use it to determine if the homeowner reply is out of topic. The message is'.$workflow_message.'if the reply is out of topic strictly reply with."Out of Topic".',
                        ],
                        [
                            'role' => 'user',
                            'content' => $question,
                        ],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Failed to create and run thread: ' . $e->getMessage());

            // Optionally, rethrow the exception or handle it
            throw new Exception('Unable to create and run thread. Please check the logs for more details.');
        }
    }

    public function qualify()
    {
        Log::info("I qualified a lead");
    }
    public function get_wake_time($id)
    {
        try {
            // Fetch the assistant record
            $assistant = Assistant::where('openAI_id', $id)->first();

            // Check if the assistant exists
            if (!$assistant) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Assistant not found',
                ], 404);
            }

            // Convert sleep time to minutes
            $sleep_time = (int) $this->convertToMinutes($assistant->sleep_time, $assistant->sleep_time_units);

            // Calculate wake time
            $wake_time = Carbon::now()->addMinutes($sleep_time);

            // Return JSON response
            return response()->json([
                'status' => 'success',
                'wake_time' => $wake_time->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            // Handle errors and return a JSON error response
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    private function addMessage($thread_id, $user_response, $api_key)
    {
        // Log the input data for the message creation process
        Log::info("Attempting to add message to thread", [
            'thread_id' => $thread_id,
            'user_response' => $user_response, // Sensitive content might be logged carefully
            'api_key' => $api_key ? '****' : null // Hiding the API key for security
        ]);

        $client = OpenAI::client($api_key);

        try {
            // Log that we are calling the OpenAI API to add the message
            Log::info("Calling OpenAI API to add message to the thread", [
                'thread_id' => $thread_id,
            ]);

            $response = $client->threads()->messages()->create($thread_id, [
                'role' => 'user',
                'content' => $user_response,
            ]);

            // Log successful message creation
            Log::info("Message added successfully to thread", [
                'thread_id' => $thread_id,
                'message_content' => $user_response, // Optionally log content
            ]);

            return $response;
        } catch (\Exception $e) {
            // Log the exception error details
            Log::error("Error adding message to the thread", [
                'error' => $e->getMessage(),
                'thread_id' => $thread_id,
                'user_response' => $user_response, // Log the input message, be cautious with sensitive data
            ]);

            // Rethrow the exception with a custom message
            throw new \RuntimeException("Failed to add message to the thread", 0, $e);
        }
    }

    private function RunThread($thread_id, $assistant_id, $api_key): ThreadRunResponse
    {
        // Log the input data for the thread run process
        Log::info("Attempting to run thread", [
            'thread_id' => $thread_id,
            'assistant_id' => $assistant_id,
            'api_key' => $api_key ? '****' : null // Hide API key for security
        ]);

        set_time_limit(0);
        $client = OpenAI::client($api_key);

        try {
            // Log that we are calling the OpenAI API to run the thread
            Log::info("Calling OpenAI API to run the thread", [
                'thread_id' => $thread_id,
                'assistant_id' => $assistant_id,
            ]);

            $response = $client->threadRuns()->create(
                $thread_id,
                [
                    'assistant_id' => $assistant_id,
                ]
            );

            // Log successful thread run initiation
            Log::info("Thread run initiated successfully", [
                'thread_id' => $thread_id,
                'assistant_id' => $assistant_id,
            ]);

            return $response;
        } catch (\Exception $e) {
            // Log the exception error details
            Log::error("Error running the thread", [
                'error' => $e->getMessage(),
                'thread_id' => $thread_id,
                'assistant_id' => $assistant_id,
            ]);

            // Rethrow the exception with a custom message
            throw new \RuntimeException("Failed to run the thread", 0, $e);
        }
    }




    private function loadAnswer(ThreadRunResponse $threadRun, string $api_key)
    {
        // Log the initial attempt to load the answer
        Log::info("Attempting to load answer for thread run", [
            'thread_id' => $threadRun->threadId,
            'run_id' => $threadRun->id,
            'api_key' => $api_key ? '****' : null // Mask the API key for security
        ]);

        set_time_limit(0);
        $client = OpenAI::client($api_key);

        // Loop to check the status of the thread run until it is no longer 'queued' or 'in_progress'
        while (in_array($threadRun->status, ['queued', 'in_progress'])) {
            // Log the status of the thread on each iteration
            Log::info("Checking thread status", [
                'thread_id' => $threadRun->threadId,
                'run_id' => $threadRun->id,
                'status' => $threadRun->status
            ]);

            // Retrieve the updated status of the thread run
            try {
                $threadRun = $client->threads()->runs()->retrieve(
                    threadId: $threadRun->threadId,
                    runId: $threadRun->id,
                );
            } catch (\Exception $e) {
                // Log the error if retrieval fails
                Log::error("Error retrieving thread run status", [
                    'error' => $e->getMessage(),
                    'thread_id' => $threadRun->threadId,
                    'run_id' => $threadRun->id
                ]);
                return null; // Return null if retrieval fails
            }
        }

        // Check if the thread run has completed successfully
        if ($threadRun->status !== 'completed') {
            // Log the failure if the thread did not complete successfully
            Log::error("Thread run did not complete successfully", [
                'thread_id' => $threadRun->threadId,
                'run_id' => $threadRun->id,
                'status' => $threadRun->status,
                'error_message' => isset($threadRun->error) ? $threadRun->error : 'Unknown error' // Log error message if available
            ]);

            // Set the error message
            $this->error = 'Request failed, please try again';
            return null; // Optionally, return null or some default value
        }

        // Log that the thread run has completed successfully
        Log::info("Thread run completed successfully", [
            'thread_id' => $threadRun->threadId,
            'run_id' => $threadRun->id,
            'status' => $threadRun->status
        ]);

        // Retrieve the list of messages from the thread
        try {
            $messageList = $client->threads()->messages()->list(
                threadId: $threadRun->threadId,
            );
        } catch (\Exception $e) {
            // Log the error if message retrieval fails
            Log::error("Error retrieving message list", [
                'error' => $e->getMessage(),
                'thread_id' => $threadRun->threadId
            ]);
            return null; // Return null if message retrieval fails
        }

        // Log the message content before returning
        Log::info("Message content retrieved successfully", [
            'thread_id' => $threadRun->threadId,
            'message_content' => $messageList->data[0]->content[0]->text->value
        ]);

        // Return the text value of the first message
        return $messageList->data[0]->content[0]->text->value;
    }


    private function star_lead(string $phone, string $sending_number): array
    {
        $endpoint = 'https://godspeedoffers.com/api/v3/sms/star-lead';

        try {
            // Log the start of the SMS reply process
            Log::info('Starting starring process', [
                'endpoint' => $endpoint,
                'phone'    => $phone,
                'sending_number' => $sending_number
            ]);

            // Make the API request
            $response = Http::withToken('4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0')
                ->post($endpoint, [
                    'phone'   => $phone,
                    'sending_number' => $sending_number

                ]);

            // Log the response from the API
            Log::info('starred the lead', [
                'status' => $response->status(),
                'success' => $response->successful(),
                'data' => $response->json(),
            ]);

            // Return the response as an array
            return [
                'status'  => $response->status(),
                'success' => $response->successful(),
                'data'    => $response->json(),
            ];
        } catch (\Exception $e) {
            // Log the exception details
            Log::error('Exception occurred in starring process', [
                'phone'    => $phone,
                'error'    => $e->getMessage(),
            ]);

            // Handle exceptions and return an error response
            return [
                'status'  => 500,
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
    public function all_assistants()
    {
        // Fetch all assistants from the database
        $assistants = Assistant::all();

        // Return the data in JSON format
        return response()->json([
            'data' => $assistants,
        ]);
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
    private function calculateWaitTime($min_wait_time, $max_wait_time)
    {
        // Validate that min_wait_time is less than or equal to max_wait_time
        if ($min_wait_time > $max_wait_time) {
            throw new Exception("Minimum wait time cannot be greater than maximum wait time.");
        }

        // Generate a random number between min_wait_time and max_wait_time
        $wait_time = random_int($min_wait_time, $max_wait_time);

        return $wait_time;
    }

    private function checkDateTime($dateTime)
    {
        // Parse the input datetime
        $carbonDate = Carbon::parse($dateTime);
        logger("Input DateTime: {$dateTime}");
        logger("Parsed Carbon DateTime: {$carbonDate}");
    
        // Define Monday as the start of the week
        $carbonDate->settings([
            'week_starts_at' => Carbon::MONDAY,
        ]);
    
        // Define Monday to Saturday range
        $startOfWeek = $carbonDate->copy()->startOfWeek(); // Monday at 00:00
        $endOfWeek = $startOfWeek->copy()->addDays(5)->endOfDay(); // Saturday at 23:59
        logger("Start of Week (Monday 00:00): {$startOfWeek}");
        logger("End of Week (Saturday 23:59): {$endOfWeek}");
    
        // Check if the date is between Monday and Saturday
        if ($carbonDate->between($startOfWeek, $endOfWeek)) {
            logger("Date is within Monday to Saturday range.");
    
            // Check if the time is between 9 AM and 8 PM
            $validStartTime = $carbonDate->copy()->setTime(9, 0);
            $validEndTime = $carbonDate->copy()->setTime(20, 0);
            logger("Valid Start Time (9 AM): {$validStartTime}");
            logger("Valid End Time (8 PM): {$validEndTime}");
    
            if (!$carbonDate->between($validStartTime, $validEndTime)) {
                logger("Time is outside the valid range. Adjusting to next valid time.");
                // Adjust to the next valid time in the range
                $carbonDate = $carbonDate->copy()->setTime(9, 0)->addDay();
                logger("Adjusted DateTime: {$carbonDate}");
            } else {
                logger("Time is within the valid range.");
            }
    
            return $carbonDate; // Return the adjusted date/time
        }
    
        // If the day is not between Monday and Saturday, move to next Monday at 9 AM
        logger("Date is outside Monday to Saturday range. Adjusting to next Monday at 9 AM.");
        $adjustedDate = $carbonDate->next(Carbon::MONDAY)->setTime(9, 0);
        logger("Adjusted DateTime for Next Monday: {$adjustedDate}");
    
        return $adjustedDate;
    }


    private function get_no_of_thread_messages(string $threadId, string $api_key): int
    {
        set_time_limit(0);
        Log::info("getting no of threads");
        $client = OpenAI::client($api_key);

        // Retrieve all messages in the thread
        $messageList = $client->threads()->messages()->list(
            threadId: $threadId,
        );

        // Filter messages with the role of 'assistant'
        $assistantResponses = array_filter(
            $messageList->data,
            fn($message) => $message->role === 'assistant'
        );

        // Count the number of assistant responses
        $assistantResponseCount = count($assistantResponses);

        // Optionally log the count for debugging or monitoring
        Log::info("Thread ID: $threadId has $assistantResponseCount assistant responses.");

        // Return the count of assistant messages
        return $assistantResponseCount;
    }
    private function containsKeywords(string $text): bool
    {
        $keywords = [
            "The property is not for sale",
            "not interested",
            "no",
            "not selling",
            "I'm not interested in selling",
            "Wrong number.",
            "I already sold it",
            "Stop contacting me",
            "Take me off your list",
            "Scam",
            "Scammer",
            "i'm a realtor",
            "my wife is a realtor",
            "my husband is a realtor",
            "im a real estate broker",
            "reported",
            "Where did you get my name",
            "Stop harassing me",
            "Stop",
            "Wrong number",
            "i rent",
            "im renting",
            "i am renting",
            "I don't own it",
            "haunted",
            'scary',
            'bite me',
            'bite'
        ];

        foreach ($keywords as $keyword) {
            if (Str::contains(strtolower($text), strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }
    private function containsEmoji($string) {
        // Regex pattern to match emojis
        $emojiPattern = '/[\x{1F600}-\x{1F64F}|\x{1F300}-\x{1F5FF}|\x{1F680}-\x{1F6FF}|\x{1F1E0}-\x{1F1FF}|\x{2600}-\x{26FF}|\x{2700}-\x{27BF}]/u';
    
        // Check for emojis in the string
        return preg_match($emojiPattern, $string) ? true : false;
    }
    
    
    private function sendWakeTimeRequest($phone, $sending_number, $bearerToken)
    {
        try {
            // Send the POST request with phone, sending_number, and Bearer token in the headers
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $bearerToken, // Set Bearer token
            ])->post('https://godspeedoffers.com/api/v3/sms/wake-time', [
                'phone' => $phone,
                'sending_number' => $sending_number,
            ]);

            // Check if the request was successful
            if ($response->successful()) {
                // Return the response body or specific data you need
                return $response->json(); // Return the JSON response
            } else {
                // Log error details if the response is not successful
                Log::error("Failed to send wake time request", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'phone' => $phone,
                    'sending_number' => $sending_number,
                ]);
                return null; // Return null if the request failed
            }
        } catch (\Exception $e) {
            // Handle exceptions and log the error
            Log::error("Error sending wake time request", [
                'error' => $e->getMessage(),
                'phone' => $phone,
                'sending_number' => $sending_number,
            ]);
            return null; // Return null if there was an exception
        }
    }
}
