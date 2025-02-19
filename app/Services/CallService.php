<?php

namespace App\Services;

use App\Models\CallsSent;
use App\Models\Contact;
use App\Models\Number;
use App\Models\Organisation;
use App\Models\SendingServer;
use App\Models\Workflow;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use OpenAI;


class CallService
{
    protected $provider;
    protected $config;

    public function __construct($provider = 'signalwire')
    {
        $this->provider = $provider;
    }

    public function sendCall($phone, $content, $workflow_id, $detection_duration, $contact_id, $organisation_id)
    {
        try { //You add a new call provider from here.
            switch ($this->provider) {
                case 'signalwire':
                    return $this->sendWithSignalWire($phone, $content, $workflow_id, $detection_duration, $contact_id, $organisation_id);
                default:
                    throw new Exception("MMS provider not supported.");
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
  
    private function sendWithSignalwire($phone, $content, $workflow_id, $detection_duration, $contact_id, $organisation_id)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $agent_phone_number = $workflow->agent_number;
        $messageId = Str::uuid();
        $path = $this->text_to_speech_alt($content, $messageId, $organisation->openAI);
        $contact = Contact::find($contact_id);
        if (!$path) {
            $contact->status = "OpenAI ERROR";
            $contact->save();
        }
        $this->place_call($phone, $path, $agent_phone_number, $detection_duration, $contact_id, $organisation_id);
        Log::info("I  Reached VoiceCall sending function");
    }
    private function place_call($phone, $voice_recording, $agent_phone_number, $detection_duration, $contact_id, $organisation_id)
    {
        $contact = Contact::find($contact_id);
        $workflow = Workflow::find($contact->workflow_id);
        $organisation = Organisation::find($organisation_id);
        $calling_number = $workflow->calling_number;
        $calling_number = Number::where('phone_number', $calling_number)
        ->where('organisation_id', $organisation_id)
        ->first();
        $sending_server = SendingServer::find($calling_number->sending_server_id);
        if ($sending_server) {
            $signalwire_space_url = $sending_server->signalwire_space_url;
            $project_id = $sending_server->signalwire_project_id;
            $api_token = $sending_server->signalwire_api_token;    
        }
        else{
            $signalwire_space_url = $organisation->signalwire_calling_space_url;
            $project_id = $organisation->signalwire_calling_project_id;
            $api_token = $organisation->signalwire_calling_api_token;
        }
        $calling_number = $workflow->calling_number;
        $to_number = $phone;
        $from_number = $calling_number;
        $api_url = "https://$signalwire_space_url/api/laml/2010-04-01/Accounts/$project_id/Calls.json";
        $data_first_call = [
            'Url' => route('answer-workflow-call', ['voice_recording' => $voice_recording, 'agent_phone_number' => $agent_phone_number, 'contact_id' => $contact_id]),
            'To' => $to_number,
            'From' => $from_number,
            'MachineDetection' => 'DetectMessageEnd',
            'MachineDetectionTimeout' => $detection_duration,
        ];
        list($http_code, $response) = $this->make_call($api_url, $data_first_call, $project_id, $api_token);
        $callData = json_decode($response, true);
        if (isset($callData['sid'])) {
            $call_sid = $callData['sid'];
            CallsSent::create([
                'name' => $contact->contact_name,
                'contact_id' => $contact->id,
                'contact_communication_id' => $call_sid,
                'organisation_id' => $organisation_id,
                'zipcode' => $contact->zipcode,
                'state' => $contact->state,
                'city' => $contact->city,
                'marketing_channel' => 'VoiceCall',
                'sending_number' => $calling_number,
                'user_id' => $workflow->user_id,
                'response' => 'No'
            ]);
            $contact = Contact::find($contact_id);
            if ($contact) {
                $communication_ids_array = [];
                $communication_ids = $contact->contact_communication_ids;
                if (!empty($communication_ids)) {
                    $communication_ids_array = explode(',', $communication_ids);
                }
                array_push($communication_ids_array, $callData['sid']);
                $new_contact_communication_ids = implode(',', $communication_ids_array);
                $contact->contact_communication_ids = $new_contact_communication_ids;
                $contact->save();
                Log::info('call SID: ' . $callData['sid']);

                Log::info("Message sent with SID: $call_sid");
            } else {
                Log::error("Contact with ID $contact_id not found.");
            }
        } else {
            $contact->status = "CALL FAILED";
            $contact->save();
            Log::info("Failed to call $phone");
        }
        Log::info("Call response: $response, HTTP Code: $http_code");
        return response($response)
            ->header('Content-Type', 'application/json');
    }
    private function make_call($api_url, $data, $project_id, $api_token)
    {
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "$project_id:$api_token");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return [$http_code, $response];
    }

    private function text_to_speech_alt($textMessage, $messageId, $openAiApiKey)
    {
        $client = OpenAI::client($openAiApiKey);
        try {
            $result = $client->audio()->speech([
                'model' => 'tts-1',
                'input' => $textMessage,
                'voice' => 'onyx',
            ]);
        } catch (\Exception $e) {
            Log::error('OpenAI API request failed', ['error' => $e->getMessage()]);
            return false;
        }
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
