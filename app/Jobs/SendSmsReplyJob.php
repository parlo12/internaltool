<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSmsReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $phone;
    private string $message;
    private string $sendingNumber;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phone, string $message, string $sendingNumber)
    {
        $this->phone = $phone;
        $this->message = $message;
        $this->sendingNumber = $sendingNumber;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $endpoint = 'https://godspeedoffers.com/api/v3/sms/reply';

        try {
           
            $is_awake=$this->sendWakeTimeRequest($this->phone, $this->sendingNumber,'4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0')['wake_time'];
            if($is_awake){
                if (Carbon::now()->lessThan($is_awake)) {
                    // Still sleeping
                    Log::info("The assistant is sleeping. This was a queued reply");
                    return ;
                }
            }else{
                Log::info("assistant is active. This was a queued reply");
            }
            // Make the API request
            $response = Http::withToken('4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0')
                ->post($endpoint, [
                    'phone'   => $this->phone,
                    'message' => $this->message,
                    'sending_number' => $this->sendingNumber,
                ]);

            

        } catch (\Exception $e) {
            // Log the exception details
            Log::error('Exception occurred in sendSmsReply', [
                'phone'   => $this->phone,
                'message' => $this->message,
                'error'   => $e->getMessage(),
            ]);
        }
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
