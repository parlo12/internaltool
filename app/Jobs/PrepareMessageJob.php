<?php

namespace App\Jobs;

use App\Models\Contact;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PrepareMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $uuid;
    protected $group_id;
    protected $api_key;
    protected $step;
    protected $contact;
    protected $dispatchTime;

    public function __construct($uuid, $group_id, $api_key, $step, $contact, $dispatchTime)
    {
        $this->uuid = $uuid;
        $this->group_id = $group_id;
        $this->api_key = $api_key;
        $this->step = $step;
        $this->contact = $contact;
        $this->dispatchTime = $dispatchTime;
    }

    public function handle()
    {
        // Fetch contact information
        $contactInfo = $this->get_contact($this->uuid, $this->group_id, $this->api_key);

        // Compose and spintax the message
        $message = $this->composeMessage($contactInfo, $this->step->content);
        $message = $this->spintax($message);

        // Dispatch the QueaueMessagesJob with the composed message
        QueaueMessagesJob::dispatch(
            '+'.$this->contact->phone,
            $message,
            $this->step->workflow_id,
            $this->step->type,
            $this->contact->id,
            $this->contact->organisation_id
        )->delay($this->dispatchTime);

        // Update the contact's status and next step time
        $step_delay = (int)$this->step->delay;
        $next_step_after = Carbon::parse($this->dispatchTime)->addSeconds($step_delay * 60);

        $contactModel = Contact::find($this->contact->id);
        $contactModel->can_send_after = $next_step_after;
        $contactModel->status = "QUEUED";
        $contactModel->can_send = 0;
        $contactModel->save();
        if ($contactModel->can_send_after) {
            UpdateContactStep::dispatch($contactModel)->delay(Carbon::parse($contactModel->can_send_after));
            Log::info("Dispatched UpdateContactStep", [
                'contact_id' => $contactModel->id,
                'can_send_after' => $contactModel->can_send_after
            ]);
        } else {
            Log::warning("Skipping UpdateContactStep due to missing can_send_after", [
                'contact_id' => $contactModel->id
            ]);
        }
        Log::info('Scheduled contact: ' . $contactModel->id . ' at ' . $this->dispatchTime);
        Log::info("The contact will move to the next step on $next_step_after");
        Log::info($message);
    }

    private function get_contact($contact_uid, $group_id, $api_key)
    {
        $url = "https://www.godspeedoffers.com/api/v3/contacts/{$group_id}/search/{$contact_uid}";
        $token = $api_key;
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

    private function composeMessage($contact, $messageTemplate)
    {
        $message = $this->replacePlaceholders($messageTemplate, $contact);
        return  $message;
    }

    private function spintax($text)
    {
        return preg_replace_callback(
            '/\{(((?>[^\{\}]+)|(?R))*)\}/x',
            function ($text) {
                $text = $text[1];
                $parts = explode('|', $text);
                return $parts[array_rand($parts)];
            },
            $text
        );
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
}

