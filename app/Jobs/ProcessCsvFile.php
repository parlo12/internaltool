<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Organisation;
use App\Models\Step;
use App\Models\Workflow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use League\Csv\Reader;
use Illuminate\Support\Str;

class ProcessCsvFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $filePath;
    public int $workflowId;
    public int $folderId;
    public $user;
    public function __construct(string $filePath, int $workflowId, int $folderId, $user)
    {
        $this->filePath = $filePath;
        $this->workflowId = $workflowId;
        $this->folderId = $folderId;
        $this->user = $user;
    }


    public function handle(): void
    {
        // Further processing logic here, e.g., parsing, inserting to DB, analytics...
        Log::info("Processing CSV in job: " . $this->filePath);
        Log::info("Workflow ID: " . $this->workflowId);
        Log::info("Folder ID: " . $this->folderId);
        $file_name = basename($this->filePath);
        $file_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $file_name);
        $file_name = strtolower(trim($file_name, '_'));
        $crm_api = new \App\Services\CRMAPIRequestsService('4|jXPTqiIGVtOSvNDua3TfSlRXLFU4lqWPcPZNgfN3f6bacce0');
        $response = $crm_api->createGroup($file_name);
        $content = json_decode($response->getContent(), true);
        Log::info("API response: " . json_encode($content));
        if ($content['data']['status'] == 'error') {
            Log::error("Error creating group.Retrying with a different name: " . json_encode($content));
            $file_name = $file_name . '_' . Str::random(5);
            $response = $crm_api->createGroup($file_name);
            $content = json_decode($response->getContent(), true);
            Log::info("API response after retry: " . json_encode($content));
            if ($content['data']['status'] == 'error') {
                Log::error("Error creating group after retry: " . json_encode($content));
                return;
            }
        }
        Log::info("Group created successfully: " . $file_name);
        $group_id = $content['data']['data']['uid'] ?? null;
        $old_workflow = Workflow::find($this->workflowId);
        $new_workflow = Workflow::create([
            'name' => $file_name,
            'contact_group' => $file_name,
            'active' => 0,
            'group_id' => $group_id,
            'voice' => $old_workflow->voice,
            'agent_number' => $old_workflow->agent_number,
            'texting_number' => $old_workflow->texting_number,
            'calling_number' => $old_workflow->calling_number,
            'number_pool_id' => $old_workflow->number_pool_id,
            'folder_id' => $old_workflow->folder_id,
            'organisation_id' => $this->user->organisation_id,
            'godspeedoffers_api' => $old_workflow->godspeedoffers_api,
            'generated_message' => $old_workflow->generated_message,
            'user_id' => $this->user->id,
            'folder_id' => $this->folderId,
        ]);


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
                        'days_of_week' => $step_to_copy->days_of_week,
                        'generated_message' => $step_to_copy->generated_message,
                    ]);
                    $new_steps_flow = $new_workflow->steps_flow ? explode(',', $new_workflow->steps_flow) : [];
                    $new_steps_flow[] = $new_step->id;
                    $new_workflow->steps_flow = implode(',', $new_steps_flow);
                    $new_workflow->save();
                } catch (\Exception $e) {
                    Log::error("Error copying step ID {$step_id}: {$e->getMessage()}");
                }
            }
        }
        $path = $this->filePath; // Update with your CSV path
        // Load the CSV
        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0); // First row contains headers

        // Get each row as an associative array (header => value)
        foreach ($csv->getRecords() as $record) {
            $property_address = $record['Property address'] ?? null;
            $Property_city = $record['Property city'] ?? null;
            $Property_state = $record['Property state'] ?? null;
            $Property_zip = $record['Property zip'] ?? null;
            $Owner_name = $record['Owner name'] ?? null;
            $Gender = $record['Gender'] ?? null;
            $Age = $record['Age'] ?? null;
            $Lead_score = $record['Lead score'] ?? null;
            $Cash_offer = $record['Cash offer'] ?? null;
            $Novation_offer = $record['Novation offer'] ?? null;
            $Creative_price_offer = $record['Creative price offer'] ?? null;
            $Monthly_payment_amount = $record['Monthly payment amount'] ?? null;
            $Down_payment_amount = $record['Down payment amount'] ?? null;
            $Phone_number_1 = $record['Phone number 1'] ?? null;
            Log::info("Property address: " . $property_address);
            Log::info("Property city: " . $Property_city);
            Log::info("Property state: " . $Property_state);
            Log::info("Property zip: " . $Property_zip);
            Log::info("Owner name: " . $Owner_name);
            Log::info("Gender: " . $Gender);
            Log::info("Age: " . $Age);
            Log::info("Lead score: " . $Lead_score);
            Log::info("Cash offer: " . $Cash_offer);
            Log::info("Novation offer: " . $Novation_offer);
            Log::info("Creative price offer: " . $Creative_price_offer);
            Log::info("Monthly payment amount: " . $Monthly_payment_amount);
            Log::info("Down payment amount: " . $Down_payment_amount);
            Log::info("Phone number 1: " . $Phone_number_1);
            $contact = Contact::create(
                [
                    'uuid' => Str::uuid(),
                    'workflow_id' => $new_workflow->id,
                    'phone' => $this->normalizePhoneNumber($Phone_number_1),
                    'can_send' => 1,
                    'response' => 'No',
                    'contact_name' => $Owner_name,
                    'status' => 'WAITING_FOR_QUEAUE',
                    'cost' => 0,
                    'subscribed' => 1,
                    'organisation_id' => $this->user->organisation_id,
                    'user_id' => $this->user->id,
                    'zipcode' => $Property_zip,
                    'city' => $Property_city,
                    'state' => $Property_state,
                    'offer' => $Cash_offer,
                    'address' => $property_address,
                    'agent' => $this->user->name,
                    'email' => "",
                    'lead_score' => $Lead_score,
                    'gender' => $Gender,
                    'age' => $Age,
                    'novation' => $Novation_offer,
                    'creative_price' => $Creative_price_offer,
                    'monthly' => $Monthly_payment_amount,
                    'downpayment' => $Down_payment_amount,
                    'generated_message' => ""
                ]
            );
            $crm_api->createContact($group_id, [
                'PHONE' => $this->normalizePhoneNumber($Phone_number_1),
                'FIRST_NAME' => $Owner_name,
                "ADDRESS" => $property_address,
                "CITY" => $Property_city,
                "STATE" => $Property_state,
                "ZIPCODE" => $Property_zip,
                "OFFER_AMOUNT" => $Cash_offer,
                "SALES_PERSON" => $this->user->name,
                "AGE" => $Age,
                "Gender" => $Gender,
                "LEAD_SCORE" => $Lead_score,
                "NOVATION" => $Novation_offer,
                "CREATIVEPRICE" => $Creative_price_offer,
                "MONTHLY" => $Monthly_payment_amount,
                "DOWNPAYMENT" => $Down_payment_amount,
            ]);
        }
    }
    private function normalizePhoneNumber($phone)
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return $digits;
        }
        if (strlen($digits) === 10) {
            return '1' . $digits;
        }
        return null;
    }
}
