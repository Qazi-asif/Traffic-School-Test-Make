<?php

namespace App\Http\Controllers\Student\Texas;

use App\Http\Controllers\Controller;
use App\Models\Texas\Certificate;
use App\Models\Texas\Enrollment;
use App\Models\Texas\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List user's certificates
     */
    public function index()
    {
        $user = Auth::user();
        
        $certificates = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.texas.certificates.index', compact('certificates'));
    }

    /**
     * Create certificate on course completion
     */
    public function generate($enrollmentId)
    {
        $user = Auth::user();
        $enrollment = Enrollment::with('course')
            ->where('user_id', $user->id)
            ->where('id', $enrollmentId)
            ->firstOrFail();

        // Check if course is completed
        if (!$enrollment->completed_at || $enrollment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'error' => 'Course must be completed before generating certificate.',
            ], 422);
        }

        // Texas-specific: Check proctoring requirements
        if ($enrollment->proctoring_required && !$enrollment->proctoring_completed) {
            return response()->json([
                'success' => false,
                'error' => 'Proctoring session must be completed before generating certificate.',
            ], 422);
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('enrollment_id', $enrollmentId)->first();
        if ($existingCertificate) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate already exists.',
                'certificate_id' => $existingCertificate->id,
                'redirect' => route('student.texas.certificates.view', $existingCertificate->id),
            ]);
        }

        // Generate certificate number
        $certificateNumber = $this->generateCertificateNumber($enrollment);

        // Create certificate record
        $certificate = Certificate::create([
            'enrollment_id' => $enrollmentId,
            'user_id' => $user->id,
            'course_id' => $enrollment->course_id,
            'certificate_number' => $certificateNumber,
            'student_name' => $user->full_name,
            'course_title' => $enrollment->course->title,
            'completion_date' => $enrollment->completed_at,
            'issue_date' => now(),
            'final_score' => $enrollment->quiz_average ?? 0,
            'total_hours' => $enrollment->total_time_spent / 60, // Convert minutes to hours
            'status' => 'issued',
            'proctoring_verified' => $enrollment->proctoring_completed,
            'video_completion_verified' => $enrollment->video_completion_verified ?? false,
        ]);

        // Generate PDF
        $pdfPath = $this->generateCertificatePdf($certificate);
        $certificate->update(['pdf_path' => $pdfPath]);

        return response()->json([
            'success' => true,
            'message' => 'Certificate generated successfully!',
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificateNumber,
            'proctoring_verified' => $certificate->proctoring_verified,
            'redirect' => route('student.texas.certificates.view', $certificate->id),
        ]);
    }

    /**
     * Download certificate PDF
     */
    public function download($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        if (!$certificate->pdf_path || !Storage::exists($certificate->pdf_path)) {
            // Regenerate PDF if missing
            $pdfPath = $this->generateCertificatePdf($certificate);
            $certificate->update(['pdf_path' => $pdfPath]);
        }

        $filename = "Texas_Certificate_{$certificate->certificate_number}.pdf";
        
        return Storage::download($certificate->pdf_path, $filename);
    }

    /**
     * Display certificate
     */
    public function view($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        return view('student.texas.certificates.view', compact('certificate'));
    }

    /**
     * Verify proctoring session (Texas-specific)
     */
    public function verifyProctoring(Request $request, $certificateId)
    {
        $request->validate([
            'proctor_id' => 'required|string',
            'session_id' => 'required|string',
            'verification_code' => 'required|string',
        ]);

        $user = Auth::user();
        $certificate = Certificate::with(['enrollment'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        // Verify proctoring session with external service
        $verificationResult = $this->verifyProctoringSession(
            $request->proctor_id,
            $request->session_id,
            $request->verification_code
        );

        if ($verificationResult['success']) {
            $certificate->update([
                'proctoring_verified' => true,
                'proctor_verification_data' => json_encode($verificationResult['data']),
            ]);

            $certificate->enrollment->update([
                'proctoring_completed' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proctoring session verified successfully.',
                'verification_data' => $verificationResult['data'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Proctoring verification failed: ' . $verificationResult['error'],
        ], 422);
    }

    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber($enrollment)
    {
        $prefix = 'TX';
        $courseCode = strtoupper(substr($enrollment->course->title, 0, 3));
        $timestamp = now()->format('ymd');
        $sequence = str_pad($enrollment->id, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$courseCode}{$timestamp}{$sequence}";
    }

    /**
     * Generate certificate PDF
     */
    private function generateCertificatePdf($certificate)
    {
        $data = [
            'certificate' => $certificate,
            'student_name' => $certificate->student_name,
            'course_title' => $certificate->course_title,
            'completion_date' => $certificate->completion_date->format('F j, Y'),
            'issue_date' => $certificate->issue_date->format('F j, Y'),
            'certificate_number' => $certificate->certificate_number,
            'final_score' => $certificate->final_score,
            'total_hours' => number_format($certificate->total_hours, 1),
            'proctoring_verified' => $certificate->proctoring_verified,
            'video_completion_verified' => $certificate->video_completion_verified,
            'state_seal' => asset('images/texas-state-seal.png'),
            'school_logo' => asset('images/school-logo.png'),
        ];

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('certificates.texas.template', $data);
        $pdf->setPaper('letter', 'landscape');
        
        // Save PDF to storage
        $filename = "certificates/texas/certificate_{$certificate->id}_{$certificate->certificate_number}.pdf";
        Storage::put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Verify proctoring session with external service
     */
    private function verifyProctoringSession($proctorId, $sessionId, $verificationCode)
    {
        // This would integrate with a real proctoring service like ProctorU, Examity, etc.
        // For now, we'll simulate the verification
        
        try {
            // Simulate API call to proctoring service
            $response = [
                'success' => true,
                'data' => [
                    'proctor_id' => $proctorId,
                    'session_id' => $sessionId,
                    'verification_code' => $verificationCode,
                    'verified_at' => now()->toISOString(),
                    'proctor_name' => 'John Doe', // Would come from API
                    'session_duration' => 120, // minutes
                    'integrity_score' => 95, // percentage
                ],
            ];

            // In production, you would make an actual HTTP request:
            // $client = new \GuzzleHttp\Client();
            // $response = $client->post('https://proctoring-service.com/api/verify', [
            //     'json' => [
            //         'proctor_id' => $proctorId,
            //         'session_id' => $sessionId,
            //         'verification_code' => $verificationCode,
            //     ]
            // ]);

            return $response;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Proctoring service unavailable. Please try again later.',
            ];
        }
    }

    /**
     * Get proctoring requirements
     */
    public function getProctoringRequirements($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        return response()->json([
            'success' => true,
            'proctoring_required' => $certificate->enrollment->proctoring_required,
            'proctoring_completed' => $certificate->enrollment->proctoring_completed,
            'proctoring_verified' => $certificate->proctoring_verified,
            'course_requires_proctoring' => $certificate->enrollment->course->requires_proctoring,
        ]);
    }
}