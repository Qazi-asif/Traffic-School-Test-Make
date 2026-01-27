<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CertificateDeliveryMail extends Mailable
{
    use Queueable, SerializesModels;

    public $certificate;

    public $user;

    public $pdfPath;

    public function __construct($certificate, $pdfPath)
    {
        $this->certificate = $certificate;
        $this->user = $certificate->user;
        $this->pdfPath = $pdfPath;
    }

    public function build()
    {
        return $this->subject('Your Florida BDI Course Completion Certificate')
            ->view('emails.certificate-delivery')
            ->attach($this->pdfPath, [
                'as' => 'Certificate_'.$this->certificate->certificate_number.'.pdf',
                'mime' => 'application/pdf',
            ]);
    }
}
