<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMessageNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public array $contactData,
        private readonly string $smtpUsername,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->smtpUsername, $this->contactData['full_name']),
            replyTo: [
                new Address($this->contactData['email'], $this->contactData['full_name']),
            ],
            subject: 'A message from ' . $this->contactData['full_name'],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message-notification',
            text: 'emails.contact-message-notification-text',
            with: [
                'fullName' => $this->contactData['full_name'],
                'email' => $this->contactData['email'],
                'phone' => $this->contactData['phone'] ?: 'Not provided',
                'eventType' => $this->contactData['event_type'] ?: 'Not provided',
                'eventDate' => $this->contactData['event_date'] ?: 'Not provided',
                'bodyMessage' => $this->contactData['message'],
            ],
        );
    }
}
