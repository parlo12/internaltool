<?php

namespace App\Http\Controllers;

use App\Models\AICall;
use App\Models\Assistant;
use App\Models\CallResponse;
use App\Models\KnowledgeBase;
use App\Models\Organisation;
use App\Models\Thread;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenAI\Responses\Threads\Runs\ThreadRunResponse;
use SignalWire\LaML\MessageResponse;
use Twilio\TwiML\VoiceResponse;
use Ratchet\Client\Connector;
use React\EventLoop\Factory;
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
            'openAI'=>$org->openAI,
            'organisation_id'=>auth()->user()->organisation_id
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
    // Extract data from the incoming webhook
    $call = $request->input('message.call');
    $summary = $request->input('message.summary');

    // Prepare the specific data to return
    $responseData = [
        'call_id' => $call['id'] ?? null,
        'customer_number' => $call['orgId'] ?? null, // Assuming orgId represents the customer number
        'call_summary' => $summary,
    ];
    $call_id =$call['id'];
    $bearerToken = "475d199e-30a7-4d75-832b-d3c1b7794d76";
  Log::info($this->get_call($call_id,$bearerToken));
    // Log the response data
    Log::info('End of Call Response Data:', $responseData);

    // Return the specific data as a JSON response
    return response()->json($responseData, 200);
}
private function get_call(string $id, string $token)
{
    $url = "https://api.vapi.ai/call/{$id}";

    $client = new \GuzzleHttp\Client();

    try {
        $response = $client->request('GET', $url, [
            'headers' => [
                'Authorization' => "Bearer {$token}",
            ],
        ]);

        return json_decode($response->getBody(), true);
    } catch (\GuzzleHttp\Exception\RequestException $e) {
        // Handle exceptions or errors
        return [
            'error' => true,
            'message' => $e->getMessage(),
        ];
    }
}
public function test()
{
    $dateTime="2024-12-29 11:43:03";
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
    // Ensure the script doesn't timeout
    set_time_limit(0);
    $api_key = "";
    $assistant_id = "asst_7qXPmiy2RwA7va56kl4Y3OJD";
    // Log the contact context for debugging
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



}
