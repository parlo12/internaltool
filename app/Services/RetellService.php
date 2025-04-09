<?php
// app/Services/RetellService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RetellService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.retellai.com/v1';

    public function __construct()
    {
        $this->apiKey = env('RETELL_API_KEY'); // Directly using env()

        if (empty($this->apiKey)) {
            throw new \RuntimeException('Retell API key not configured');
        }
    }

    public function getRecentCalls($minutes = 30, $limit = 50)
    {
        \Log::info("Retrieving recent calls", ['minutes' => $minutes, 'limit' => $limit]);
    
        $apiKey = env('RETELL_API_KEY');
        if (empty($apiKey)) {
            \Log::error('Retell API key not configured');
            throw new \RuntimeException('Retell API key not configured in .env');
        }
    
        try {
            // Calculate timestamp thresholds (in milliseconds)
            $now = now()->getTimestamp() * 1000;
            $lowerThreshold = $now - ($minutes * 60 * 1000);
            
            \Log::debug("Timestamp thresholds calculated", [
                'lower_threshold' => $lowerThreshold,
                'upper_threshold' => $now,
                'human_readable_lower' => date('Y-m-d H:i:s', $lowerThreshold/1000),
                'human_readable_upper' => date('Y-m-d H:i:s', $now/1000)
            ]);
    
            $requestPayload = [
                'sort_order' => 'descending',
                'limit' => $limit,
                'filter_criteria' => [
                    'start_timestamp' => [
                        'lower_threshold' => $lowerThreshold,
                        'upper_threshold' => $now
                    ],
                    'call_status' => ['ended'] // Only completed calls
                ]
            ];
    
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.retellai.com/v2/list-calls",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($requestPayload),
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer $apiKey",
                    "Content-Type: application/json",
                    "Accept: application/json"
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            ]);
    
            \Log::debug("API request prepared", ['payload' => $requestPayload]);
            $startTime = microtime(true);
    
            $response = curl_exec($curl);
            $err = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $duration = round((microtime(true) - $startTime) * 1000, 2);
    
            curl_close($curl);
    
            \Log::debug("API response received", [
                'status_code' => $httpCode,
                'duration_ms' => $duration,
                'response_size' => strlen($response)
            ]);
    
            if ($err) {
                \Log::error("API request failed", ['error' => $err]);
                throw new \RuntimeException("API connection failed: $err");
            }
    
            // Validate response
            if (empty($response)) {
                \Log::warning("Empty API response received");
                return [];
            }
    
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \Log::error("Invalid JSON response", [
                    'error' => json_last_error_msg(),
                    'response_sample' => substr($response, 0, 200)
                ]);
                throw new \RuntimeException("Invalid API response format");
            }
    
            // Handle API errors
            if ($httpCode >= 400) {
                $errorMsg = $data['message'] ?? 'Unknown API error';
                \Log::error("API returned error", [
                    'status_code' => $httpCode,
                    'error' => $errorMsg
                ]);
                throw new \RuntimeException("API Error ($httpCode): $errorMsg");
            }
    
            // Process successful response
            $calls = $data['calls'] ?? $data; // Handle both wrapped and direct array responses
            $callCount = count($calls);
    
            \Log::info("Calls retrieved successfully", [
                'count' => $callCount,
                'duration_ms' => $duration
            ]);
    
            if ($callCount > 0) {
                \Log::debug("Sample call data", [
                    'first_call' => $calls[0]['call_id'] ?? null,
                    'last_call' => $calls[$callCount-1]['call_id'] ?? null,
                    'time_range' => [
                        'oldest_call_start' => isset($calls[$callCount-1]['start_timestamp']) 
                            ? date('Y-m-d H:i:s', $calls[$callCount-1]['start_timestamp']/1000)
                            : null,
                        'newest_call_start' => isset($calls[0]['start_timestamp'])
                            ? date('Y-m-d H:i:s', $calls[0]['start_timestamp']/1000)
                            : null
                    ]
                ]);
            }
    
            return $calls;
    
        } catch (\Exception $e) {
            \Log::error("Failed to retrieve calls", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException("Failed to retrieve recent calls: " . $e->getMessage());
        }
    }
    public function getAllCalls($limit = 100)
    {
        $apiKey = env('RETELL_API_KEY');
        if (empty($apiKey)) {
            throw new \RuntimeException('API key not configured in .env');
        }

        // Initialize logging
        $logFile = storage_path('logs/retell_api_calls.log');
        file_put_contents($logFile, "\n\n=== New Request at " . now() . " ===\n", FILE_APPEND);

        $curl = curl_init();
        $requestPayload = [
            'sort_order' => 'descending',
            'limit' => $limit,
            'filter_criteria' => [
                'call_status' => ['ended']
            ]
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.retellai.com/v2/list-calls",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($requestPayload),
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $apiKey",
                "Content-Type: application/json",
                "Accept: application/json"
            ],
            CURLOPT_VERBOSE => true,
            CURLOPT_STDERR => fopen($logFile, 'a')
        ]);

        file_put_contents($logFile, "Request Payload: " . json_encode($requestPayload, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        file_put_contents($logFile, "HTTP Status: $httpCode\n", FILE_APPEND);
        file_put_contents($logFile, "Raw Response: " . print_r($response, true) . "\n", FILE_APPEND);

        curl_close($curl);

        if ($err) {
            file_put_contents($logFile, "cURL Error: $err\n", FILE_APPEND);
            throw new \RuntimeException("API connection failed: $err");
        }

        if (empty($response)) {
            file_put_contents($logFile, "Empty response from API\n", FILE_APPEND);
            throw new \RuntimeException("Empty API response");
        }

        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            file_put_contents($logFile, "JSON Error: " . json_last_error_msg() . "\n", FILE_APPEND);
            throw new \RuntimeException("Invalid API response format: " . json_last_error_msg());
        }

        file_put_contents($logFile, "Decoded Data: " . print_r($data, true) . "\n", FILE_APPEND);

        if ($httpCode >= 400) {
            $errorMsg = $data['message'] ?? (is_string($data)) ? $data : json_encode($data);
            file_put_contents($logFile, "API Error: $errorMsg\n", FILE_APPEND);
            throw new \RuntimeException("API Error ($httpCode): $errorMsg");
        }

        if (!is_array($data)) {
            file_put_contents($logFile, "Invalid data format, expected array\n", FILE_APPEND);
            throw new \RuntimeException("Unexpected API response format. Expected array.");
        }

        if (empty($data)) {
            file_put_contents($logFile, "No calls found in response\n", FILE_APPEND);
            return [];
        }
        return $data;

        // $formattedCalls = [];
        // foreach ($data as $call) {
        //     try {
        //         $duration = isset($call['start_timestamp'], $call['end_timestamp'])
        //             ? round(($call['end_timestamp'] - $call['start_timestamp']) / 1000)
        //             : 0;

        //         $formattedCalls[] = [
        //             'Call ID' => $call['call_id'] ?? null,
        //             'Type' => $call['call_type'] ?? null,
        //             'Call Duration' => $duration > 0 ? "$duration seconds" : 'N/A',
        //             'Cost' => isset($call['call_cost']['combined_cost'])
        //                 ? '$' . number_format($call['call_cost']['combined_cost'] / 100, 2)
        //                 : 'N/A',
        //             'Disconnection Reason' => $call['disconnection_reason'] ?? null,
        //             'Call Status' => $call['call_status'] ?? null,
        //             'User Sentiment' => $call['call_analysis']['user_sentiment'] ?? null,
        //             'From' => $call['from_number'] ?? $call['from'] ?? null, // Try different field names
        //             'To' => $call['to_number'] ?? $call['to'] ?? null,
        //             'Call Successful' => $call['call_analysis']['call_successful'] ?? null,
        //             'End to End Latency' => isset($call['latency']['e2e']['p50'])
        //                 ? $call['latency']['e2e']['p50'] . 'ms'
        //                 : 'N/A',
        //             'detailed_call_summary' => $call['call_analysis']['call_summary'] ?? null,
        //             '_qualified_lead' => $call['call_analysis']['custom_analysis_data']['_qualified_lead'] ?? null
        //         ];
        //     } catch (\Exception $e) {
        //         file_put_contents($logFile, "Error processing call: " . $e->getMessage() . "\n", FILE_APPEND);
        //         continue;
        //     }
        // }

        file_put_contents($logFile, "Formatted Results: " . print_r($formattedCalls, true) . "\n", FILE_APPEND);
        return $formattedCalls;
    }
}
