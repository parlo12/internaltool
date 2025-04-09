<?php

namespace App\Http\Controllers;

use App\Models\Assistant;
use App\Models\Organisation;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;
use Illuminate\Support\Facades\Http;
use OpenAI;
use Carbon\Carbon;

class AISalesPersonController extends Controller
{
    public function index(Request $request)
    {
        $assistants = Assistant::where('organisation_id', auth()->user()->organisation_id)->get();
        return inertia("AI/Index", [
            'success' => session('success'),
            'error' => session('error'),
            'assistants' => $assistants
        ]);
    }
    public function view($id)
    {
        $assistant = Assistant::findOrFail($id);
        return inertia('AI/Show', [
            'assistant' => $assistant,
            'success' => session('success'),
            'error' => session('error')
        ]);
    }
    public function update(Request $request, Assistant $assistant)
    {
        $org = Organisation::find(auth()->user()->organisation_id);
        if (!$org->openAI) {
            return redirect()->route('ai.index')->with('error', 'Make sure your organisation has an OpenAi key provided');
        }
        // Validate incoming data
        $baseDir = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads';
        $request->validate([
            'name' => 'required|string|max:255',
            'prompt' => 'required|string',
            'file1' => 'nullable|file|mimes:pdf,docx,jpg,png',  // Adjust file validation as needed
            'file2' => 'nullable|file|mimes:pdf,docx,jpg,png',  // Adjust file validation as needed
            'sleep_time' => 'required|integer',
            'sleep_time_units' => 'required|string',
            'min_wait_time' => 'required|integer',
            'max_wait_time' => 'required|integer',
            'wait_time_units' => 'required|string',
            'maximum_messages' => 'required|integer',
        ]);

        // Update assistant data
        $assistant->name = $request->input('name');
        $assistant->prompt = $request->input('prompt');
        $assistant->sleep_time = $request->input('sleep_time');
        $assistant->sleep_time_units = $request->input('sleep_time_units');
        $assistant->min_wait_time = $request->input('min_wait_time');
        $assistant->max_wait_time = $request->input('max_wait_time');
        $assistant->wait_time_units = $request->input('wait_time_units');
        $assistant->maximum_messages = $request->input('maximum_messages');
        $assistant->openAI = $org->openAI;
        $assistant->organisation_id = auth()->user()->organisation_id;

        $client = OpenAI::client($org->openAI);

        $client->assistants()->modify($assistant->openAI_id, [
            'name' => $request->input('name'),
            'instructions' =>  $request->input('prompt')
        ]);
        // Handle file1 upload and OpenAI integration
        if ($request->hasFile('file1')) {
            // Delete old file if exists
            if ($assistant->file1) {
                \Storage::delete($assistant->file1);
                $client->files()->delete($assistant->file1_id);
                //$client->assistantsFiles()->delete($assistant->openAI_id, $assistant->file1_id);
            }

            // Store the file locally
            $file1 = $request->file('file1');
            $file1Name = $file1->getClientOriginalName();
            $file1Path = $file1->move($baseDir, $file1Name); // Save the file directly to the custom directory
            $data['file1'] = $file1Name;
            $assistant->file1 = $file1Name;

            // Upload the new file to OpenAI
            $realFilePath = storage_path("app/public/{$file1Path}");

            // Upload the file to OpenAI
            $file1Upload = $client->files()->upload([
                'file' => fopen($file1Path, 'r'),
                'purpose' => 'assistants', // Define the purpose for the file
            ]);

            // Update OpenAI file ID
            $assistant->file1_id = $file1Upload->id;

            // Optionally, associate the file with the assistant on OpenAI
            $parameters = [
                'file_id' => $file1Upload->id
            ];
            //$client->assistantsFiles()->create($assistant->openAI_id, $parameters);  // Associate the file with the assistant
        }

        // Handle file2 upload and OpenAI integration
        if ($request->hasFile('file2')) {
            // Delete old file if exists
            if ($assistant->file2) {
                \Storage::delete($assistant->file2);
                $client->files()->delete($assistant->file2_id);
                //$client->assistantsFiles()->delete($assistant->openAI_id, $assistant->file2_id);
            }

            // Store the file locally
            $file2 = $request->file('file2');
            $file2Name = $file2->getClientOriginalName();
            $file2Path = $file2->move($baseDir, $file2Name); // Save the file directly to the custom directory
            $data['file2'] = $file2Name;
            $assistant->file2 = $file2Path;

            // Upload the new file to OpenAI
            $realFilePath = storage_path("app/public/{$file2Path}");

            // Upload the file to OpenAI
            $file2Upload = $client->files()->upload([
                'file' => fopen($file2Path, 'r'),
                'purpose' => 'assistants', // Define the purpose for the file
            ]);

            // Update OpenAI file ID
            $assistant->file2_id = $file2Upload->id;

            // Optionally, associate the file with the assistant on OpenAI
            $parameters = [
                'file_id' => $file2Upload->id
            ];
            //$client->assistantsFiles()->create($assistant->openAI_id, $parameters);  // Associate the file with the assistant
        }

        // Save the updated assistant to the database
        $assistant->save();

        // Redirect back with a success message
        return redirect()->route('ai.index')->with('success', 'Assistant updated successfully!');
    }


