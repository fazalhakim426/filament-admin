<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;
    public function __construct(
        public $user,
        public string $password
    ) {}


    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $userRole = $this->user->roles->first()->name;
        return new Envelope(
            subject: $userRole. ' Credentials Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.user_credentials',
        );
    }
    public function build()
    {
        return $this->subject('Your Account Details')
            ->markdown('emails.user_credentials');
    }
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
