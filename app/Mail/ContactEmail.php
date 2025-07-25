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
    return $this->from($this->details['from_email'], $this->details['from_name']) // Dynamic "From"
                ->subject($this->details['subject']) // Dynamic subject
                ->view('emails.contact') // Dynamic body from Blade view
                ->with([
                    'details' => $this->details // Pass full dynamic data to the view
                ]);
}

}
