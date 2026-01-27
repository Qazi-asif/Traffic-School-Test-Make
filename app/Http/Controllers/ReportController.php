<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Payment;
use App\Models\UserCourseEnrollment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function indexWeb()
    {
        return response()->json([
            'available_reports' => [
                'enrollment_report' => 'Student Enrollment Report',
                'completion_report' => 'Course Completion Report',
                'revenue_report' => 'Revenue Report',
                'certificate_report' => 'Certificate Report',
                'state_compliance_report' => 'State Compliance Report',
            ],
        ]);
    }

    public function generateWeb(Request $request)
    {
        $reportType = $request->input('report_type');
        $startDate = $request->input('start_date', Carbon::now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        $stateCode = $request->input('state_code');

        switch ($reportType) {
            case 'enrollment':
            case 'enrollment_report':
                return $this->generateEnrollmentReport($startDate, $endDate, $stateCode);
            case 'completion':
            case 'completion_report':
                return $this->generateCompletionReport($startDate, $endDate, $stateCode);
            case 'revenue':
            case 'revenue_report':
                return $this->generateRevenueReport($startDate, $endDate, $stateCode);
            case 'certificate':
            case 'certificate_report':
                return $this->generateCertificateReport($startDate, $endDate, $stateCode);
            case 'compliance':
            case 'state_compliance':
            case 'state_compliance_report':
                return $this->generateStateComplianceReport($startDate, $endDate, $stateCode);
            default:
                return response()->json(['error' => 'Invalid report type'], 400);
        }
    }

    private function generateEnrollmentReport($startDate, $endDate, $stateCode = null)
    {
        $query = UserCourseEnrollment::with(['user', 'floridaCourse', 'legacyCourse'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->where('state', $stateCode);
        }

        $enrollments = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_enrollments' => $enrollments->count(),
            'unique_students' => $enrollments->pluck('user_id')->unique()->count(),
            'courses_enrolled' => $enrollments->pluck('course_id')->unique()->count(),
            'completed_enrollments' => $enrollments->where('completed_at', '!=', null)->count(),
        ];

        return response()->json([
            'report_type' => 'Enrollment Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'detailed_data' => $enrollments->map(function ($enrollment) {
                $user = $enrollment->user;
                $course = $enrollment->floridaCourse ?? $enrollment->legacyCourse;
                return [
                    'student_name' => $user ? ($user->first_name ?? 'N/A').' '.($user->last_name ?? '') : 'N/A',
                    'student_email' => $user ? ($user->email ?? 'N/A') : 'N/A',
                    'course_title' => $course ? ($course->title ?? 'N/A') : 'N/A',
                    'enrolled_at' => $enrollment->created_at->format('Y-m-d'),
                    'status' => $enrollment->completed_at ? 'completed' : 'in_progress',
                    'progress' => $enrollment->progress ?? 0,
                ];
            })->toArray(),
        ]);
    }

    private function generateCompletionReport($startDate, $endDate, $stateCode = null)
    {
        $query = UserCourseEnrollment::with(['user', 'floridaCourse', 'legacyCourse'])
            ->whereNotNull('completed_at')
            ->whereBetween('completed_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->where('state', $stateCode);
        }

        $completions = $query->orderBy('completed_at', 'desc')->get();

        $summary = [
            'total_completions' => $completions->count(),
            'unique_students' => $completions->pluck('user_id')->unique()->count(),
            'courses_completed' => $completions->pluck('course_id')->unique()->count(),
        ];

        return response()->json([
            'report_type' => 'Course Completion Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'detailed_data' => $completions->map(function ($completion) {
                $user = $completion->user;
                $course = $completion->floridaCourse ?? $completion->legacyCourse;
                return [
                    'student_name' => $user ? ($user->first_name ?? 'N/A').' '.($user->last_name ?? '') : 'N/A',
                    'student_email' => $user ? ($user->email ?? 'N/A') : 'N/A',
                    'course_title' => $course ? ($course->title ?? 'N/A') : 'N/A',
                    'completed_at' => $completion->completed_at->format('Y-m-d'),
                    'score' => $completion->progress ?? 0,
                ];
            })->toArray(),
        ]);
    }

    private function generateRevenueReport($startDate, $endDate, $stateCode = null)
    {
        $query = Payment::with(['user', 'enrollment.floridaCourse', 'enrollment.legacyCourse'])
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->where('state', $stateCode);
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_revenue' => $payments->sum('amount'),
            'total_transactions' => $payments->count(),
            'average_transaction' => $payments->count() > 0 ? round($payments->sum('amount') / $payments->count(), 2) : 0,
            'unique_customers' => $payments->pluck('user_id')->unique()->count(),
        ];

        return response()->json([
            'report_type' => 'Revenue Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'detailed_data' => $payments->map(function ($payment) {
                $user = $payment->user;
                $enrollment = $payment->enrollment;
                $course = null;
                
                if ($enrollment) {
                    $course = $enrollment->floridaCourse ?? $enrollment->legacyCourse;
                }
                
                return [
                    'student_name' => $user ? ($user->first_name ?? 'N/A').' '.($user->last_name ?? '') : 'N/A',
                    'student_email' => $user ? ($user->email ?? 'N/A') : 'N/A',
                    'course_title' => $course ? ($course->title ?? 'N/A') : 'N/A',
                    'amount_paid' => floatval($payment->amount ?? 0),
                    'enrolled_at' => $payment->created_at->format('Y-m-d'),
                    'payment_status' => 'completed',
                ];
            })->toArray(),
        ]);
    }

    private function generateCertificateReport($startDate, $endDate, $stateCode = null)
    {
        $query = Certificate::with(['enrollment.user', 'enrollment.course'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->where('state_code', $stateCode);
        }

        $certificates = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_certificates' => $certificates->count(),
            'sent_to_state' => $certificates->where('is_sent_to_state', true)->count(),
            'pending_state_submission' => $certificates->where('is_sent_to_state', false)->count(),
        ];

        return response()->json([
            'report_type' => 'Certificate Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'detailed_data' => $certificates->map(function ($certificate) {
                return [
                    'certificate_number' => $certificate->certificate_number ?? 'N/A',
                    'student_name' => $certificate->student_name ?? 'N/A',
                    'course_name' => $certificate->course_name ?? 'N/A',
                    'state_code' => $certificate->state_code ?? 'N/A',
                    'completion_date' => $certificate->completion_date ?? 'N/A',
                    'issued_date' => $certificate->created_at->format('Y-m-d'),
                    'status' => $certificate->status ?? 'pending',
                ];
            })->toArray(),
        ]);
    }

    private function generateStateComplianceReport($startDate, $endDate, $stateCode = null)
    {
        $query = \App\Models\FloridaCertificate::with(['enrollment.user'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($stateCode) {
            $query->where('state_code', $stateCode);
        }

        $certificates = $query->get();

        $summary = [
            'total_certificates' => $certificates->count(),
            'submitted_to_state' => $certificates->where('is_sent_to_state', true)->count(),
            'confirmed_by_state' => $certificates->where('status', 'confirmed')->count(),
            'rejected_by_state' => $certificates->where('status', 'rejected')->count(),
            'compliance_rate' => $certificates->count() > 0
                ? round(($certificates->where('status', 'confirmed')->count() / $certificates->count()) * 100, 2)
                : 0,
        ];

        return response()->json([
            'report_type' => 'State Compliance Report',
            'period' => "$startDate to $endDate",
            'summary' => $summary,
            'detailed_data' => $certificates->map(function ($certificate) {
                return [
                    'certificate_number' => $certificate->certificate_number ?? 'N/A',
                    'student_name' => $certificate->student_name ?? 'N/A',
                    'state_code' => $certificate->state_code ?? 'N/A',
                    'completion_date' => $certificate->completion_date ?? 'N/A',
                    'status' => $certificate->status ?? 'pending',
                    'compliance_status' => ($certificate->status ?? null) === 'confirmed' ? 'Compliant' : 'Pending',
                ];
            })->toArray(),
        ]);
    }
}
