<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class CertificateGeneratedNotification extends Notification
{
    use Queueable;

    public $certificate;

    public function __construct($certificate)
    {
        $this->certificate = $certificate;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mail = (new MailMessage)
            ->subject('Your Certificate is Ready!')
            ->view('emails.certificates.generated', [
                'user' => $notifiable,
                'certificate' => $this->certificate,
            ]);

        // Attach PDF if exists
        $pdfPath = $this->certificate->pdf_path ?? null;
        if ($pdfPath && Storage::exists($pdfPath)) {
            $mail->attach(Storage::path($pdfPath), [
                'as' => 'certificate.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
