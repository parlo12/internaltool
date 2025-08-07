<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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
     * The email attachments.
     *
     * @var array
     */
    public $attachments;

    /**
     * Create a new message instance.
     *
     * @param array $details
     * @param array $attachments (optional) - Each item: ['path' => ..., 'name' => ..., 'mime' => ...]
     */
    public function __construct(array $details, array $attachments = [])
    {
        $this->details = $details;
        $this->attachments = $attachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->details['from_email'],
            subject: $this->details['subject'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact',
            with: ['details' => $this->details],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        foreach ($this->attachments as $file) {
            if (!file_exists($file['path'])) {
                continue; // Consider logging this
            }

            $attachments[] = Attachment::fromPath($file['path'])
                ->as($file['name'] ?? basename($file['path']))
                ->withMime($file['mime'] ?? mime_content_type($file['path']));
        }

        return $attachments;
    }
}