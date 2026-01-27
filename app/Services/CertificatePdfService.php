<?php

namespace App\Services;

use App\Models\FloridaCertificate;
use App\Models\UserCourseEnrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CertificatePdfService
{
    public function __construct(protected CourtCodeService $courtCodeService) {}

    public function generateCertificate(FloridaCertificate $certificate)
    {
        $html = $this->buildCertificateHtml($certificate);

        $pdf = Pdf::loadHTML($html)
            ->setPaper('letter', 'landscape')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        $filename = "certificate_{$certificate->dicds_certificate_number}.pdf";
        $path = "certificates/{$filename}";

        Storage::put($path, $pdf->output());

        $certificate->update([
            'pdf_path' => $path,
            'generated_at' => now(),
        ]);

        return $path;
    }

    public function getCourtCode(UserCourseEnrollment $enrollment): ?string
    {
        if (! $enrollment->court_id) {
            return null;
        }

        $court = $enrollment->court;
        if (! $court) {
            return null;
        }

        $tvccCode = $this->courtCodeService->findForCourt($court, 'tvcc')
            ->where('is_active', true)
            ->first();

        return $tvccCode?->code_value;
    }

    private function buildCertificateHtml(FloridaCertificate $certificate)
    {
        return view('certificates.florida-template', compact('certificate'))->render();
    }
}
