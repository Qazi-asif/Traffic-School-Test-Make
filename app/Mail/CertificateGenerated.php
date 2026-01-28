<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class CertificateGenerated extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $course;
    public $certificateNumber;
    public $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, $course, string $certificateNumber, string $pdfContent)
    {
        $this->user = $user;
        $this->course = $course;
        $this->certificateNumber = $certificateNumber;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Course Completion Certificate - ' . $this->certificateNumber,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.certificate-generated',
            with: [
                'user' => $this->user,
                'course' => $this->course,
                'certificateNumber' => $this->certificateNumber,
                'studentName' => $this->user->first_name . ' ' . $this->user->last_name,
                'courseName' => $this->course ? $this->course->title : 'Defensive Driving Course',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, 'certificate-' . $this->certificateNumber . '.pdf')
                ->withMime('application/pdf'),
        ];
    }
}