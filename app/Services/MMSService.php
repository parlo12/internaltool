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
use Illuminate\Support\Str;
use OpenAI;


class MMSService
{
    protected $provider;
    protected $config;

    public function __construct($provider = 'twilio')
    {
        $this->provider = $provider;
    }

    public function sendMMS($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        try { //You add a new MMS provider from here.
            switch ($this->provider) {
                case 'twilio':
                    return $this->sendWithTwilio($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                case 'signalwire':
                    return $this->sendWithSignalWire($phone, $content, $workflow_id, $type, $contact_id, $organisation_id);
                default:
                    throw new Exception("MMS provider not supported.");
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendWithTwilio($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $texting_number = $workflow->texting_number;
        $texting_number=Number::where('phone_number',$texting_number)->first();
        $sending_server=SendingServer::find($texting_number->sending_server_id);
        if($sending_server){//if the number is attached to a sending server
            $sid = $sending_server->twilio_account_sid;
            $token = $sending_server->twilio_auth_token;
        }else{
            $sid = $organisation->twilio_texting_account_sid;
            $token = $organisation->twilio_texting_auth_token;
        }
        $texting_number = $workflow->texting_number;
        $twilio = new TwilioClient($sid, $token);
        $workflow = Workflow::find($workflow_id);
        $voice = $workflow->voice;
        $userId = 1;
        $messageId = Str::uuid();
        $contact = Contact::find($contact_id);
        $path = $this->text_to_speech_alt($content, $messageId, $organisation->openAI);
        if (!$path) {
            $contact->status = "OpenAI ERROR";
            $contact->save();
        }
        try {
            $mediaUrl = [$path];
            $message = $twilio->messages->create(
                $phone,
                [
                    'from' => $texting_number,
                    'mediaUrl' => $mediaUrl
                ]
            );
            Log::info("I sent an MMS with thi $message->sid");
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
                        'marketing_channel' => 'VoiceMMS',
                        'sending_number' => $texting_number,
                        'user_id' => $workflow->user_id,
                        'response' => 'No'
                    ]);
                } {
                    $contact->status = "MMS FAILED";
                    $contact->save();
                }
            } else {
                Log::error("Contact with ID $contact_id not found.");
            }
        } catch (\Exception $e) {
            $contact->status = "MMS FAILED";
            $contact->save();
            Log::info("Error:" . $e->getMessage());
        }
    }

    private function sendWithSignalwire($phone, $content, $workflow_id, $type, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $texting_number = $workflow->texting_number;
        $texting_number=Number::where('phone_number',$texting_number)->first();
        $sending_server=SendingServer::find($texting_number->sending_server_id);
        if($sending_server){//if the number is attached to a sending server
            $projectID = $sending_server->signalwire_project_id;
            $authToken = $sending_server->signalwire_api_token;
            $signalwireSpaceUrl = $sending_server->signalwire_space_url; 
        }
        else{
            $projectID = $organisation->signalwire_texting_project_id;
            $authToken = $organisation->signalwire_texting_api_token;
            $signalwireSpaceUrl = $organisation->signalwire_texting_space_url;
        }
        $texting_number = $workflow->texting_number; // Example: example.signalwire.com
        $client = new SignalWireClient($projectID, $authToken, [
            'signalwireSpaceUrl' => $signalwireSpaceUrl
        ]);
        $messageId = Str::uuid();
        $path = $this->text_to_speech_alt($content, $messageId, $organisation->openAI);
        $contact = Contact::find($contact_id);
        if (!$path) {
            $contact->status = "OpenAI ERROR";
            $contact->save();
        }
        try {
            $message = $client->messages->create(
                $phone,
                [
                    'from' => $texting_number,
                    'mediaUrl' => [$path]
                ]
            );
            Log::info("I sent an MMS with this $message->sid");
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
                        'marketing_channel' => 'VoiceMMS',
                        'sending_number' => $texting_number,
                        'user_id' => $workflow->user_id,
                        'response' => 'No'
                    ]);
                } else {
                    $contact->status = "MMS FAILED";
                    $contact->save();
                }
            } else {
                Log::error("Contact with ID $contact_id not found.");
            }
        } catch (\Exception $e) {
            Log::info("Error:" . $e->getMessage());
            $contact->status = "MMS FAILED";
            $contact->save();
        }

        return ['success' => true, 'provider' => 'signalwire'];
    }
    private function text_to_speech_alt($textMessage, $messageId, $openAiApiKey)
    {
        // Temporarily override the OpenAI API key
        //config(['services.openai.key' => $openAiApiKey]);
        $client = OpenAI::client($openAiApiKey);
        try {
            // Call the OpenAI API with the new key
            $result = $client->audio()->speech([
                'model' => 'tts-1',
                'input' => $textMessage,
                'voice' => 'onyx',
            ]);
        } catch (\Exception $e) {
            // Log the error and return false if the OpenAI request fails
            Log::error('OpenAI API request failed', ['error' => $e->getMessage()]);
            return false;
        }

        // Save the audio file
        $baseDir = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads';
        $fileName = "mes_" . $messageId . '_' . rand(0000, 9999) . ".mp3";
        $fullPath = $baseDir . DIRECTORY_SEPARATOR . $fileName;

        if (!is_dir($baseDir)) {
            if (!mkdir($baseDir, 0777, true) && !is_dir($baseDir)) {
                Log::error('Failed to create directory', ['directory' => $baseDir]);
                return false;
            }
        }

        if (file_put_contents($fullPath, $result) !== false) {
            $publicUrl = 'https://internaltools.godspeedoffers.com/uploads/' . $fileName;
            Log::info('File saved', ['path' => $publicUrl]);
            return $publicUrl;
        } else {
            Log::error('Failed to save file', ['path' => $fullPath]);
            return false;
        }
    }
}
