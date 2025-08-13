<?php

namespace App\Jobs;

use App\Models\Contact;
use App\Models\Workflow;
use App\Services\CRMAPIRequestsService;
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
        $workflow = Workflow::find($this->contact->workflow_id);
        $CRMAPIRequestsService = new CRMAPIRequestsService($workflow->godspeedoffers_api);
        $contact_info = $CRMAPIRequestsService->get_contact($this->contact->uuid, $workflow->group_id);
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
        $generated_message = $contact_info['custom_fields']['GENERATED_MESSAGE'] ?? null;
        $emd = $contact_info['custom_fields']['EARNEST_MONEY_DEPOSIT'] ?? null;
        $sca = $contact_info['custom_fields']['SELLER_CARRY_AMOUNT'] ?? null;
        $upa = $contact_info['custom_fields']['UPFRONT_PAYMENT_AMOUNT'] ?? null;
        $plc = $contact_info['custom_fields']['PRIVATE_LENDER_CONTRIBUTION'] ?? null;
        $list_price = $contact_info['custom_fields']['LIST_PRICE'] ?? null;
        // Update the contact record
        $this->contact->update([
            'zipcode' => $zipcode,
            'city' => $city,
            'state' => $state,
            'offer' => $offer,
            'address' => $address,
            'agent' => $sales_person,
            'email' => $email,
            'lead_score' => $lead_score,
            'gender' => $gender,
            'age' => $age,
            'novation' => $novation,
            'creative_price' => $creative_price,
            'monthly' => $monthly,
            'downpayment' => $down_payment,
            'generated_message' => $generated_message,
            'earnest_money_deposit' => $emd,
            'seller_carry_amount' => $sca,
            'upfront_payment_amount' => $upa,
            'private_lender_contribution' => $plc,
            'list_price' => $list_price,
        ]);
    }
}
