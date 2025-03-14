<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Number;
use App\Models\Organisation;
use App\Models\SendingServer;
use App\Models\Step;
use App\Models\TextSent;
use App\Models\Workflow;
use Exception;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;
use SignalWire\Rest\Client as SignalWireClient;
use Imagick;
use Carbon\Carbon;



class OfferService
{
    protected $provider;
    protected $config;

    public function __construct($provider = 'twilio')
    {
        $this->provider = $provider;
    }

    public function sendOffer($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $texting_number)
    {
        try { //You add a new MMS provider from here.
            switch ($this->provider) {
                case 'twilio':
                    return $this->sendWithTwilio($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $texting_number);
                case 'signalwire':
                    return $this->sendWithSignalWire($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $texting_number);
                default:
                    throw new Exception("MMS provider not supported.");
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function sendWithTwilio($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $texting_number)
    {
        $organisation = Organisation::find($organisation_id);
        $number = Number::where('phone_number', $texting_number)
            ->where('organisation_id', $organisation_id)
            ->first();
        $sending_server = SendingServer::find($number->sending_server_id);
        if ($sending_server) { //if the number is attached to a sending server
            $sid = $sending_server->twilio_account_sid;
            $token = $sending_server->twilio_auth_token;
        } else {
            $sid = $organisation->twilio_texting_account_sid;
            $token = $organisation->twilio_texting_auth_token;
        }
        $twilio = new TwilioClient($sid, $token);
        $workflow = Workflow::find($workflow_id);
        $contact = Contact::find($contact_id);
        $step = Step::find($contact->current_step);
        $expiry = $step->offer_expiry;
        Log::info("Step expiry is $expiry");
        $path = $this->generate_offer_card($contact->address, $expiry, $contact->offer, $contact->agent);
        Log::info($path);
        if (!$path) {
            $contact->status = "Image Gen Error";
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
                $contact->update(['status' => 'SMS SENT']);
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
                    TextSent::create([
                        'name' => $contact->contact_name,
                        'contact_id' => $contact->id,
                        'contact_communication_id' => $message_sid,
                        'organisation_id' => $organisation_id,
                        'zipcode' => $contact->zipcode,
                        'state' => $contact->state,
                        'city' => $contact->city,
                        'marketing_channel' => 'OfferMMS',
                        'sending_number' => $texting_number,
                        'user_id' => $workflow->user_id,
                        'response' => 'No'
                    ]);
                } {
                    $contact->status = "OFFER SENT";
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

    private function sendWithSignalwire($phone, $content, $workflow_id, $type, $contact_id, $organisation_id, $texting_number)
    {
        $organisation = Organisation::find($organisation_id);
        $workflow = Workflow::find($workflow_id);
        $number = Number::where('phone_number', $texting_number)
            ->where('organisation_id', $organisation_id)
            ->first();
        $sending_server = SendingServer::find($number->sending_server_id);
        if ($sending_server) { //if the number is attached to a sending server
            $projectID = $sending_server->signalwire_project_id;
            $authToken = $sending_server->signalwire_api_token;
            $signalwireSpaceUrl = $sending_server->signalwire_space_url;
        } else {
            $projectID = $organisation->signalwire_texting_project_id;
            $authToken = $organisation->signalwire_texting_api_token;
            $signalwireSpaceUrl = $organisation->signalwire_texting_space_url;
        }
        // Create a new SignalWire Client
        $client = new SignalWireClient($projectID, $authToken, [
            'signalwireSpaceUrl' => $signalwireSpaceUrl
        ]);

        $contact = Contact::find($contact_id);
        $step = Step::find($contact->current_step);
        $expiry = $step->offer_expiry;
        $path = $this->generate_offer_card($contact->address, $expiry, $contact->offer, $contact->agent);
        if (!$path) {
            $contact->status = "Imagick Error";
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
                    $contact = Contact::find($contact_id);
                    $contact->update(['status' => 'SMS SENT']);
                    TextSent::create([
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
                    $contact->status = "OFFER SENT";
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

    private function generate_offer_card($address, $expiry, $price, $agent)
    {
        try {
            Log::info("The address is $address");
            Log::info("The expiry is $expiry");
            Log::info("The price is $price");
            Log::info("The agent is $agent");

            // Load the image from the public directory
            $imagePath = public_path('uploads/offer_template.png');

            // Check if the template image exists
            if (!file_exists($imagePath)) {
                Log::error("Offer template image not found: $imagePath");
                return false;
            }

            // Generate a unique file name for the output
            $randomFileName = 'offer_template_' . uniqid() . '.png';
            $outputPath = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads/' . $randomFileName;

            // Check if the output directory is writable
            if (!is_writable(dirname($outputPath))) {
                Log::error("Upload directory is not writable: " . dirname($outputPath));
                return false;
            }

            // Create an Imagick object for the image
            $imagick = new Imagick($imagePath);

            // Get today's date
            $today = Carbon::now()->format('Y-m-d');

            // Format expiry date
            try {
                $valid = Carbon::parse($expiry)->format('m/d'); // Automatically parses various formats
            } catch (\Exception $e) {
                Log::error("Invalid expiry date format: $expiry");
                return false;
            }

            // Create a new ImagickDraw object to draw the text
            $draw = new \ImagickDraw();
            $draw->setFillColor('black');
            $draw->setFontSize(36);

            $draw_on_black = new \ImagickDraw();
            $draw_on_black->setFillColor('white');
            $draw_on_black->setFontSize(36);

            $draw_price = new \ImagickDraw();
            $draw_price->setFillColor('white');
            $draw_price->setFontSize(100);

            // X and Y coordinates for text positions
            $coordinates = [
                ['draw' => $draw, 'x' => 450, 'y' => 87, 'text' => $address],
                ['draw' => $draw, 'x' => 450, 'y' => 233, 'text' => $expiry],
                ['draw' => $draw_on_black, 'x' => 772, 'y' => 588, 'text' => $valid],
                ['draw' => $draw_price, 'x' => 325, 'y' => 850, 'text' => $price],
                ['draw' => $draw, 'x' => 800, 'y' => 1463, 'text' => $today],
                ['draw' => $draw, 'x' => 450, 'y' => 379, 'text' => $price],
                ['draw' => $draw_on_black, 'x' => 272, 'y' => 588, 'text' => $agent],
            ];

            // Annotate the image with text
            foreach ($coordinates as $coordinate) {
                $imagick->annotateImage(
                    $coordinate['draw'],
                    $coordinate['x'],
                    $coordinate['y'],
                    0,
                    $coordinate['text']
                );
            }

            // Try to save the image
            if (!$imagick->writeImage($outputPath)) {
                Log::error("Failed to save offer image to: $outputPath");
                return false;
            }

            // Free resources
            $imagick->clear();
            $imagick->destroy();

            // Return the publicly accessible URL of the generated image
            return 'https://internaltools.godspeedoffers.com/uploads/' . $randomFileName;
        } catch (\Exception $e) {
            Log::error('Error generating offer card: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