    public function create()
    {
        return inertia('AI/Create', [
            'success' => session('success'),
            'error' => session('error')
        ]);
    }

    public function store(Request $request)
    {
        $baseDir = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads';
        $org = Organisation::find(auth()->user()->organisation_id);
        if (!$org->openAI) {
            return redirect()->route('ai.index')->with('error', 'Make sure your organisation has an OpenAi key provided');
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'prompt' => 'required|string', // The seller description for training
            'file1' => 'nullable|file|mimes:pdf,docx,jpg,png',
            'file2' => 'nullable|file|mimes:pdf,docx,jpg,png',
            'sleep_time' => 'required|integer',
            'sleep_time_units' => 'required|string',
            'min_wait_time' => 'required|integer',
            'max_wait_time' => 'required|integer',
            'wait_time_units' => 'required|string',
            'maximum_messages' => 'required|integer',
        ]);
        $client = OpenAI::client($org->openAI);
        // Create assistant with OpenAI
        $assistant = $client->assistants()->create([
            'name' => $request->input('name'),
            'tools' => [
                [
                    'type' => 'file_search',
                ],
                [
                    'type' => 'function',
                    'function' => [
                        'name' => 'qualified_lead_function',
                        'description' => 'Call this function when you determine that the home seller is a qualified lead',
                        'strict' => false,
                        'parameters' => [
                            'type' => 'object',
                            'properties' => [
                                'phone' => [
                                    'type' => 'string',
                                    'description' => 'The Phone number of the lead',
                                ],
                                'qualified_status' => [
                                    'type' => 'boolean',
                                    'description' => 'True if the lead is qualified, false if not',
                                ],
                                'notes' => [
                                    'type' => 'string',
                                    'description' => 'Notes explaining why the lead is qualified',
                                ],
                            ],
                            'required' => ['phone', 'qualified_status', 'notes'],
                        ],
                    ],
                ],
            ],
            'instructions' => $request->input('prompt'),
            'model' => 'gpt-4-turbo',
        ]);


        // Prepare to save in the database
        $data = [
            'name' => $request->input('name'),
            'prompt' => $request->input('prompt'),
            'openAI_id' => $assistant->id,
            'sleep_time' => $request->input('sleep_time'),
            'sleep_time_units' => $request->input('sleep_time_units'),
            'min_wait_time' => $request->input('min_wait_time'),
            'max_wait_time' => $request->input('max_wait_time'),
            'wait_time_units' => $request->input('wait_time_units'),
            'maximum_messages' => $request->input('maximum_messages'),
            'openAI' => $org->openAI,
            'organisation_id' => auth()->user()->organisation_id
        ];

        // Ensure the directory exists
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        // Handle training file uploads
        if ($request->hasFile('file1')) {
            $file1 = $request->file('file1');
            $file1Name = $file1->getClientOriginalName();
            $file1Path = $file1->move($baseDir, $file1Name); // Save the file directly to the custom directory
            $data['file1'] = $file1Name;

            // Open the file and upload it to OpenAI
            $file1Upload = $client->files()->upload([
                'file' => fopen($file1Path, 'r'), // Open the file for reading
                'purpose' => 'assistants', // Define the purpose for the file
            ]);

            // Save OpenAI file ID in the data array
            $data['file1_id'] = $file1Upload->id;

            // Optionally, associate the file with the assistant
        }

        if ($request->hasFile('file2')) {
            $file2 = $request->file('file2');
            $file2Name = $file2->getClientOriginalName();
            $file2Path = $file2->move($baseDir, $file2Name); // Save the file directly to the custom directory
            $data['file2'] = $file2Name;

            // Open the file and upload it to OpenAI
            $file2Upload = $client->files()->upload([
                'file' => fopen($file2Path, 'r'), // Open the file for reading
                'purpose' => 'assistants', // Define the purpose for the file
            ]);

            // Save OpenAI file ID in the data array
            $data['file2_id'] = $file2Upload->id;

            // Optionally, associate the file with the assistant
            //$client->assistantsFiles()->create($assistant->id, ['file_id' => $file2Upload->id]);
        }

