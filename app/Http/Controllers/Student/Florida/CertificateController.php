<?php

namespace App\Http\Controllers\Student\Florida;

use App\Http\Controllers\Controller;
use App\Models\Florida\Certificate;
use App\Models\Florida\Enrollment;
use App\Models\Florida\Course;
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

        return view('student.florida.certificates.index', compact('certificates'));
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

        // Check if certificate already exists
        $existingCertificate = Certificate::where('enrollment_id', $enrollmentId)->first();
        if ($existingCertificate) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate already exists.',
                'certificate_id' => $existingCertificate->id,
                'redirect' => route('student.florida.certificates.view', $existingCertificate->id),
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
            'flhsmv_submitted' => false,
            'dicds_transmission_id' => null,
        ]);

        // Generate PDF
        $pdfPath = $this->generateCertificatePdf($certificate);
        $certificate->update(['pdf_path' => $pdfPath]);

        // Florida-specific: Queue FLHSMV/DICDS submission
        if ($enrollment->course->requires_state_submission) {
            dispatch(new \App\Jobs\SubmitFloridaCertificateJob($certificate));
        }

        return response()->json([
            'success' => true,
            'message' => 'Certificate generated successfully!',
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificateNumber,
            'redirect' => route('student.florida.certificates.view', $certificate->id),
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

        $filename = "Florida_Certificate_{$certificate->certificate_number}.pdf";
        
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

        return view('student.florida.certificates.view', compact('certificate'));
    }

    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber($enrollment)
    {
        $prefix = 'FL';
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
            'state_seal' => asset('images/florida-state-seal.png'),
            'school_logo' => asset('images/school-logo.png'),
        ];

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('certificates.florida.template', $data);
        $pdf->setPaper('letter', 'landscape');
        
        // Save PDF to storage
        $filename = "certificates/florida/certificate_{$certificate->id}_{$certificate->certificate_number}.pdf";
        Storage::put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Check FLHSMV submission status
     */
    public function checkSubmissionStatus($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        return response()->json([
            'success' => true,
            'flhsmv_submitted' => $certificate->flhsmv_submitted,
            'submission_date' => $certificate->flhsmv_submission_date,
            'dicds_transmission_id' => $certificate->dicds_transmission_id,
            'submission_status' => $certificate->flhsmv_submission_status ?? 'pending',
        ]);
    }

    /**
     * Resend certificate to FLHSMV (if failed)
     */
    public function resendToFlhsmv($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        if (!$certificate->enrollment->course->requires_state_submission) {
            return response()->json([
                'success' => false,
                'error' => 'This course does not require state submission.',
            ], 422);
        }

        if ($certificate->flhsmv_submitted && $certificate->flhsmv_submission_status === 'success') {
            return response()->json([
                'success' => false,
                'error' => 'Certificate has already been successfully submitted to FLHSMV.',
            ], 422);
        }

        // Queue resubmission
        dispatch(new \App\Jobs\SubmitFloridaCertificateJob($certificate));

        return response()->json([
            'success' => true,
            'message' => 'Certificate resubmission queued. You will be notified when complete.',
        ]);
    }
}