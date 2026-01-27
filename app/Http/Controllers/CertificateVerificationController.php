<?php

namespace App\Http\Controllers;

use App\Models\CertificateVerificationLog;
use App\Models\FloridaCertificate;
use Illuminate\Http\Request;

class CertificateVerificationController extends Controller
{
    public function verify(Request $request, $verificationHash = null)
    {
        if ($verificationHash) {
            $certificate = FloridaCertificate::where('verification_hash', $verificationHash)->first();
        } else {
            $request->validate([
                'certificate_number' => 'required|string',
            ]);

            $certificate = FloridaCertificate::where('dicds_certificate_number', $request->certificate_number)->first();
        }

        if (! $certificate) {
            return response()->json(['message' => 'Certificate not found'], 404);
        }

        // Log verification attempt
        CertificateVerificationLog::create([
            'certificate_id' => $certificate->id,
            'verified_by' => $request->verified_by,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'verified_at' => now(),
        ]);

        return response()->json([
            'valid' => true,
            'certificate' => [
                'student_name' => $certificate->student_name,
                'course_name' => $certificate->course_name,
                'completion_date' => $certificate->completion_date,
                'dicds_certificate_number' => $certificate->dicds_certificate_number,
                'final_exam_score' => $certificate->final_exam_score,
            ],
        ]);
    }
}
