<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use App\Jobs\PlaceCallJob;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class callController extends Controller
{
    private $report_id;
    public function create()
    {
        $voices = $this->getVoices();
        $contact_groups = $this->get_contact_groups()['data'];
        return inertia("Calls/Create", [
            'success' => session('success'),
            'contactGroups' => $contact_groups,
            'voices' => $voices
        ]);
    }

    public function get_placeholders(Request $request)
    {
        $group_id = $request->group_id;
        $contact = $this->getFirstContact($group_id);
        $contact_info = $this->get_contact($contact['uid'], $group_id);
        $placeholders = $this->create_placeholders($contact_info);
        $placeholderKeys = array_keys($placeholders);
        return response()->json($placeholderKeys);
    }

    public function call(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        $contacts = $this->getAllContacts($request->contact_group);
        $voice = $request->voice;
        $contacts = $this->getAllContacts($request->contact_group);
        $detection_duration = $request->detection_duration;
        $batchSize = $request->batch_size;
        $delayMinutes = $request->delay_time;
        $agent_phone_number = $request->country_code . $request->agent_phone_number;
        $currentBatch = 0;
        $campaign_id = Str::uuid();
        foreach (array_chunk($contacts, $batchSize) as $batch) {
            foreach ($batch as $contact) {
                $message = $request->message;
                $contactUid = $contact['uid'];
                $phone = $contact['phone'];
                $groupId = $request->contact_group;
                $contactInfo = $this->get_contact($contactUid, $groupId);
                $firstName = $contactInfo['custom_fields']['FIRST_NAME'];
                $lastName = $contactInfo['custom_fields']['LAST_NAME'];
                $name = $firstName . ' ' . $lastName;
                $group_name = $this->getGroupName($groupId);
                $report = Report::create(
                    [
                        'contact_uid' => $contactUid,
                        'contact_name' => $name,
                        'group_name' => $group_name,
                        'phone' => $phone,
                        'campaign_id' => $campaign_id,
                        'call_status' => 'QUEUED'
                    ]
                );
                $message = $this->composeMessage($contactInfo, $message);
                $userId = 1;
                $messageId = Str::uuid();
                if (isset($voice) && !empty($voice)) {
                    $path = $this->textToSpeech($message, $userId, $messageId, $voice);
                } else {
                    $path = $this->textToSpeech($message, $userId, $messageId, 'knrPHWnBmmDHMoiMeP3l');
                }
                $delay = Carbon::now()->addMinutes($currentBatch * $delayMinutes);
                PlaceCallJob::dispatch('+' . $phone, $path, $report->id, $agent_phone_number, $detection_duration)->delay($delay);
            }
            $currentBatch++;
        }
        return redirect()->route('create')->with('success', "Campaign: $campaign_id scheduled for send");
    }
    public function handleCall(Request $request)
{
    $report_id = $request->input('report_id');
    $agent_phone_number = $request->input('agent_phone_number');
    

    Log::info("Reached handleCall");
    Log::info($request->all());
    
    $amd_status = $request->input('AnsweredBy');
    $voice_recording = $request->input('voice_recording');
    $response = '<?xml version="1.0" encoding="UTF-8"?>';
    $response .= '<Response>';
    
    if ($amd_status == 'human' || $amd_status == 'unknown') {
        DB::table('reports')
            ->where('id', $report_id)
            ->update(['call_status' => 'RECORDING_PLAYED']);
        $response .= '<Play>' . htmlspecialchars($voice_recording) . '</Play>';
        $actionUrl = route('transfer') . '?report_id=' . urlencode($report_id) . '&agent_phone_number=' . urlencode($agent_phone_number);
        $response .= '<Gather numDigits="1" timeout="15" action="' . htmlspecialchars($actionUrl) . '" method="POST">';
        $response .= '<Play>https://voicemails.godspeedoffers.com/mes_1_7612.mp3</Play>';
        $response .= '</Gather>';
    } 
    else if( $amd_status == 'machine_end_other'){
   DB::table('reports')
                ->where('id', $report_id)
                ->update(['call_status' => 'SUCCESSFUL']);
    }
    else {
        DB::table('reports')
            ->where('id', $report_id)
            ->update(['call_status' => 'VOICEMAIL_LEFT']);
        $response .= '<Play>' . htmlspecialchars($voice_recording) . '</Play>';
    }

    $response .= '</Response>';
    
    Log::info("handleCall Response: " . $response);
    
    return response($response, 200)
        ->header('Content-Type', 'application/xml')
        ->header('Pragma', 'no-cache')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
        ->header('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');
}


    public function transferCall(Request $request)
    {
        Log::info('Reached transferCall');
        Log::info('Digits received: ' . $request->input('Digits'));
        $report_id = $request->input('report_id');
        $agent_phone_number = $request->input('agent_phone_number');
        $digits = $request->input('Digits');
        if ($digits == '1') {
            DB::table('reports')
                ->where('id', $report_id)
                ->update(['call_status' => 'CALL_TRANSFERRED']);
            Log::info('Forwarding call to agent.');
            $response = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            $response .= '<Dial><Number>' . $agent_phone_number . '</Number></Dial>';
            $response .= '</Response>';
        } else {
            DB::table('reports')
                ->where('id', $report_id)
                ->update(['call_status' => 'RECORDING_PLAYED_NOT_TRANSFERRED']);
            Log::info('Hanging up call.');
            $response = '<?xml version="1.0" encoding="UTF-8"?>';
            $response .= '<Response>';
            $response .= '<Hangup/>';
            $response .= '</Response>';
        }
        Log::info("transferCall Response: " . $response);
        return response($response, 200)
            ->header('Content-Type', 'application/xml')
            ->header('Pragma', 'no-cache')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
            ->header('Expires', 'Thu, 19 Nov 1981 08:52:00 GMT');
    }

    public function place_call($phone, $voice_recording, $report_id, $agent_phone_number, $detection_duration)
    {
        $signalwire_space_url = env('SIGNALWIRE_SPACE_URL');
        $project_id = env('SIGNALWIRE_PROJECT_ID');
        $api_token = env('SIGNALWIRE_API_TOKEN');
        $to_number = $phone;
        //$to_number='+16892873119';
        $from_number = '+12044001022';
        $api_url = "https://$signalwire_space_url/api/laml/2010-04-01/Accounts/$project_id/Calls.json";
        $data_first_call = [
            'Url' => route('answer', ['voice_recording' => $voice_recording, 'report_id' => $report_id, 'agent_phone_number' => $agent_phone_number]),
            'To' => $to_number,
            'From' => $from_number,
            // 'AsyncAMD' => 'true',
            // 'AsyncAmdStatusCallback' => route('amdStatus'),
            // 'AsyncAmdStatusCallbackMethod' => 'POST',
            'MachineDetection' => 'DetectMessageEnd',
            'MachineDetectionTimeout' => $detection_duration,
        ];
        list($http_code, $response) = $this->make_call($api_url, $data_first_call, $project_id, $api_token);
        $callData = json_decode($response, true);
        if (isset($callData['sid'])) {
            // Update the call_sid field if sid exists
            Log::info('Report ID: ' . $report_id);
            Log::info('call SID: ' . $callData['sid']);
            DB::table('reports')
                ->where('id', $report_id)
                ->update(['call_status' => 'SUCCESSFUL', 'call_sid' => $callData['sid']]);
        } else {
            Log::info('Report ID: ' . $report_id);
            DB::table('reports')
                ->where('id', $report_id)
                ->update(['call_status' => 'FAILED']);
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
    public function amdStatus(Request $request)
    {
        // Handle the async AMD status callback
        Log::info("Async AMD status: " . json_encode($request->all()));
        // Process the status as needed
    }
    private function get_contact_groups()
    {
        $url = 'https://godspeedoffers.com/api/v3/contacts';
        $token = env('GODSPEED_BEARER_TOKEN');;
        $client = new Client();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Accept' => 'application/json',
                ],
            ]);
            $body = $response->getBody();
            $data = json_decode($body, true);
            if ($data['status'] == 'success') {
                return $data['data'];
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to retrieve contacts'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getAllContacts($group_id)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/all';
        $token = env('GODSPEED_BEARER_TOKEN');
        $allContacts = [];
        $currentPage = 1;
        $totalPages = 1;
        do {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => [
                    'page' => $currentPage,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if ($data['status'] == 'success') {
                $allContacts = array_merge($allContacts, $data['data']['data']);
                $currentPage++;
                $totalPages = $data['data']['last_page'];
            } else {
                break;
            }
        } while ($currentPage <= $totalPages);
        $contacts = array_map(function ($contact) {
            return [
                'uid' => $contact['uid'],
                'phone' => $contact['phone'],
            ];
        }, $allContacts);
        return $contacts;
    }

    private function composeMessage($contact, $messageTemplate)
    {
        $message = $this->replacePlaceholders($messageTemplate, $contact);
        return  $message;
    }

    private function replacePlaceholders($template, $contact)
    {
        $placeholders = $this->create_placeholders($contact);
        foreach ($placeholders as $key => $value) {
            $template = str_replace($key, $value, $template);
        }
        Log::info('Final Template: ' . $template);
        return $template;
    }

    private function get_contact($contact_uid, $group_id)
    {
        $url = "https://www.godspeedoffers.com/api/v3/contacts/{$group_id}/search/{$contact_uid}";
        $token = env('GODSPEED_BEARER_TOKEN');
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

    private function create_placeholders($contact)
    {
        $placeholders = [
            '{{phone}}' => $contact['phone'],
        ];
        foreach ($contact['custom_fields'] as $key => $value) {
            $placeholders['{{' . $key . '}}'] = $value;
        }
        return $placeholders;
    }

    private function getFirstContact($group_id)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/all';
        $token = env('GODSPEED_BEARER_TOKEN');
        $currentPage = 1;
        $totalPages = 1;
        do {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
                'query' => [
                    'page' => $currentPage,
                ],
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            if ($data['status'] == 'success') {
                $contacts = $data['data']['data'];
                if (!empty($contacts)) {
                    $firstContact = [
                        'uid' => $contacts[0]['uid'],
                        'phone' => $contacts[0]['phone'],
                    ];
                    return $firstContact;
                }
                $currentPage++;
                $totalPages = $data['data']['last_page'];
            } else {
                // Handle the error as per your application's requirement
                break;
            }
        } while ($currentPage <= $totalPages);
        return null;
    }

    private function textToSpeech($textMessage, $userId, $messageId, $voice)
    {
        $endPointUrl = "https://api.elevenlabs.io/v1/text-to-speech/$voice";
        $apiKey = env('ELEVEN_LABS_API_KEY');
        $headers = [
            "Accept" => "audio/mpeg",
            "Content-Type" => "application/json",
            "xi-api-key" => $apiKey
        ];
        $data = [
            "text" => $textMessage,
            "model_id" => "eleven_monolingual_v1",
            "voice_settings" => [
                "stability" => 0.5,
                "similarity_boost" => 0.5
            ]
        ];
        $response = Http::withHeaders($headers)->post($endPointUrl, $data);
        if ($response->failed()) {
            Log::error('API request failed', ['response' => $response->body()]);
            return false;
        }
        $audioContent = $response->body();
        $baseDir = '/home/customer/www/voicemails.godspeedoffers.com/public_html/uploads';
        $fileName = "mes_" . $messageId . '_' . rand(0000, 9999) . ".mp3";
        $fullPath = $baseDir . DIRECTORY_SEPARATOR . $fileName;
        if (!is_dir($baseDir)) {
            if (!mkdir($baseDir, 0777, true) && !is_dir($baseDir)) {
                Log::error('Failed to create directory', ['directory' => $baseDir]);
                return false;
            }
        }
        if (file_put_contents($fullPath, $audioContent) !== false) {
            $publicUrl = 'https://voicemails.godspeedoffers.com/uploads/' . $fileName;
            Log::info('File saved', ['path' => $publicUrl]);
            return $publicUrl;
        } else {
            Log::error('Failed to save file', ['path' => $fullPath]);
            return false;
        }
    }

    private function getVoices()
    {
        $url = "https://api.elevenlabs.io/v1/voices";
        try {
            $response = Http::get($url);
            if ($response->successful()) {
                $voices = $response->json()['voices'];
                $filteredVoices = array_map(function ($voice) {
                    return [
                        'name' => $voice['name'],
                        'gender' => $voice['labels']['gender'],
                        'preview_url' => $voice['preview_url'],
                        'voice_id' => $voice['voice_id']
                    ];
                }, $voices);
                return $filteredVoices;
            } else {
                return ['error' => 'Request failed with status: ' . $response->status()];
            }
        } catch (\Exception $e) {
            return ['error' => 'Request failed with error: ' . $e->getMessage()];
        }
    }
    private function getGroupName($group_id)
    {
        $client = new Client();
        $url = 'https://godspeedoffers.com/api/v3/contacts/' . $group_id . '/show';
        $token = env('GODSPEED_BEARER_TOKEN');

        try {
            $response = $client->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();

            if ($statusCode == 200) {
                $data = json_decode($body, true);
                if (isset($data['data']['name'])) {
                    return $data['data']['name'];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
