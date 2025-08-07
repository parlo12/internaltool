<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;
    public $attachments;

    /**
     * Create a new message instance.
     *
     * @param array $details
     * @param array $attachments (optional) - Each item: ['file' => ..., 'name' => ..., 'mime' => ...]
     */
    public function __construct($details, $attachments = [])
    {
        $this->details = $details;
        $this->attachments = $attachments;
    }

    public function build()
    {
        $email = $this->from($this->details['from_email'], $this->details['from_name'])
            ->subject($this->details['subject'])
            ->view('emails.contact')
            ->with(['details' => $this->details]);

        foreach ($this->attachments as $file) {
            if (!file_exists($file['file'])) {
                continue; // or log error
            }

            $email->attach($file['file'], [
                'as' => $file['name'] ?? basename($file['file']),
                'mime' => $file['mime'] ?? mime_content_type($file['file']),
            ]);
        }

        return $email;
    }
}
