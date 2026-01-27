<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NevadaCertificate;
use App\Models\NevadaComplianceLog;
use App\Models\NevadaCourse;
use App\Models\NevadaStudent;
use App\Models\NevadaSubmission;
use App\Models\UserCourseEnrollment;
use App\Services\NevadaComplianceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class NevadaController extends Controller
{
    protected $complianceService;

    public function __construct(NevadaComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_courses' => NevadaCourse::active()->count(),
            'total_students' => NevadaStudent::count(),
            'total_certificates' => NevadaCertificate::count(),
            'pending_submissions' => NevadaCertificate::pending()->count(),
            'recent_completions' => NevadaCertificate::with(['enrollment.user', 'enrollment.course'])
                ->latest()
                ->take(10)
                ->get(),
        ];

        return view('admin.nevada.dashboard', compact('stats'));
    }

    /**
     * Courses list
     */
    public function courses()
    {
        $courses = NevadaCourse::with('course')->paginate(20);

        return view('admin.nevada.courses.index', compact('courses'));
    }

    /**
     * Store course
     */
    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'nevada_course_code' => 'required|string',
            'course_type' => 'required|in:traffic_safety,defensive_driving,dui',
            'approved_date' => 'required|date',
            'expiration_date' => 'nullable|date',
            'approval_number' => 'nullable|string',
            'required_hours' => 'required|numeric|min:0',
            'max_completion_days' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $course = NevadaCourse::create($validated);

        return response()->json($course, 201);
    }

    /**
     * Update course
     */
    public function updateCourse(Request $request, $id)
    {
        $course = NevadaCourse::findOrFail($id);

        $validated = $request->validate([
            'nevada_course_code' => 'string',
            'course_type' => 'in:traffic_safety,defensive_driving,dui',
            'approved_date' => 'date',
            'expiration_date' => 'nullable|date',
            'approval_number' => 'nullable|string',
            'required_hours' => 'numeric|min:0',
            'max_completion_days' => 'integer|min:1',
            'is_active' => 'boolean',
        ]);

        $course->update($validated);

        return response()->json($course);
    }

    /**
     * Students list
     */
    public function students(Request $request)
    {
        $query = NevadaStudent::with(['user', 'enrollment.course']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nevada_dmv_number', 'like', "%{$request->search}%")
                    ->orWhere('court_case_number', 'like', "%{$request->search}%")
                    ->orWhereHas('user', function ($uq) use ($request) {
                        $uq->where('email', 'like', "%{$request->search}%")
                            ->orWhere('first_name', 'like', "%{$request->search}%")
                            ->orWhere('last_name', 'like', "%{$request->search}%");
                    });
            });
        }

        $students = $query->paginate(20);

        return view('admin.nevada.students.index', compact('students'));
    }

    /**
     * Student detail
     */
    public function studentDetail($enrollmentId)
    {
        $enrollment = UserCourseEnrollment::with(['user', 'course', 'nevadaStudent'])
            ->findOrFail($enrollmentId);

        $activityLog = $this->complianceService->getStudentActivityLog($enrollment);
        $validationErrors = $this->complianceService->validateCompletionRequirements($enrollment);

        return view('admin.nevada.students.detail', compact('enrollment', 'activityLog', 'validationErrors'));
    }

    /**
     * Activity log
     */
    public function activityLog($enrollmentId)
    {
        $enrollment = UserCourseEnrollment::findOrFail($enrollmentId);
        $logs = $this->complianceService->getStudentActivityLog($enrollment);

        return response()->json($logs);
    }

    /**
     * Certificates list
     */
    public function certificates(Request $request)
    {
        $query = NevadaCertificate::with(['enrollment.user', 'enrollment.course']);

        if ($request->status) {
            $query->where('submission_status', $request->status);
        }

        $certificates = $query->latest()->paginate(20);

        return view('admin.nevada.certificates.index', compact('certificates'));
    }

    /**
     * Submit certificate
     */
    public function submitCertificate($id)
    {
        $certificate = NevadaCertificate::findOrFail($id);

        $submission = $this->complianceService->submitToState($certificate);

        return response()->json([
            'success' => $submission->isSuccessful(),
            'submission' => $submission,
        ]);
    }

    /**
     * Compliance logs
     */
    public function complianceLogs(Request $request)
    {
        $query = NevadaComplianceLog::with(['user', 'enrollment', 'chapter']);

        if ($request->log_type) {
            $query->byType($request->log_type);
        }

        if ($request->user_id) {
            $query->byUser($request->user_id);
        }

        if ($request->enrollment_id) {
            $query->byEnrollment($request->enrollment_id);
        }

        if ($request->from && $request->to) {
            $query->dateRange(
                Carbon::parse($request->from),
                Carbon::parse($request->to)
            );
        }

        $logs = $query->latest()->paginate(50);

        return view('admin.nevada.compliance-logs', compact('logs'));
    }

    /**
     * Export logs
     */
    public function exportLogs(Request $request)
    {
        $filters = $request->only(['from', 'to', 'log_type', 'user_id', 'enrollment_id']);

        if (isset($filters['from'])) {
            $filters['from'] = Carbon::parse($filters['from']);
        }
        if (isset($filters['to'])) {
            $filters['to'] = Carbon::parse($filters['to']);
        }

        $csv = $this->complianceService->exportComplianceLogs($filters);

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="nevada-compliance-logs-'.now()->format('Y-m-d').'.csv"');
    }

    /**
     * Submissions list
     */
    public function submissions(Request $request)
    {
        $query = NevadaSubmission::with(['certificate.enrollment.user']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $submissions = $query->latest()->paginate(20);

        return view('admin.nevada.submissions.index', compact('submissions'));
    }

    /**
     * Submission detail
     */
    public function submissionDetail($id)
    {
        $submission = NevadaSubmission::with(['certificate.enrollment.user', 'certificate.enrollment.course'])
            ->findOrFail($id);

        return view('admin.nevada.submissions.detail', compact('submission'));
    }

    /**
     * Retry submission
     */
    public function retrySubmission($id)
    {
        $submission = NevadaSubmission::findOrFail($id);
        $certificate = $submission->certificate;

        $newSubmission = $this->complianceService->submitToState($certificate);

        return response()->json([
            'success' => $newSubmission->isSuccessful(),
            'submission' => $newSubmission,
        ]);
    }

    /**
     * Reports
     */
    public function reports()
    {
        return view('admin.nevada.reports.index');
    }

    /**
     * Compliance report
     */
    public function complianceReport(Request $request)
    {
        $from = $request->from ? Carbon::parse($request->from) : now()->subMonth();
        $to = $request->to ? Carbon::parse($request->to) : now();

        $report = $this->complianceService->getComplianceReport($from, $to);

        return view('admin.nevada.reports.compliance', compact('report', 'from', 'to'));
    }
}
