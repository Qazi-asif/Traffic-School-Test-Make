<?php

namespace App\Http\Controllers\Student\Delaware;

use App\Http\Controllers\Controller;
use App\Models\Delaware\Certificate;
use App\Models\Delaware\Enrollment;
use App\Models\Delaware\Course;
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

        return view('student.delaware.certificates.index', compact('certificates'));
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
                'redirect' => route('student.delaware.certificates.view', $existingCertificate->id),
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
            'quiz_rotation_set' => $enrollment->quiz_rotation_set_assigned,
            'insurance_discount_eligible' => $enrollment->isEligibleForInsuranceDiscount(),
            'aggressive_driving_completed' => $enrollment->aggressive_driving_completion,
        ]);

        // Generate PDF
        $pdfPath = $this->generateCertificatePdf($certificate);
        $certificate->update(['pdf_path' => $pdfPath]);

        // Delaware-specific: Generate insurance discount letter if eligible
        if ($certificate->insurance_discount_eligible) {
            $insuranceLetterPath = $this->generateInsuranceDiscountLetter($certificate);
            $certificate->update(['insurance_letter_path' => $insuranceLetterPath]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Certificate generated successfully!',
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificateNumber,
            'insurance_discount_eligible' => $certificate->insurance_discount_eligible,
            'aggressive_driving_completed' => $certificate->aggressive_driving_completed,
            'redirect' => route('student.delaware.certificates.view', $certificate->id),
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

        $filename = "Delaware_Certificate_{$certificate->certificate_number}.pdf";
        
        return Storage::download($certificate->pdf_path, $filename);
    }

    /**
     * Download insurance discount letter (Delaware-specific)
     */
    public function downloadInsuranceLetter($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        if (!$certificate->insurance_discount_eligible) {
            return response()->json([
                'success' => false,
                'error' => 'Insurance discount letter is not available for this certificate.',
            ], 404);
        }

        if (!$certificate->insurance_letter_path || !Storage::exists($certificate->insurance_letter_path)) {
            // Regenerate insurance letter if missing
            $insuranceLetterPath = $this->generateInsuranceDiscountLetter($certificate);
            $certificate->update(['insurance_letter_path' => $insuranceLetterPath]);
        }

        $filename = "Delaware_Insurance_Discount_{$certificate->certificate_number}.pdf";
        
        return Storage::download($certificate->insurance_letter_path, $filename);
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

        return view('student.delaware.certificates.view', compact('certificate'));
    }

    /**
     * Request insurance discount (Delaware-specific)
     */
    public function requestInsuranceDiscount(Request $request, $certificateId)
    {
        $request->validate([
            'insurance_company' => 'required|string|max:255',
            'policy_number' => 'required|string|max:100',
            'contact_email' => 'required|email',
        ]);

        $user = Auth::user();
        $certificate = Certificate::with(['enrollment'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        if (!$certificate->insurance_discount_eligible) {
            return response()->json([
                'success' => false,
                'error' => 'This certificate is not eligible for insurance discount.',
            ], 422);
        }

        // Update enrollment with insurance request
        $certificate->enrollment->update([
            'insurance_discount_requested' => true,
            'insurance_company' => $request->insurance_company,
            'insurance_policy_number' => $request->policy_number,
            'insurance_contact_email' => $request->contact_email,
        ]);

        // Send notification to insurance company (would be implemented)
        // dispatch(new \App\Jobs\SendInsuranceDiscountNotificationJob($certificate, $request->all()));

        return response()->json([
            'success' => true,
            'message' => 'Insurance discount request submitted successfully. Your insurance company will be notified.',
        ]);
    }

    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber($enrollment)
    {
        $prefix = 'DE';
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
            'quiz_rotation_set' => $certificate->quiz_rotation_set,
            'insurance_discount_eligible' => $certificate->insurance_discount_eligible,
            'aggressive_driving_completed' => $certificate->aggressive_driving_completed,
            'state_seal' => asset('images/delaware-state-seal.png'),
            'school_logo' => asset('images/school-logo.png'),
        ];

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('certificates.delaware.template', $data);
        $pdf->setPaper('letter', 'landscape');
        
        // Save PDF to storage
        $filename = "certificates/delaware/certificate_{$certificate->id}_{$certificate->certificate_number}.pdf";
        Storage::put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Generate insurance discount letter (Delaware-specific)
     */
    private function generateInsuranceDiscountLetter($certificate)
    {
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;
        
        $data = [
            'certificate' => $certificate,
            'enrollment' => $enrollment,
            'student_name' => $certificate->student_name,
            'student_address' => $user->address ?? '',
            'student_city' => $user->city ?? '',
            'student_state' => $user->state ?? 'DE',
            'student_zip' => $user->zip_code ?? '',
            'course_title' => $certificate->course_title,
            'completion_date' => $certificate->completion_date->format('F j, Y'),
            'certificate_number' => $certificate->certificate_number,
            'total_hours' => number_format($certificate->total_hours, 1),
            'school_name' => config('app.name'),
            'school_address' => config('school.address', ''),
            'school_phone' => config('school.phone', ''),
            'director_signature' => asset('images/director-signature.png'),
        ];

        // Generate insurance discount letter PDF
        $pdf = Pdf::loadView('certificates.delaware.insurance-letter', $data);
        $pdf->setPaper('letter', 'portrait');
        
        // Save PDF to storage
        $filename = "certificates/delaware/insurance_letter_{$certificate->id}_{$certificate->certificate_number}.pdf";
        Storage::put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Check insurance discount eligibility
     */
    public function checkInsuranceEligibility($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        return response()->json([
            'success' => true,
            'insurance_discount_eligible' => $certificate->insurance_discount_eligible,
            'course_eligible' => $certificate->enrollment->course->insurance_discount_eligible,
            'completion_requirements_met' => $certificate->enrollment->completed_at && $certificate->enrollment->final_exam_completed,
            'aggressive_driving_completed' => $certificate->aggressive_driving_completed,
        ]);
    }
}