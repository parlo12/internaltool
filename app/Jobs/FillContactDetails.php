<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Workflow;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FillContactDetails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function handle()
    {
        // Get the workflow information (you may want to fetch it based on the contact's workflow_id)
        $workflow = Workflow::find($this->contact->workflow_id); // Assuming the Contact model has a workflow relationship

        // Get contact information using the provided method
        $contact_info = $this->get_contact($this->contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
        Log::info("contact info",$contact_info);
        // Extract the custom fields for city, state, and zipcode
        $zipcode = $contact_info['custom_fields']['ZIPCODE'] ?? null;
        $city = $contact_info['custom_fields']['CITY'] ?? null;
        $state = $contact_info['custom_fields']['STATE'] ?? null;
        $offer = $contact_info['custom_fields']['OFFER_AMOUNT'] ?? null;
        $address = $contact_info['custom_fields']['ADDRESS'] ?? null;
        $sales_person = $contact_info['custom_fields']['SALES_PERSON'] ?? null;
        $email = $contact_info['custom_fields']['EMAIL'] ?? null;
        $gender = $contact_info['custom_fields']['Gender'] ?? null;
        $age = $contact_info['custom_fields']['AGE'] ?? null;
        $lead_score = $contact_info['custom_fields']['LEAD_SCORE'] ?? null;
        $novation = $contact_info['custom_fields']['NOVATION'] ?? null;
        $creative_price = $contact_info['custom_fields']['CREATIVEPRICE'] ?? null;
        $down_payment = $contact_info['custom_fields']['DOWNPAYMENT'] ?? null;
        $monthly = $contact_info['custom_fields']['MONTHLY'] ?? null;


        // Update the contact record
        $this->contact->update([
            'zipcode' => $zipcode,
            'city' => $city,
            'state' => $state,
            'offer'=>$offer,
            'address'=>$address,
            'agent'=>$sales_person,
            'email'=>$email,
            'lead_score'=>$lead_score,
            'gender'=>$gender,
            'age'=>$age,
            'novation'=>$novation,
            'creative_price'=>$creative_price,
            'monthly'=>$monthly,
            'downpayment'=>$down_payment
        ]);
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
}
