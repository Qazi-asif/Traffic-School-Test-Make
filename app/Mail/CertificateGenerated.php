<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificateGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    public $course;

    public $certificateNumber;

    public $certificatePdf;

    public function __construct($user, $course, $certificateNumber, $certificatePdf = null)
    {
        $this->user = $user;
        $this->course = $course;
        $this->certificateNumber = $certificateNumber;
        $this->certificatePdf = $certificatePdf;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Certificate Generated - '.$this->course->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate-generated',
        );
    }

    public function attachments(): array
    {
        $attachments = [];

        if ($this->certificatePdf) {
            $attachments[] = Attachment::fromData(
                fn () => $this->certificatePdf,
                'certificate-'.$this->certificateNumber.'.pdf'
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}
