<?php

namespace App\Http\Controllers;

use App\Models\AICall;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AICallController extends Controller
{
    public function handleEndOfCallWebhook(Request $request)
    {
        try {
            $webhookData = $request->all();
            Log::info("Webhook received", ['event' => $webhookData['event'] ?? 'unknown']);
            $callData = $webhookData['call'] ?? [];
            if ($callData['direction'] == "outbound") {
                $sending_number = ltrim($callData['from_number'], '+');
                $phone = ltrim($callData['to_number'], '+');
            } else {
                $sending_number = ltrim($callData['to_number'], '+');
                $phone = ltrim($callData['from_number'], '+');
            }

            if (($webhookData['event'] ?? null) == 'call_ended') {
                $contact = Contact::where('phone', $phone)->first();
                if ($contact) {
                    $ai_call = AICall::create([
                        'name' => $contact->contact_name,
                        'contact_id' => $contact->id,
                        'contact_communication_id' => null,
                        'organisation_id' => $contact->organisation_id,
                        'zipcode' => $contact->zipcode,
                        'state' => $contact->state,
                        'city' => $contact->city,
                        'marketing_channel' => 'SMS',
                        'sending_number' => $sending_number,
                        'user_id' => $contact->user_id,
                        'response' => 'Yes',
                        'cost' => $callData['cost'] ?? 0,
                    ]);
                }
            }
            if (($webhookData['event'] ?? null) !== 'call_analyzed') {
                Log::info("Skipping non-call_analyzed event");
                return response()->json(['status' => 'ignored'], 200);
            }
            $callData = $webhookData['call'] ?? [];
            if (empty($callData['call_id']) || empty($callData['transcript'])) {
                throw new \RuntimeException("Missing required call data");
            }

            $note = "Call Summary: " .
                ($callData['call_analysis']['custom_analysis_data']['detailed_call_summary'] ?? 'N/A') .
                "\nQualified Lead: " .
                ($callData['call_analysis']['custom_analysis_data']['_qualified_lead'] ? 'Qualified' : 'Not Qualified') .
                "\nSentiment: " .
                ($callData['call_analysis']['user_sentiment'] ?? 'N/A');
            Log::info("Note generated", ['note' => $note]);
            $this->sendAiCallSummary($phone, $sending_number, $callData['transcript'], $note,);
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
    private function sendAiCallSummary($phone, $sending_number, $message, $note)
    {
        $startTime = microtime(true);

        Log::info('Starting AI Call Summary API call', [
            'phone' => $phone,
            'sending_number' => $sending_number,
            'message_length' => strlen($message),
            'note_length' => strlen($note),
            'initiated_at' => now()->toDateTimeString()
        ]);

        try {
            $token = '4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0';
            $url = 'https://crmstaging.godspeedoffers.com/api/v3/sms/ai-call-summary';

            Log::debug('Preparing API request', [
                'endpoint' => $url,
                'token_truncated' => substr($token, 0, 5) . '...' // Log partial token for security
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])
                ->timeout(30) // Set timeout
                ->post($url, [
                    'phone' => $phone,
                    'sending_number' => $sending_number,
                    'message' => $message,
                    'note' => $note,
                ]);

            $duration = round((microtime(true) - $startTime) * 1000, 2); // ms

            if ($response->successful()) {
                $data = $response->json();

                Log::info('AI Call Summary API call succeeded', [
                    'status_code' => $response->status(),
                    'response' => $data,
                    'duration_ms' => $duration
                ]);

                return $data;
            } else {
                $error = $response->json();

                Log::error('AI Call Summary API call failed', [
                    'status_code' => $response->status(),
                    'error' => $error,
                    'duration_ms' => $duration,
                    'request_payload' => [ // Redacted sensitive data
                        'phone' => '***' . substr($phone, -4),
                        'sending_number' => '***' . substr($sending_number, -4),
                        'message_length' => strlen($message),
                        'note_length' => strlen($note)
                    ]
                ]);

                throw new \Exception("API call failed: " . ($error['message'] ?? 'Unknown error'));
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::critical('API connection failed', [
                'error' => $e->getMessage(),
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);
            throw new \Exception("Connection to API failed: " . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Unexpected error in AI Call Summary', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
    /**
     * Handle inbound Retell calls without signature verification
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleInboundRetellCall(Request $request)
    {
        try {
            // Log incoming request
            Log::channel('retell')->info('Inbound call received', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'full_payload' => $request->all(),
                'timestamp' => now()->toDateTimeString()
            ]);
    
            // Validate request
            $validated = $request->validate([
                'event' => 'required|string|in:call_inbound',
                'call_inbound' => 'required|array',
                'call_inbound.from_number' => 'required|string',
                'call_inbound.to_number' => 'required|string',
                'call_inbound.agent_id' => 'sometimes|string|nullable',
            ]);
    
            Log::channel('retell')->debug('Request validated successfully', [
                'from_number' => $validated['call_inbound']['from_number'],
                'to_number' => $validated['call_inbound']['to_number'],
                'agent_id' => $validated['call_inbound']['agent_id'] ?? null
            ]);
    
            // Extract call details
            $fromNumber = $validated['call_inbound']['from_number'];
            $toNumber = $validated['call_inbound']['to_number'];
            $defaultAgentId = $validated['call_inbound']['agent_id'] ?? null;
    
            // Lookup contact
            Log::channel('retell')->info('Attempting contact lookup', [
                'phone_number' => $fromNumber,
                'lookup_time' => now()->toDateTimeString()
            ]);
    
            $contact = Contact::where('phone', $fromNumber)->first();
    
            if ($contact) {
                Log::channel('retell')->info('Contact found', [
                    'contact_id' => $contact->id,
                    'contact_name' => $contact->contact_name,
                    'lookup_duration' => microtime(true) - LARAVEL_START
                ]);
    
                $response = [
                    'call_inbound' => [
                        'dynamic_variables' => [
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
                        ],
                        'metadata' => [
                            'call_direction' => 'inbound',
                            'received_at' => now()->toDateTimeString(),
                            'contact_id' => $contact->id,
                            'is_existing_contact' => true
                        ],
                    ],
                ];
    
                Log::channel('retell')->debug('Response prepared for existing contact', [
                    'dynamic_variables_count' => count($response['call_inbound']['dynamic_variables']),
                    'metadata' => $response['call_inbound']['metadata']
                ]);
            } else {
                Log::channel('retell')->warning('Contact not found', [
                    'phone_number' => $fromNumber,
                    'lookup_duration' => microtime(true) - LARAVEL_START
                ]);
    
                $response = [
                    'call_inbound' => [
                        'dynamic_variables' => [
                            'name' => '',
                            'state' => '',
                            'offer' => '',
                            'address' => '',
                            'gender' => '',
                            'lead_score' => '',
                            'phone' => '',
                            'organisation_id' => '',
                            'novation' => '',
                            'creative_price' => '',
                            'downpayment' => '',
                            'monthly' => '',
                        ],
                        'metadata' => [
                            'call_direction' => 'inbound',
                            'received_at' => now()->toDateTimeString(),
                            'is_existing_contact' => false,
                            'phone_number' => $fromNumber
                        ],
                    ],
                ];
    
                Log::channel('retell')->debug('Response prepared for new contact');
            }
    
            Log::channel('retell')->info('Returning response to Retell', [
                'response_summary' => [
                    'has_contact' => !empty($contact),
                    'variables_count' => count($response['call_inbound']['dynamic_variables'])
                ]
            ]);
    
            return response()->json($response);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('retell')->error('Validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json(['error' => 'Invalid request'], 400);
    
        } catch (\Exception $e) {
            Log::channel('retell')->error('Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
