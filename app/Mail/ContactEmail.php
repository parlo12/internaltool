<?php

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
     * @param array $details
     * @param array $attachments (optional) - Each item: ['path' => ..., 'name' => ..., 'mime' => ...]
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
                      ->with([
                          'details' => $this->details
                      ]);

        // Attach files if provided
        foreach ($this->attachments as $file) {
            $email->attach($file['path'], [
                'as' => $file['name'] ?? basename($file['path']),
                'mime' => $file['mime'] ?? null,
            ]);
        }

        return $email;
    }
}
