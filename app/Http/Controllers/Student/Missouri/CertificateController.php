<?php

namespace App\Http\Controllers\Student\Missouri;

use App\Http\Controllers\Controller;
use App\Models\Missouri\Certificate;
use App\Models\Missouri\Enrollment;
use App\Models\Missouri\Course;
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

        return view('student.missouri.certificates.index', compact('certificates'));
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

        // Missouri-specific: Check Form 4444 eligibility
        if ($enrollment->course->requires_form_4444 && !$enrollment->form_4444_eligible) {
            return response()->json([
                'success' => false,
                'error' => 'Form 4444 requirements not met. Please complete all course requirements.',
            ], 422);
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('enrollment_id', $enrollmentId)->first();
        if ($existingCertificate) {
            return response()->json([
                'success' => true,
                'message' => 'Certificate already exists.',
                'certificate_id' => $existingCertificate->id,
                'redirect' => route('student.missouri.certificates.view', $existingCertificate->id),
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
            'form_4444_generated' => $enrollment->course->requires_form_4444,
            'point_reduction_eligible' => $enrollment->point_reduction_eligible ?? false,
        ]);

        // Generate PDF
        $pdfPath = $this->generateCertificatePdf($certificate);
        $certificate->update(['pdf_path' => $pdfPath]);

        // Missouri-specific: Generate Form 4444 if required
        if ($enrollment->course->requires_form_4444) {
            $form4444Path = $this->generateForm4444($certificate);
            $certificate->update(['form_4444_path' => $form4444Path]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Certificate generated successfully!',
            'certificate_id' => $certificate->id,
            'certificate_number' => $certificateNumber,
            'form_4444_generated' => $certificate->form_4444_generated,
            'redirect' => route('student.missouri.certificates.view', $certificate->id),
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

        $filename = "Missouri_Certificate_{$certificate->certificate_number}.pdf";
        
        return Storage::download($certificate->pdf_path, $filename);
    }

    /**
     * Download Form 4444 (Missouri-specific)
     */
    public function downloadForm4444($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        if (!$certificate->form_4444_generated) {
            return response()->json([
                'success' => false,
                'error' => 'Form 4444 is not available for this certificate.',
            ], 404);
        }

        if (!$certificate->form_4444_path || !Storage::exists($certificate->form_4444_path)) {
            // Regenerate Form 4444 if missing
            $form4444Path = $this->generateForm4444($certificate);
            $certificate->update(['form_4444_path' => $form4444Path]);
        }

        $filename = "Missouri_Form4444_{$certificate->certificate_number}.pdf";
        
        return Storage::download($certificate->form_4444_path, $filename);
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

        return view('student.missouri.certificates.view', compact('certificate'));
    }

    /**
     * Generate unique certificate number
     */
    private function generateCertificateNumber($enrollment)
    {
        $prefix = 'MO';
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
            'point_reduction_eligible' => $certificate->point_reduction_eligible,
            'state_seal' => asset('images/missouri-state-seal.png'),
            'school_logo' => asset('images/school-logo.png'),
        ];

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('certificates.missouri.template', $data);
        $pdf->setPaper('letter', 'landscape');
        
        // Save PDF to storage
        $filename = "certificates/missouri/certificate_{$certificate->id}_{$certificate->certificate_number}.pdf";
        Storage::put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Generate Form 4444 (Missouri-specific)
     */
    private function generateForm4444($certificate)
    {
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;
        
        $data = [
            'certificate' => $certificate,
            'enrollment' => $enrollment,
            'student_name' => $certificate->student_name,
            'student_address' => $user->address ?? '',
            'student_city' => $user->city ?? '',
            'student_state' => $user->state ?? 'MO',
            'student_zip' => $user->zip_code ?? '',
            'driver_license' => $enrollment->driver_license_number ?? '',
            'date_of_birth' => $user->date_of_birth ? $user->date_of_birth->format('m/d/Y') : '',
            'course_title' => $certificate->course_title,
            'completion_date' => $certificate->completion_date->format('m/d/Y'),
            'certificate_number' => $certificate->certificate_number,
            'school_name' => config('app.name'),
            'school_address' => config('school.address', ''),
            'school_phone' => config('school.phone', ''),
            'instructor_signature' => asset('images/instructor-signature.png'),
        ];

        // Generate Form 4444 PDF
        $pdf = Pdf::loadView('certificates.missouri.form4444', $data);
        $pdf->setPaper('letter', 'portrait');
        
        // Save PDF to storage
        $filename = "certificates/missouri/form4444_{$certificate->id}_{$certificate->certificate_number}.pdf";
        Storage::put($filename, $pdf->output());
        
        return $filename;
    }

    /**
     * Check point reduction eligibility
     */
    public function checkPointReduction($certificateId)
    {
        $user = Auth::user();
        $certificate = Certificate::with(['enrollment.course'])
            ->whereHas('enrollment', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->findOrFail($certificateId);

        return response()->json([
            'success' => true,
            'point_reduction_eligible' => $certificate->point_reduction_eligible,
            'form_4444_generated' => $certificate->form_4444_generated,
            'course_requires_form_4444' => $certificate->enrollment->course->requires_form_4444,
        ]);
    }
}