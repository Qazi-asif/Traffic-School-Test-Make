<?php

namespace App\Http\Controllers;

use App\Models\FloridaCertificate;
use App\Models\UserCourseEnrollment;
use App\Services\CertificatePdfService;
use App\Services\FloridaDicdsSoapService;

class DicdsIntegrationController extends Controller
{
    private $dicdsService;

    private $pdfService;

    public function __construct(FloridaDicdsSoapService $dicdsService, CertificatePdfService $pdfService)
    {
        $this->dicdsService = $dicdsService;
        $this->pdfService = $pdfService;
    }

    public function submitToDicds($enrollmentId)
    {
        $enrollment = UserCourseEnrollment::findOrFail($enrollmentId);

        if ($enrollment->dicds_submission_status === 'approved') {
            return response()->json(['message' => 'Already submitted'], 400);
        }

        try {
            $response = $this->dicdsService->submitCompletion($enrollment);

            if ($response->StatusCode === 'CC000') {
                $certificate = $this->generateCertificate($enrollment, $response->CertificateNumber);

                return response()->json([
                    'success' => true,
                    'certificate_number' => $response->CertificateNumber,
                    'certificate_id' => $certificate->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $response->StatusMessage,
            ], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getSubmissionStatus($enrollmentId)
    {
        $enrollment = UserCourseEnrollment::with('dicdsSubmissionLogs')->findOrFail($enrollmentId);

        return response()->json([
            'status' => $enrollment->dicds_submission_status,
            'certificate_number' => $enrollment->dicds_certificate_number,
            'logs' => $enrollment->dicdsSubmissionLogs,
        ]);
    }

    public function testConnection()
    {
        try {
            $client = new \SoapClient('https://services.flhsmv.gov/DriverSchoolWebService/DriverSchoolWebService.asmx?WSDL');

            return response()->json(['success' => true, 'message' => 'Connection successful']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function generateCertificate($enrollment, $certificateNumber)
    {
        $certificate = FloridaCertificate::create([
            'enrollment_id' => $enrollment->id,
            'dicds_certificate_number' => $certificateNumber,
            'student_name' => $enrollment->user->full_name,
            'completion_date' => now(),
            'course_name' => $enrollment->floridaCourse->title,
            'final_exam_score' => $enrollment->final_exam_score,
            'driver_license_number' => $enrollment->user->drivers_license_number,
            'citation_number' => $enrollment->citation_number,
            'citation_county' => $enrollment->citation_county,
            'traffic_school_due_date' => $enrollment->traffic_school_due_date,
            'student_address' => $enrollment->user->full_address,
            'student_date_of_birth' => $enrollment->user->date_of_birth,
            'court_name' => $enrollment->court_name,
            'verification_hash' => bin2hex(random_bytes(16)),
        ]);

        $this->pdfService->generateCertificate($certificate);

        return $certificate;
    }
}
