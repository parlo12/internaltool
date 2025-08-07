<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The email details.
     *
     * @var array
     */
    public $details;

    /**
     * Create a new message instance.
     *
     * @param array $details (Should include 'attachments' key if needed)
     */
    public function __construct(array $details)
    {
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->view('emails.contact')
            ->with(['details' => $this->details])
            ->from($this->details['from_email'], $this->details['from_name'] ?? $this->details['from_email'])
            ->subject($this->details['subject']);

        // Attach files if provided
        if (!empty($this->details['attachments'])) {
            foreach ($this->details['attachments'] as $file) {
                if (isset($file['file']) && file_exists($file['file'])) {
                    $email->attach($file['file'], [
                        'as' => $file['name'] ?? basename($file['file']),
                        'mime' => $file['mime'] ?? mime_content_type($file['file']),
                    ]);
                }
            }
        }

        return $email;
    }
}
