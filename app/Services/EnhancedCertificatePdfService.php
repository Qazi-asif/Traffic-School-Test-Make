<?php

namespace App\Services;

use App\Models\FloridaCertificate;
use Barryvdh\DomPDF\Facade\Pdf;

class EnhancedCertificatePdfService
{
    public function generateCertificate($certificateId, $format = 'pdf')
    {
        $certificate = FloridaCertificate::with(['user', 'course'])->findOrFail($certificateId);

        $data = [
            'certificate' => $certificate,
            'user' => $certificate->user,
            'course' => $certificate->course,
            'certificate_number' => $this->formatCertificateNumber($certificate),
            'completion_date' => $certificate->completion_date->format('m/d/Y'),
            'date_of_birth' => $certificate->user->date_of_birth->format('m/d/Y'),
            'exam_score' => $certificate->exam_score ?? 0,
            'qr_code' => $this->generateQRCode($certificate),
        ];

        if ($format === 'html') {
            return view('certificates.florida-bdi-template', $data)->render();
        }

        $pdf = Pdf::loadView('certificates.florida-bdi-template', $data);
        $pdf->setPaper('letter', 'portrait');

        return $pdf;
    }

    public function downloadCertificate($certificateId)
    {
        $certificate = FloridaCertificate::findOrFail($certificateId);
        $pdf = $this->generateCertificate($certificateId, 'pdf');

        $filename = 'Certificate_'.$this->formatCertificateNumber($certificate).'.pdf';

        return $pdf->download($filename);
    }

    public function streamCertificate($certificateId)
    {
        $pdf = $this->generateCertificate($certificateId, 'pdf');

        return $pdf->stream();
    }

    private function formatCertificateNumber($certificate)
    {
        if ($certificate->dmv_certificate_number) {
            return $certificate->dmv_certificate_number;
        }

        $schoolId = $certificate->school_id ?? '10076';
        $sequentialNumber = str_pad($certificate->id, 4, '0', STR_PAD_LEFT);

        return $schoolId.'-'.$sequentialNumber;
    }

    private function generateQRCode($certificate)
    {
        $verificationUrl = route('certificate.verify', ['number' => $certificate->certificate_number]);

        // Simple QR code data - you can enhance with actual QR library
        return base64_encode($verificationUrl);
    }
}
