<?php

namespace App\Domains\Platform\Mail;

use App\Models\User;
use App\Domains\Platform\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Vendor $vendor,
        public User $owner,
        public string $temporaryPassword,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to GarageHQ - Your Account Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-welcome',
        );
    }
}
