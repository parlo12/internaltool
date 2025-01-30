<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function build()
    {
        return $this->from($this->details['from_email'], $this->details['from_name']) // Set dynamic "From" address
                    ->subject($this->details['subject']) // Set dynamic subject
                    ->view('emails.contact') // Your email view
                    ->with('details', $this->details);
    }
}
