<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Certificate;
use App\Models\FloridaCertificate;
use App\Models\MissouriCertificate;
use App\Models\TexasCertificate;
use App\Models\DelawareCertificate;
use App\Models\UserCourseEnrollment;
use App\Models\StateStamp;
use App\Services\MultiStateCertificateService;
use App\Mail\CertificateGenerated;
use Barryvdh\DomPDF\Facade\Pdf;

class MultiStateCertificateController extends Controller
{
    protected $certificateService;

    public function __construct(MultiStateCertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    /**
     * Display certificate selection page
     */
    public function index()
    {
        $user = Auth::user();
        
        $enrollments = UserCourseEnrollment::with(['course'])
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('certificates.select', compact('enrollments'));
    }

    /**
     * Generate certificate for enrollment
     */
    public function generate(Request $request, $enrollmentId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::with(['user', 'course'])
            ->where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment not found or access denied.');
        }

        if ($enrollment->status !== 'completed') {
            return redirect()->back()->with('error', 'Course must be completed to generate certificate.');
        }

        try {
            // Generate certificate using the service
            $result = $this->certificateService->generateCertificate($enrollment);

            if (!$result['success']) {
                return redirect()->back()->with('error', 'Failed to generate certificate: ' . $result['error']);
            }

            // Store certificate in database
            $certificate = $this->storeCertificate($enrollment, $result['certificate_data']);

            // Return certificate view
            return $this->viewCertificate($certificate);

        } catch (\Exception $e) {
            \Log::error('Certificate generation failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to generate certificate. Please try again.');
        }
    }

    /**
     * Download certificate as PDF
     */
    public function download(Request $request, $enrollmentId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::with(['user', 'course'])
            ->where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }

        if ($enrollment->status !== 'completed') {
            return response()->json(['error' => 'Course not completed'], 400);
        }

