<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Number;
use App\Models\Organisation;
use App\Models\SendingServer;
use App\Models\TextSent;
use App\Models\Workflow;
use Exception;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use SignalWire\Rest\Client as SignalWireClient;
use ElephantIO\Client as ElephantClient;

class SMSService
{
    protected $provider;
    protected $config;

    public function __construct($provider = 'twilio')
    {
        $this->provider = $provider;
    }

    public function sendSms($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        try { //You add a new SMS provider from here.
            switch ($this->provider) {
                case 'twilio':
                    return $this->sendWithTwilio($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                case 'signalwire':
                    return $this->sendWithSignalWire($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                case 'websockets-api':
                    return $this->sendWithWebsocketsAPI($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                default:
                    throw new Exception("SMS provider not supported.");
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendWithTwilio($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
            Log::info("Texting service is twilio");
            $workflow = Workflow::find($workflow_id);
            $texting_number=$workflow->texting_number;
            $texting_number=Number::where('phone_number',$texting_number)->first();
            $sending_server=SendingServer::find($texting_number->sending_server_id);
            Log::info($sending_server);
            if($sending_server){//if the number is attached to a sending server
                $sid = $sending_server->twilio_account_sid;
                $token = $sending_server->twilio_auth_token;
            }else{//use the org details
                $sid = $organisation->twilio_texting_account_sid;
                $token = $organisation->twilio_texting_auth_token;
            }
            $texting_number = $workflow->texting_number;
            $twilio = new TwilioClient($sid, $token);
            $message = $twilio->messages->create(
                $phone,
                [
                    'from' => $texting_number,
                    'body' => $content
                ]
            );
            $contact = Contact::find($contact_id);
            if ($contact) {
                $communication_ids_array = [];
                $communication_ids = $contact->contact_communication_ids;
                if (!empty($communication_ids)) {
                    $communication_ids_array = explode(',', $communication_ids);
                }
                $message_sid = $message->sid;
                array_push($communication_ids_array, $message_sid);
                $new_contact_communication_ids = implode(',', $communication_ids_array);
                $contact->contact_communication_ids = $new_contact_communication_ids;
                $contact->save();
                Log::info("Message sent with SID: $message_sid");
                if ($message_sid) {
                    $text_sent = TextSent::create([
                        'name' => $contact->contact_name,
                        'contact_id' => $contact->id,
                        'contact_communication_id' => $message_sid,
                        'organisation_id' => $organisation_id,
                        'marketing_channel' => 'SMS',
                        'sending_number' => $texting_number,
                        'zipcode' => $contact->zipcode,
                        'state' => $contact->state,
                        'city' => $contact->city,
                        'user_id' => $workflow->user_id,
                        'response' => 'No'
                    ]);
                } else {
                    $contact->status = "SMS FAILED";
                    $contact->save();
                }
            } else {
                Log::error("Contact with ID $contact_id not found.");
            }        
    }

    private function sendWithWebsocketsAPI($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $contact = Contact::find($contact_id);
        if ($contact) {
            $workflow = Workflow::find($workflow_id);
            $texting_number = $workflow->texting_number;
            $texting_number=Number::where('phone_number',$texting_number)->first();
            $sending_server=SendingServer::find($texting_number->sending_server_id);
            if($sending_server){//if the number is attached to a sending server
                $api_url = $sending_server->websockets_api_url;
                $auth_token = $sending_server->websockets_auth_token;
                $device_id = $sending_server->websockets_device_id;
            }else{
                $api_url = $organisation->api_url;
                $auth_token = $organisation->auth_token;
                $device_id = $organisation->device_id;
            }
            
            $client = ElephantClient::create('https://coral-app-cazak.ondigitalocean.app/?apiKey=692c2be16f7cb78700c969da90002582');
            $client->connect();
            Log::info('Connected to Websocket API');
            $client->emit('outgoingSMS', [
                'deviceId' => $device_id,
                'receiver' => $phone,
                'content' => $content,
            ]);
            //if ($packet = $client->wait(null, 1)) {
            Log::info("Message sent with websockets to: $phone");
            $text_sent = TextSent::create([
                'name' => $contact->contact_name,
                'contact_id' => $contact->id,
                'contact_communication_id' => 'websockets',
                'organisation_id' => $organisation_id,
                'marketing_channel' => 'SMS',
                'sending_number' => $texting_number,
                'zipcode' => $contact->zipcode,
                'state' => $contact->state,
                'city' => $contact->city,
                'user_id' => $workflow->user_id,
                'response' => 'No',
                'cost' => 0
            ]);
            // }else{
            //     $contact->status = "SMS FAILED";
            //     $contact->save();
            // }
        }
        return ['success' => true, 'provider' => 'signalwire'];
    }
    private function sendWithSignalwire($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $workflow = Workflow::find($workflow_id);
        $organisation = Organisation::find($organisation_id);
        $texting_number = $workflow->texting_number;
        $texting_number=Number::where('phone_number',$texting_number)->first();
        $sending_server=SendingServer::find($texting_number->sending_server_id);
        if($sending_server){//if the number is attached to a sending server
            $projectID = $sending_server->signalwire_project_id;
            $authToken = $sending_server->signalwire_api_token;
            $signalwireSpaceUrl = $sending_server->signalwire_space_url; // Example: example.signalwire.com
        }else{
            $projectID = $organisation->signalwire_texting_project_id;
            $authToken = $organisation->signalwire_texting_api_token;
            $signalwireSpaceUrl = $organisation->signalwire_texting_space_url; // Example: example.signalwire.com
        }
        $texting_number = $workflow->texting_number;
        // Create a new SignalWire Client
        $client = new SignalWireClient($projectID, $authToken, [
            'signalwireSpaceUrl' => $signalwireSpaceUrl
        ]);

        // Send an SMS
        $message = $client->messages->create(
            $phone, // Destination phone number (in E.164 format)
            [
                'from' => $texting_number, // Your SignalWire phone number (in E.164 format)
                'body' => $content
            ]
        );
        $contact = Contact::find($contact_id);
        if ($contact) {
            $communication_ids_array = [];
            $communication_ids = $contact->contact_communication_ids;
            if (!empty($communication_ids)) {
                $communication_ids_array = explode(',', $communication_ids);
            }
            $message_sid = $message->sid;
            array_push($communication_ids_array, $message_sid);
            $new_contact_communication_ids = implode(',', $communication_ids_array);
            $contact->contact_communication_ids = $new_contact_communication_ids;
            $contact->save();
            Log::info("Message sent with SID: $message_sid");
            if ($message_sid) {
                $text_sent = TextSent::create([
                    'name' => $contact->contact_name,
                    'contact_id' => $contact->id,
                    'contact_communication_id' => $message_sid,
                    'organisation_id' => $organisation_id,
                    'zipcode' => $contact->zipcode,
                    'state' => $contact->state,
                    'city' => $contact->city,
                    'marketing_channel' => 'SMS',
                    'sending_number' => $texting_number,
                    'user_id' => $workflow->user_id,
                    'response' => 'No'
                ]);
            } else {
                $contact->status = "SMS FAILED";
                $contact->save();
            }
        } else {
            Log::error("Contact with ID $contact_id not found.");
        }
        return ['success' => true, 'provider' => 'signalwire'];
    }
}