        // Save assistant data to the database
        $assistantRecord = Assistant::create($data);

        return redirect()->route('ai.index')->with('success', 'Assistant created successfully!');
    }



    public function destroy($id)
    {
        //dd("here");
        $org = Organisation::find(auth()->user()->organisation_id);
        if (!$org->openAI) {
            return redirect()->route('ai.index')->with('error', 'Make sure your organisation has an OpenAi key provided');
        }
        $client = OpenAI::client($org->openAI);

        $assistant = Assistant::findOrFail($id);

        $client->assistants()->delete($assistant->openAI_id);
        if ($assistant->file1) {
            \Storage::delete($assistant->file1);
            $client->files()->delete($assistant->file1_id);
        }
        if ($assistant->file2) {
            \Storage::delete($assistant->file2);
            $client->files()->delete($assistant->file2_id);
        }
        // Delete the assistant record
        $assistant->delete();

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Assistant deleted successfully!');
    }
    function makeApiCall()
    {
        // Define the customer object
        $customer = [
            "numberE164CheckEnabled" => true,
            "extension" => null,
            "number" => "+15029109264",
            "sipUri" => null,
            "name" => "John Doe"
        ];

        // Define the assistantId
        $assistantId = "51a05551-797e-46b0-996c-c10e4b0e030e";

        // Your Bearer token
        $bearerToken = "475d199e-30a7-4d75-832b-d3c1b7794d76";
        $phoneId = "3c89be7a-83db-4172-993e-343be9af810f";
        // Make the POST request with authentication
        $response = Http::withToken($bearerToken)
            ->post('https://api.vapi.ai/call', [
                'customer' => $customer,
                'assistantId' => $assistantId,
                'phoneNumberId' => $phoneId
            ]);

        // Handle the response
        if ($response->successful()) {
            return $response->json();
        } else {
            return [
                'error' => true,
                'status' => $response->status(),
                'message' => $response->json(),
            ];
        }
    }

    public function handleEndOfCallWebhook(Request $request)
    {
        try {
            $webhookData = $request->all();
            Log::info("Webhook received", ['event' => $webhookData['event'] ?? 'unknown']);
            if (($webhookData['event'] ?? null) !== 'call_analyzed') {
                Log::info("Skipping non-call_analyzed event");
                return response()->json(['status' => 'ignored'], 200);
            }
            // Extract data from the nested 'call' object
            $callData = $webhookData['call'] ?? [];

            $requiredFields = [
                'call_id' => $callData['call_id'] ?? null,
                'transcript' => $callData['transcript'] ?? null,
                'from_number' => $callData['from_number'] ?? null,
                'to_number' => $callData['to_number'] ?? null,
                'direction' => $callData['direction'] ?? null,
                'call_analysis' => $callData['call_analysis'] ?? null
            ];

            Log::info("Call details", [
                'call_id' => $requiredFields['call_id'],
                'from' => $requiredFields['from_number'],
                'to' => $requiredFields['to_number'],
                'direction' => $requiredFields['direction'],
                'duration' => isset($callData['end_timestamp'], $callData['start_timestamp'])
                    ? round(($callData['end_timestamp'] - $callData['start_timestamp']) / 1000) . 's'
                    : 'N/A',
                'disconnection_reason' => $callData['disconnection_reason'] ?? 'N/A',
                'call_analysis' => $callData['call_analysis'] ?? 'N/A',
                'custom_analysis_data' => $callData['call_analysis']['custom_analysis_data']['detailed_call_summary'] ?? 'N/A',
                'qualified_lead' => $callData['call_analysis']['custom_analysis_data']['_qualified_lead'] ?? 'N/A',
            ]);
            // Validate required fields
            if (empty($requiredFields['call_id']) || empty($requiredFields['transcript'])) {
                throw new \RuntimeException("Missing required call data");
            }
            if ($requiredFields['direction'] == "outbound") {
                $sending_number = $requiredFields['from_number'];
                $phone = $requiredFields['to_number'];
            } else {
                $sending_number = $requiredFields['to_number'];
                $phone = $requiredFields['from_number'];
            }
            $note = "Call Summary: " .
                ($callData['call_analysis']['custom_analysis_data']['detailed_call_summary'] ?? 'N/A') .
                "\nQualified Lead: " .
                ($callData['call_analysis']['custom_analysis_data']['_qualified_lead'] ?? 'N/A');
            $this->sendAiCallSummary($phone, $sending_number, $requiredFields['transcript'], $note,);
            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error("Webhook processing failed", [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    private function processCallReport(string $callId, ?string $transcript, ?array $analysis)
    {
        // Save to database or trigger actions
        // \App\Models\CallReport::create([
        //     'call_id' => $callId,
        //     'transcript' => $transcript,
        //     'sentiment' => $analysis['sentiment'] ?? null,
        //     'summary' => $analysis['summary'] ?? null,
        // ]);

        Log::info("Processed Retell call report: $callId");
    }

    public function test()
    {
        $dateTime = "2024-12-29 11:43:03";
        $th = $this->checkDateTime($dateTime);
        echo $th;
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

    private function createAndRunThread(string $question): ThreadRunResponse
    {
        set_time_limit(0);
        $api_key = "";
        $assistant_id = "asst_7qXPmiy2RwA7va56kl4Y3OJD";

        $client = OpenAI::client($api_key);
        try {
            // Create and run the thread with OpenAI
            return $client->threads()->createAndRun([
                'assistant_id' => $assistant_id,
                'thread' => [
                    'messages' => [
                        [
                            'role' => 'assistant',
                            'content' => 'Here is the homeowner information and their property information: ',
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

    public function handleQualifiedLead(Request $request)
    {
        $leadId = $request->input('lead_id');
        $qualifiedStatus = $request->input('qualified_status');
        $notes = $request->input('notes');

        // Log the qualified lead
        \Log::info("Lead ID: $leadId is qualified.", [
            'qualified_status' => $qualifiedStatus,
            'notes' => $notes
        ]);

        // Perform actions for the qualified lead
        return response()->json(['message' => 'Lead processed successfully'], 200);
    }
    // In a controller
    public function recentCalls()
    {
        try {
            $retell = new \App\Services\RetellService();
            $calls = $retell->getAllCalls();
            // dd($calls);
            if (empty($calls)) {
                echo "No calls found.\n";
                return;
            }

            echo str_repeat("=", 80) . "\n";
            echo "RECENT CALLS (Total: " . count($calls) . ")\n";
            echo str_repeat("=", 80) . "\n\n";

            foreach ($calls as $index => $call) {
                if ($call['call_status'] == "ended") {
                    continue;
                }
                echo "CALL #" . ($index + 1) . "\n";
                echo str_repeat("-", 80) . "\n";

                // Format and display each field
                echo "ID:            " . ($call['call_id'] ?? 'N/A') . "\n";
                echo "Type:          " . ($call['call_type'] ?? 'N/A') . "\n";
                echo "Duration:      " . ($call['duration_ms'] ?? 'N/A') . "\n";
                echo "Cost:          " . ($call['call_cost']['combined_cost'] ?? 'N/A') . "\n";
                echo "Status:        " . ($call['call_status'] ?? 'N/A') . "\n";
                echo "Disconnected:  " . ($call['disconnection_reason'] ?? 'N/A') . "\n";
                echo "From:          " . ($call['from_number'] ?? 'N/A') . "\n";
                echo "To:            " . ($call['to_number'] ?? 'N/A') . "\n";
                echo "Sentiment:     " . ($call['call_analysis']['user_sentiment'] ?? 'N/A') . "\n";
                echo "Call Summary:     " . ($call['call_analysis']['call_summary'] ?? 'N/A') . "\n";
                echo "Successful:    " . ($call['call_analysis']['call_successful'] ?? 'N/A') . "\n";
                echo "detailed call summary:     " . ($call['call_analysis']['custom_analysis_data']['detailed_call_summary'] ?? 'N/A') . "\n";
                echo "Qualified:     " . ($call['call_analysis']['custom_analysis_data']['_qualified_lead'] ?? 'N/A') . "\n";


                echo "\n" . str_repeat("=", 80) . "\n\n";
            }
        } catch (\RuntimeException $e) {
            echo "Error: " . $e->getMessage() . "\n";
            \Log::error("Failed to retrieve calls: " . $e->getMessage());
        }
    }
    private function sendAiCallSummary($phone, $sending_number, $message, $note,)
    {


        // Make the API call
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ',
            '4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0',
            'Accept' => 'application/json',
        ])
            ->post('https://crmstaging.godspeedoffers.com/api/sms/ai-call-summary', [
                'phone' => '+1234567890',
                'sending_number' => '+1987654321',
                'message' => 'AI call summary content here',
                'note' => 'Important follow-up',
                'sending_server_id' => 1,
                // For file upload, you would use:
                // 'file' => fopen('path/to/file.jpg', 'r')
            ]);

        if ($response->successful()) {
            $data = $response->json();
            // Handle success
        } else {
            $error = $response->json();
            // Handle error
        }
    }
}
