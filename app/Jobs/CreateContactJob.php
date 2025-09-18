<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\ContactImportFailure;
use App\Models\ContactImportProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Events\ContactImportProgress as ImportProgressEvent;
use App\Helpers\PhoneHelper;
use App\Models\User;

class CreateContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $contactData;
    protected $workflow_id;
    protected $group_id;


    public function __construct($user_id, array $contactData, $workflow_id, $group_id)
    {
        $this->user_id = $user_id;
        $this->contactData = $contactData;
        $this->workflow_id = $workflow_id;
        $this->group_id = $group_id;
    }

    public function handle()
    {
        try {

            // Validate and format phone number
            [$isValid, $phoneResult] = PhoneHelper::validateAndFormat($this->contactData['phone']);
            if (!$isValid) {
                ContactImportFailure::create([
                    'user_id' => $this->user_id,
                    'error' => $phoneResult ?: 'Invalid phone number',
                    'phone' => $this->contactData['phone'],
                    'contact_name' => $this->contactData['contact_name'] ?? null,
                ]);
                $this->updateProgress(false);
                return;
            }
            // // Skip if phone already exists in this contact group
            // $existingContact = Contact::where('contact_group_id', $this->user_id)
            //     ->where('phone', $phoneResult)
            //     ->first();

            // if ($existingContact) {
            //     ContactImportFailure::create([
            //         'user_id' => $this->user_id,
            //         'error' => 'Duplicate contact',
            //         'phone' => $phoneResult,
            //         'contact_name' => $this->contactData['contact_name'] ?? null,
            //     ]);
            //     $this->updateProgress(false);
            //     return;
            // }
            $user = User::find($this->user_id);
            Contact::create([
                'user_id' => $this->user_id,
                'phone' => $phoneResult,
                'contact_name' => $this->contactData['contact_name'] ?? null,
                'uuid' => Str::uuid(),
                'workflow_id' => $this->workflow_id,
                'can_send' => 1,
                'response' => 'No',
                'status' => 'WAITING_FOR_QUEAUE',
                'cost' => 0,
                'subscribed' => 1,
                'organisation_id' => $user->organisation_id,
                'user_id' => $this->user_id,
                'zipcode' => $this->contactData['zipcode'] ?? null,
                'city' => $this->contactData['city'] ?? null,
                'state' => $this->contactData['state'] ?? null,
                'offer' => $this->contactData['offer'] ?? null,
                'address' => $this->contactData['address'] ?? null,
                'agent' => $this->contactData['agent'] ?? null,
                'email' => $this->contactData['email'] ?? null,
                'lead_score' => $this->contactData['lead_score'] ?? null,
                'gender' => $this->contactData['gender'] ?? null,
                'age' => $this->contactData['age'] ?? null,
                'novation' => $this->contactData['novation'] ?? null,
                'creative_price' => $this->contactData['creative_price'] ?? null,
                'monthly' => $this->contactData['monthly'] ?? null,
                'downpayment' => $this->contactData['downpayment'] ?? null,
                'list_price' => $this->contactData['list_price'] ?? null,
                'earnest_money_deposit' => $this->contactData['earnest_money_deposit'] ?? null,
                'generated_message' => ""
            ]);

            $crm_api = new \App\Services\CRMAPIRequestsService($user->godspeedoffers_api);

            $crm_api->createContact($this->group_id, [
                'PHONE' => $phoneResult,
                'FIRST_NAME' => $this->contactData['contact_name'] ?? null,
                'ADDRESS' => $this->contactData['address'] ?? null,
                'CITY' => $this->contactData['city'] ?? null,
                'STATE' => $this->contactData['state'] ?? null,
                'ZIPCODE' => $this->contactData['zipcode'] ?? null,
                'OFFER_AMOUNT' => $this->contactData['offer'] ?? null,
                'SALES_PERSON' => $this->contactData['agent'] ?? null,
                'AGE' => $this->contactData['age'] ?? null,
                'Gender' => $this->contactData['gender'] ?? null,
                'LEAD_SCORE' => $this->contactData['lead_score'] ?? null,
                'NOVATION' => $this->contactData['novation'] ?? null,
                'CREATIVEPRICE' => $this->contactData['creative_price'] ?? null,
                'MONTHLY' => $this->contactData['monthly'] ?? null,
                'DOWNPAYMENT' => $this->contactData['downpayment'] ?? null,
                'EMAIL' => $this->contactData['email'] ?? null,
                'LIST_PRICE' => $this->contactData['list_price'] ?? null,
                'EARNEST_MONEY_DEPOSIT' => $this->contactData['earnest_money_deposit'] ?? null,
            ]);

            $this->updateProgress(true);
        } catch (\Throwable $e) {
            $this->updateProgress(false, true);
            ContactImportFailure::create([
                'user_id' => $this->user_id,
                'error' => Str::limit($e->getMessage(), 255),
                'phone' => $this->contactData['phone'] ?? null,
                'contact_name' => $this->contactData['contact_name'] ?? null,
            ]);
            Log::error("Failed to create contact for group {$this->user_id}", [
                'error' => $e->getMessage(),
                'contact_data' => $this->contactData,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function updateProgress(bool $wasImported = true, bool $wasFailed = false): void
    {
        $progress = ContactImportProgress::where('user_id', $this->user_id)->latest()->first();

        if (!$progress) return;

        $progress->increment('processed_contacts');

        if ($wasImported) {
            $progress->increment('imported_contacts');
        } elseif ($wasFailed) {
            $progress->increment('failed_contacts');
        }

        $processed = $progress->processed_contacts;
        $total = $progress->total_contacts;

        $percent = min(round(($processed / $total) * 100), 100);
        Log::info("Progress: $percent% for group {$this->user_id}");

        $cacheKey = "import_progress_{$this->user_id}_last_percent";
        $lastPercent = cache()->get($cacheKey);

        $shouldBroadcast = false;

        if ($total < 100) {
            sleep(1); // Ensure frontend has time to catch up
            $shouldBroadcast = true;
        } elseif ($percent !== $lastPercent) {
            if ($total < 1000 && $percent % 2 === 0) {
                $shouldBroadcast = true;
            } elseif ($total < 10000 && $percent % 5 === 0) {
                $shouldBroadcast = true;
            } elseif ($total < 100000 && $percent % 10 === 0) {
                $shouldBroadcast = true;
            } elseif ($total < 1000000 && $percent % 20 === 0) {
                $shouldBroadcast = true;
            }
        }

        // Always broadcast final 100% if import is complete
        if ($processed >= $total) {
            $percent = 100;
            $shouldBroadcast = true;
        }

        if ($shouldBroadcast) {
            broadcast(new ImportProgressEvent($this->user_id, $percent));
            cache()->put($cacheKey, $percent, 3600); // 1 hour cache
        }
    }
}