        try {
            // Generate certificate using the service
            $result = $this->certificateService->generateCertificate($enrollment);

            if (!$result['success']) {
                return response()->json(['error' => $result['error']], 500);
            }

            // Store certificate in database
            $certificate = $this->storeCertificate($enrollment, $result['certificate_data']);

            // Return PDF download
            $filename = $result['filename'];
            
            return response($result['pdf_content'])
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            \Log::error('Certificate download failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to download certificate'], 500);
        }
    }

    /**
     * View certificate in browser
     */
    public function view(Request $request, $enrollmentId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::with(['user', 'course'])
            ->where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            return redirect()->back()->with('error', 'Enrollment not found or access denied.');
        }

        if ($enrollment->status !== 'completed') {
            return redirect()->back()->with('error', 'Course must be completed to view certificate.');
        }

        try {
            // Get or create certificate
            $certificate = $this->getOrCreateCertificate($enrollment);
            
            return $this->viewCertificate($certificate);

        } catch (\Exception $e) {
            \Log::error('Certificate view failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to view certificate.');
        }
    }

    /**
     * Email certificate to student
     */
    public function email(Request $request, $enrollmentId)
    {
        $user = Auth::user();
        
        $enrollment = UserCourseEnrollment::with(['user', 'course'])
            ->where('id', $enrollmentId)
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'Enrollment not found'], 404);
        }

        if ($enrollment->status !== 'completed') {
            return response()->json(['error' => 'Course not completed'], 400);
        }

        try {
            // Generate certificate using the service
            $result = $this->certificateService->generateCertificate($enrollment);

            if (!$result['success']) {
                return response()->json(['error' => $result['error']], 500);
            }

            // Store certificate in database
            $certificate = $this->storeCertificate($enrollment, $result['certificate_data']);

            // Send email
            $recipientEmail = $request->input('email', $user->email);
            
            Mail::to($recipientEmail)->send(new CertificateGenerated(
                $user,
                $enrollment->course,
                $certificate->certificate_number,
                $result['pdf_content']
            ));

            // Update certificate as sent
            $certificate->update([
                'is_sent_to_student' => true,
                'sent_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Certificate emailed successfully',
                'sent_to' => $recipientEmail
            ]);

        } catch (\Exception $e) {
            \Log::error('Certificate email failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to email certificate'], 500);
        }
    }

    /**
     * Verify certificate by number
     */
    public function verify(Request $request)
    {
        $request->validate([
            'certificate_number' => 'required|string',
            'student_name' => 'nullable|string',
        ]);

        $certificateNumber = $request->certificate_number;
        $studentName = $request->student_name;

        // Search in all certificate tables
        $certificate = $this->findCertificateByNumber($certificateNumber);

        if (!$certificate) {
            return response()->json([
                'valid' => false,
                'message' => 'Certificate not found'
            ]);
        }

        // Verify student name if provided
        if ($studentName && stripos($certificate->student_name, $studentName) === false) {
            return response()->json([
                'valid' => false,
                'message' => 'Certificate found but student name does not match'
            ]);
        }

        // Log verification attempt
        DB::table('certificate_verification_logs')->insert([
            'certificate_id' => $certificate->id,
            'certificate_type' => get_class($certificate),
            'verified_by' => $request->ip(),
            'verification_method' => 'web',
            'verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'valid' => true,
            'certificate' => [
                'certificate_number' => $certificate->certificate_number,
                'student_name' => $certificate->student_name,
                'course_name' => $certificate->course_name,
                'completion_date' => $certificate->completion_date->format('F j, Y'),
                'final_exam_score' => $certificate->final_exam_score . '%',
                'state_code' => $this->getStateCode($certificate),
            ]
        ]);
    }

    /**
     * Admin dashboard for certificate management
     */
    public function dashboard(Request $request)
    {
        $this->authorize('admin');

        $query = Certificate::with(['enrollment.user', 'enrollment.course']);

        // Apply filters
        if ($request->state_code) {
            $query->where('state_code', $request->state_code);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->date_from) {
            $query->whereDate('completion_date', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('completion_date', '<=', $request->date_to);
        }

        $certificates = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = [
            'total_certificates' => Certificate::count(),
            'sent_certificates' => Certificate::where('is_sent_to_student', true)->count(),
            'pending_certificates' => Certificate::where('is_sent_to_student', false)->count(),
            'state_submitted' => Certificate::where('is_sent_to_state', true)->count(),
        ];

        // Get state breakdown
        $stateBreakdown = Certificate::select('state_code', DB::raw('count(*) as count'))
            ->groupBy('state_code')
            ->get()
            ->pluck('count', 'state_code');

        return view('admin.certificates.dashboard', compact('certificates', 'stats', 'stateBreakdown'));
    }

    /**
     * Bulk certificate operations
     */
    public function bulkAction(Request $request)
    {
        $this->authorize('admin');

        $request->validate([
            'action' => 'required|in:email,regenerate,delete',
            'certificate_ids' => 'required|array',
            'certificate_ids.*' => 'exists:certificates,id',
        ]);

        $certificates = Certificate::whereIn('id', $request->certificate_ids)->get();
        $results = [];

        foreach ($certificates as $certificate) {
            try {
                switch ($request->action) {
                    case 'email':
                        $this->emailCertificate($certificate);
                        $results[] = "Certificate {$certificate->certificate_number} emailed successfully";
                        break;
                    case 'regenerate':
                        $this->regenerateCertificate($certificate);
                        $results[] = "Certificate {$certificate->certificate_number} regenerated successfully";
                        break;
                    case 'delete':
                        $certificate->delete();
                        $results[] = "Certificate {$certificate->certificate_number} deleted successfully";
                        break;
                }
            } catch (\Exception $e) {
                $results[] = "Failed to process certificate {$certificate->certificate_number}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results
        ]);
    }

    /**
     * Store certificate in appropriate table
     */
    private function storeCertificate(UserCourseEnrollment $enrollment, array $certificateData): Certificate
    {
        $stateCode = $this->getEnrollmentStateCode($enrollment);
        
        // Create unified certificate record
        $certificate = Certificate::updateOrCreate(
            ['enrollment_id' => $enrollment->id],
            [
                'user_id' => $enrollment->user_id,
                'certificate_number' => $certificateData['certificate_number'],
                'certificate_type' => $this->getCertificateType($enrollment, $stateCode),
                'state_code' => $stateCode,
                'student_name' => $certificateData['student_name'],
                'course_title' => $certificateData['course_title'] ?? $enrollment->course->title,
                'completion_date' => $enrollment->completed_at,
                'final_exam_score' => $enrollment->final_exam_score ?? 95,
                'verification_hash' => Str::random(32),
                'generated_at' => now(),
                'status' => Certificate::STATUS_GENERATED,
                'metadata' => $certificateData,
            ]
        );

        // Also create state-specific certificate record for backward compatibility
        $this->createStateSpecificCertificate($enrollment, $certificateData, $stateCode);

        return $certificate;
    }

    /**
     * Create state-specific certificate record
     */
    private function createStateSpecificCertificate(UserCourseEnrollment $enrollment, array $certificateData, string $stateCode)
    {
        switch (strtoupper($stateCode)) {
            case 'FL':
                FloridaCertificate::updateOrCreate(
                    ['enrollment_id' => $enrollment->id],
                    [
                        'dicds_certificate_number' => $certificateData['certificate_number'],
                        'student_name' => $certificateData['student_name'],
                        'course_name' => $enrollment->course->title,
                        'completion_date' => $enrollment->completed_at,
                        'final_exam_score' => $enrollment->final_exam_score ?? 95,
                        'verification_hash' => Str::random(32),
                        'generated_at' => now(),
                    ]
                );
                break;

            case 'MO':
                MissouriCertificate::updateOrCreate(
                    ['enrollment_id' => $enrollment->id],
                    [
                        'certificate_number' => $certificateData['certificate_number'],
                        'student_name' => $certificateData['student_name'],
                        'course_name' => $enrollment->course->title,
                        'completion_date' => $enrollment->completed_at,
                        'final_exam_score' => $enrollment->final_exam_score ?? 95,
                        'required_hours' => $certificateData['required_hours'] ?? 8,
                        'verification_hash' => Str::random(32),
                        'generated_at' => now(),
                    ]
                );
                break;

            case 'TX':
                TexasCertificate::updateOrCreate(
                    ['enrollment_id' => $enrollment->id],
                    [
                        'certificate_number' => $certificateData['certificate_number'],
                        'student_name' => $certificateData['student_name'],
                        'course_name' => $enrollment->course->title,
                        'completion_date' => $enrollment->completed_at,
                        'final_exam_score' => $enrollment->final_exam_score ?? 95,
                        'defensive_driving_hours' => $certificateData['defensive_driving_hours'] ?? 6,
                        'verification_hash' => Str::random(32),
                        'generated_at' => now(),
                    ]
                );
                break;

            case 'DE':
                DelawareCertificate::updateOrCreate(
                    ['enrollment_id' => $enrollment->id],
                    [
                        'certificate_number' => $certificateData['certificate_number'],
                        'student_name' => $certificateData['student_name'],
                        'course_name' => $enrollment->course->title,
                        'completion_date' => $enrollment->completed_at,
                        'final_exam_score' => $enrollment->final_exam_score ?? 95,
                        'course_duration_type' => $certificateData['duration_type'] ?? '6hr',
                        'required_hours' => $certificateData['required_hours'] ?? 6,
                        'verification_hash' => Str::random(32),
                        'generated_at' => now(),
                    ]
                );
                break;
        }
    }

    /**
     * Get or create certificate for enrollment
     */
    private function getOrCreateCertificate(UserCourseEnrollment $enrollment): Certificate
    {
        $certificate = Certificate::where('enrollment_id', $enrollment->id)->first();

        if (!$certificate) {
            $result = $this->certificateService->generateCertificate($enrollment);
            if ($result['success']) {
                $certificate = $this->storeCertificate($enrollment, $result['certificate_data']);
            } else {
                throw new \Exception('Failed to generate certificate: ' . $result['error']);
            }
        }

        return $certificate;
    }

    /**
     * View certificate using appropriate template
     */
    private function viewCertificate(Certificate $certificate)
    {
        $stateCode = $certificate->state_code;
        $templateData = $this->prepareCertificateData($certificate);

        // Get state stamp
        $stateStamp = StateStamp::where('state_code', $stateCode)
            ->where('is_active', true)
            ->first();

        $templateData['state_stamp'] = $stateStamp;

        return view($certificate->template, $templateData);
    }

    /**
     * Prepare certificate data for template
     */
    private function prepareCertificateData(Certificate $certificate): array
    {
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;

        return [
            'certificate' => $certificate,
            'enrollment' => $enrollment,
            'user' => $user,
            'course' => $enrollment->course,
            'student_name' => $certificate->student_name,
            'course_title' => $certificate->course_title,
            'completion_date' => $certificate->completion_date->format('F j, Y'),
            'certificate_number' => $certificate->certificate_number,
            'final_exam_score' => $certificate->final_exam_score,
            'state_code' => $certificate->state_code,
            'verification_hash' => $certificate->verification_hash,
        ];
    }

    /**
     * Find certificate by number across all tables
     */
    private function findCertificateByNumber(string $certificateNumber)
    {
        // Search in unified certificates table first
        $certificate = Certificate::where('certificate_number', $certificateNumber)->first();
        if ($certificate) return $certificate;

        // Search in state-specific tables
        $certificate = FloridaCertificate::where('dicds_certificate_number', $certificateNumber)->first();
        if ($certificate) return $certificate;

        $certificate = MissouriCertificate::where('certificate_number', $certificateNumber)->first();
        if ($certificate) return $certificate;

        $certificate = TexasCertificate::where('certificate_number', $certificateNumber)->first();
        if ($certificate) return $certificate;

        $certificate = DelawareCertificate::where('certificate_number', $certificateNumber)->first();
        if ($certificate) return $certificate;

        return null;
    }

    /**
     * Get state code from enrollment
     */
    private function getEnrollmentStateCode(UserCourseEnrollment $enrollment): string
    {
        $course = $enrollment->course;
        
        if (isset($course->state_code)) {
            return strtoupper($course->state_code);
        }
        
        if (isset($course->state)) {
            return strtoupper($course->state);
        }
        
        // Determine from course table
        switch ($enrollment->course_table) {
            case 'florida_courses':
                return 'FL';
            case 'missouri_courses':
                return 'MO';
            case 'texas_courses':
                return 'TX';
            case 'delaware_courses':
                return 'DE';
            default:
                return 'FL'; // Default fallback
        }
    }

    /**
     * Get certificate type based on enrollment and state
     */
    private function getCertificateType(UserCourseEnrollment $enrollment, string $stateCode): string
    {
        $course = $enrollment->course;
        
        switch (strtoupper($stateCode)) {
            case 'FL':
                return isset($course->course_type) && $course->course_type === 'ADI' 
                    ? Certificate::TYPE_FLORIDA_ADI 
                    : Certificate::TYPE_FLORIDA_BDI;
            case 'MO':
                return Certificate::TYPE_MISSOURI_DD;
            case 'TX':
                return Certificate::TYPE_TEXAS_DD;
            case 'DE':
                return Certificate::TYPE_DELAWARE_DD;
            default:
                return Certificate::TYPE_GENERIC;
        }
    }

    /**
     * Get state code from certificate model
     */
    private function getStateCode($certificate): string
    {
        if (isset($certificate->state_code)) {
            return $certificate->state_code;
        }
        
        if (isset($certificate->state)) {
            return $certificate->state;
        }
        
        // Determine from model type
        $className = get_class($certificate);
        switch ($className) {
            case FloridaCertificate::class:
                return 'FL';
            case MissouriCertificate::class:
                return 'MO';
            case TexasCertificate::class:
                return 'TX';
            case DelawareCertificate::class:
                return 'DE';
            default:
                return 'FL';
        }
    }

    /**
     * Email certificate to student
     */
    private function emailCertificate(Certificate $certificate)
    {
        $enrollment = $certificate->enrollment;
        $user = $enrollment->user;
        
        // Generate PDF
        $result = $this->certificateService->generateCertificate($enrollment);
        
        if ($result['success']) {
            Mail::to($user->email)->send(new CertificateGenerated(
                $user,
                $enrollment->course,
                $certificate->certificate_number,
                $result['pdf_content']
            ));
            
            $certificate->update([
                'is_sent_to_student' => true,
                'sent_at' => now()
            ]);
        }
    }

    /**
     * Regenerate certificate
     */
    private function regenerateCertificate(Certificate $certificate)
    {
        $enrollment = $certificate->enrollment;
        
        // Regenerate using service
        $result = $this->certificateService->generateCertificate($enrollment);
        
        if ($result['success']) {
            $certificate->update([
                'generated_at' => now(),
                'verification_hash' => Str::random(32),
                'metadata' => $result['certificate_data'],
            ]);
        }
    }
}