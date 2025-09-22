<?php

namespace App\Services;

use App\Mail\ContactEmail;
use App\Models\Contact;
use App\Models\Organisation;
use App\Models\PropertyDetail;
use App\Models\Step;
use App\Models\TemplateFile;
use App\Models\Workflow;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpParser\Builder\Property;

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
            if (!empty($step->selected_file_ids)) {
                $selected_file_ids = is_array($step->selected_file_ids)
                    ? $step->selected_file_ids
                    : json_decode($step->selected_file_ids, true) ?? [];

                foreach ($selected_file_ids as $file_id) {
                    // Get the relative storage path (e.g., /storage/uploads/file.docx)
                    $path = TemplateFile::where('id', $file_id)->value('path');
                    $name = TemplateFile::where('id', $file_id)->value('name');

                    if (!$path) {
                        Log::error("Path not found for template file ID: {$file_id}");
                        continue;
                    }

                    // Convert public path to storage path
                    // e.g. /storage/uploads/filename.docx => /app/public/uploads/filename.docx
                    $relativePath = str_replace('/storage', '', $path);
                    $filePath = storage_path('app/public/' . $relativePath);

                    if (file_exists($filePath)) {
                        try {
                            $processedPath = $this->generate_attachment($filePath, $contact, $name);

                            $attachments[] = [
                                'file' => $processedPath,
                                'name' => $contact['address'] . '_' . basename($processedPath),
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
    public function generate_attachment($templatePath, $contact, $name): string
    {
        // $tempDocPath = storage_path('app/temp_LOI_' . uniqid() . '.docx');
        // $pdfOutputPath = storage_path('app/LOI_' . uniqid() . '.pdf');
        // Use extracted filename in your paths
        $filename = pathinfo($name, PATHINFO_FILENAME);
        $tempDocPath = storage_path('app/temp_' . $filename . '_' . uniqid() . '.docx');
        $pdfOutputPath = storage_path('app/' . $filename . '_' . uniqid() . '.pdf');
        // Copy template to temp
        copy($templatePath, $tempDocPath);
        // Load and replace placeholders
        $templateProcessor = new TemplateProcessor($tempDocPath);
        $property_details = PropertyDetail::where('organisation_id', $contact['organisation_id'])->first();
        if (!$property_details) {
            Log::error("Property details not found for organisation ID: {$contact['organisation_id']}");
            return $pdfOutputPath;
        }

        $listPrice = $contact['list_price']
            ? (float)str_replace(['$', ','], '', $contact['list_price'])
            : 0;

        $purchasePrice = $listPrice * ($property_details->purchase_price / 100);
        $UPA  = (float)$purchasePrice * ($property_details->upa / 100);
        $PLC  = (float)$purchasePrice * ($property_details->plc / 100);
        $downpayment = (float)$purchasePrice * ($property_details->downpayment / 100);
        $SFA = (float)$purchasePrice - $downpayment;
        $monthly_amount = $property_details->monthly_amount ?? 0;
        $baloon_payment = $SFA - ($monthly_amount * 12 * 10);
        $SCA  = (float)$purchasePrice * ($property_details->sca / 100);
        $AGP = (float)$purchasePrice * ($property_details->agreed_net_proceeds / 100);
        $RMA = (float)$purchasePrice * ($property_details->remaining_amount_after_ANP / 100);
        $today = Carbon::now()->format('jS \\d\\a\\y \\o\\f F, Y');
        $templateProcessor->setValue('agreement_date', $today);
        $templateProcessor->setValue('AGP', $AGP);
        $templateProcessor->setValue('RMA', $RMA);
        $templateProcessor->setValue('property_address', $contact['address'] ?? '');
        $templateProcessor->setValue('contact_name', $contact['contact_name'] ?? '');
        $templateProcessor->setValue('EMD', $contact['earnest_money_deposit'] ?? '');
        foreach ($contact as $key => $value) {
            $templateProcessor->setValue($key, $value ?? '');
            log::info("Set placeholder {$key} to " . ($value ?? ''));
        }
        $templateProcessor->setValue('downpayment', $downpayment);
        $templateProcessor->setValue('SCA', $SCA);
        $templateProcessor->setValue('UPA', $UPA);
        $templateProcessor->setValue('PLC', $PLC);
        $templateProcessor->setValue('purchase_price', $purchasePrice);
        $templateProcessor->setValue('monthly_amount', $monthly_amount);
        $templateProcessor->setValue('baloon_payment', $baloon_payment);
        $templateProcessor->setValue('SFA', $SFA);
        $templateProcessor->setValue('date', now()->format('F d, Y'));
        $templateProcessor->setValue('closing_day', now()->addDays(45)->format('F d, Y'));
        $templateProcessor->setValue('offer_price', $purchasePrice);

        $templateProcessor->saveAs($tempDocPath);

        // Load DOCX into PhpWord
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($tempDocPath);
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

        // Save HTML file
        $htmlPath = storage_path('app/LOI_' . uniqid() . '.html');
        $objWriter->save($htmlPath);

        // Read HTML content
        $htmlContent = file_get_contents($htmlPath);

        // --- HTML CLEANUP ---
        // Remove empty paragraphs
        $htmlContent = preg_replace('/<p>(\s|&nbsp;)*<\/p>/i', '', $htmlContent);
        // Replace multiple <br> with single
        $htmlContent = preg_replace('/(<br\s*\/?>\s*){2,}/i', '<br>', $htmlContent);
        // Remove Word page-break styles
        $htmlContent = preg_replace('/page-break-before:\s*always;?/i', '', $htmlContent);
        $htmlContent = preg_replace('/page-break-after:\s*always;?/i', '', $htmlContent);
        // Optional: trim whitespace
        $htmlContent = trim($htmlContent);

        // Convert cleaned HTML to PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($htmlContent);
        $pdf->save($pdfOutputPath);

        return $pdfOutputPath;
    }
}
