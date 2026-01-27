<?php

namespace App\Mail;

use App\Models\Certificate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateSubmission extends Mailable
{
    use Queueable, SerializesModels;

    public $certificate;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }

    public function build()
    {
        return $this->subject('Certificate Submission - '.$this->certificate->student_name)
            ->view('emails.certificate-submission')
            ->with([
                'certificate' => $this->certificate,
            ]);
    }
}
