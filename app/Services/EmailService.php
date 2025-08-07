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
use PhpOffice\PhpWord\TemplateProcessor;



class EmailService
{
    public function __construct() {}

    public function sendEmail($content, $contact_id, $organisation_id)
    {
        Log::info('Attempting to send email');
        try {
            $organisation = Organisation::find($organisation_id);
            $sending_email = $organisation->sending_email;
            $password = $organisation->email_password;

            Config::set('mail.mailers.smtp.username', $sending_email);
            Config::set('mail.mailers.smtp.password', $password);

            $contact = Contact::find($contact_id);
            $step = Step::find($contact->current_step);
            $subject = $step->email_subject ?? 'New Email';
            $workflow = Workflow::find($contact->workflow_id);

            $DynamicTagsService = new DynamicTagsService($workflow->godspeedoffers_api);
            $subject = $DynamicTagsService->composeMessage($contact, $subject);

            $details = [
                'name' => $sending_email,
                'email' => $sending_email,
                'subject' => $subject,
                'message' => $content,
                'from_email' => $sending_email,
                'from_name' => $sending_email
            ];

            // Initialize attachments array
            $attachments = [];

            // Handle template files if they exist
            if (!empty($step->template_files)) {
                // Ensure template_files is an array
                $templateFiles = is_array($step->template_files)
                    ? $step->template_files
                    : json_decode($step->template_files, true) ?? [];

                foreach ($templateFiles as $path) {
                    $filePath = storage_path('app/public' . str_replace('/storage', '', $path));

                    if (file_exists($filePath)) {
                        try {
                            $processedPath = $this->generate_attachment($filePath, $contact);
                            $attachments[] = [
                                'file' => $processedPath,
                                'name' => 'processed_' . basename($path),
                                'mime' => mime_content_type($processedPath),
                            ];
                        } catch (\Exception $e) {
                            Log::error("Failed to process template {$path}: " . $e->getMessage());
                        }
                    } else {
                        Log::error("Template file not found: {$filePath}");
                    }
                }
            }

            // Add static attachment
            $staticAttachment = public_path('uploads/Eliud Mitau-cover-mauzo.pdf');
            if (file_exists($staticAttachment)) {
                $attachments[] = [
                    'file' => $staticAttachment,
                    'name' => 'eliud.pdf',
                    'mime' => 'application/pdf',
                ];
            } else {
                Log::error("Static attachment file missing: {$staticAttachment}");
            }

            $details['attachments'] = $attachments;

            Mail::to($contact->email)->send(new ContactEmail($details));
            $contact->update(['status' => 'EMAIL_SENT']);
            Log::info('Email sent successfully');
            // After email is sent
            // foreach ($attachments as $attachment) {
            //     if (str_contains($attachment['file'], 'temp_') || str_contains($attachment['file'], 'LOI_')) {
            //         @unlink($attachment['file']);
            //     }
            // }
            return response()->json(['message' => 'Email sent successfully!'], 200);
        } catch (\Exception $e) {
            Log::error("Failed to send email: {$e->getMessage()}");
            if ($contact ?? false) {
                $contact->update(['status' => 'EMAIL_FAILED']);
            }
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
    public function generate_attachment($templatePath, $contact): string
    {
        $tempDocPath = storage_path('app/temp_LOI_' . uniqid() . '.docx');
        $pdfOutputPath = storage_path('app/LOI_' . uniqid() . '.pdf');

        // Copy template to a temp location
        copy($templatePath, $tempDocPath);

        // Load and replace
        $templateProcessor = new TemplateProcessor($tempDocPath);

        $templateProcessor->setValue('property_address', $contact['address'] ?? '');
        $templateProcessor->setValue('full_name', $contact['contact_name'] ?? '');
        $templateProcessor->setValue('company_name', 'Godspeed Offers LLC');
        $templateProcessor->setValue('email', 'eliud@godspeed.com');
        $templateProcessor->setValue('date', now()->format('F d, Y'));
        $templateProcessor->setValue('agent_name', 'Jane Doe');
        $templateProcessor->setValue('offer_price', '$250,000');
        $templateProcessor->setValue('earnest_money', '$5,000');
        $templateProcessor->setValue('closing_days', '30');

        $templateProcessor->saveAs($tempDocPath);

        // Now load it as HTML and convert to PDF
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempDocPath);
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

        // Save as HTML
        $htmlPath = storage_path('app/LOI_' . uniqid() . '.html');
        $objWriter->save($htmlPath);

        // Convert HTML to PDF
        $htmlContent = file_get_contents($htmlPath);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($htmlContent);
        $pdf->save($pdfOutputPath);

        return $pdfOutputPath;
    }
}
