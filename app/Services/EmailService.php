<?php

namespace App\Services;

use App\Mail\ContactEmail;
use App\Models\Contact;
use App\Models\Organisation;
use App\Models\Step;
use App\Models\Workflow;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;



class EmailService
{
    public function __construct()
    {
    }

    public function sendEmail( $content, $contact_id, $organisation_id)
    {
        Log::info('Attempting to send email');
        try {
            $organisation = Organisation::find($organisation_id);
            $sending_email = $organisation->sending_email;
            $password = $organisation->email_password;

            // Log retrieved email credentials
            Log::info("Sending email from: $sending_email");

            // Set the SMTP username and password dynamically
            Config::set('mail.mailers.smtp.username', $sending_email);
            Config::set('mail.mailers.smtp.password', $password);

            $contact = Contact::find($contact_id);
            $step = Step::find($contact->current_step);
            $subject = $step->email_subject ?? 'New Email'; // Fallback to a default subject if not set
            $workflow = Workflow::find($contact->workflow_id);
            $contactInfo = $this->get_contact($contact->uuid, $workflow->group_id, $workflow->godspeedoffers_api);
            $subject = $this->composeMessage($contactInfo, $subject);
            $subject = $this->spintax($subject);
            Log::info("Sending to: {$contact->email}, Subject: $subject");

            $details = [
                'name' => $sending_email,
                'email' => $sending_email,
                'subject' => $subject,
                'message' => $content,
                'from_email' => $sending_email,
                'from_name' => $sending_email
            ];

            // Attempt to send the email
            Mail::to($contact->email)->send(new ContactEmail($details));

            Log::info('Email sent successfully');
            return response()->json(['message' => 'Email sent successfully!'], 200);
        } catch (\Exception $e) {
            // Log the error message with the exception details
            Log::error("Failed to send email: {$e->getMessage()}", [
                'exception' => $e,
                'organisation_id' => $organisation_id,
                'contact_id' => $contact_id,
                'sending_email' => $sending_email ?? null,
                'contact_email' => $contact->email ?? null
            ]);

            return response()->json(['error' => 'Failed to send email', 'details' => $e->getMessage()], 500);
        }
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
        // Log::info('Final Template: ' . $template);
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
    private static function  spintax($text)
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
}
