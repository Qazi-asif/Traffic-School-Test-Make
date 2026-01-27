<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use App\Models\UserCourseEnrollment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getStatsWeb()
    {
        try {
            // Check which completion criteria has data
            $completedByDate = UserCourseEnrollment::whereNotNull('completed_at')->count();
            $completedByStatus = UserCourseEnrollment::where('status', 'completed')->count();
            $completedByProgress = UserCourseEnrollment::where('progress_percentage', 100)->count();
            
            // Use the completion criteria that has the most data
            $completedCourses = max($completedByDate, $completedByStatus, $completedByProgress);
            
            // If no completion data found, try alternative approaches
            if ($completedCourses === 0) {
                // Check for certificates issued as a proxy for completion
                try {
                    $completedCourses = \App\Models\Certificate::count();
                } catch (\Exception $e) {
                    // If Certificate model fails, try other certificate tables
                    try {
                        $completedCourses = \DB::table('certificates')->count();
                    } catch (\Exception $e2) {
                        try {
                            $completedCourses = \DB::table('florida_certificates')->count();
                        } catch (\Exception $e3) {
                            try {
                                $completedCourses = \App\Models\DicdsCertificate::count();
                            } catch (\Exception $e4) {
                                $completedCourses = 0;
                            }
                        }
                    }
                }
            }
            
            // Get certificate count safely
            $certificatesIssued = 0;
            $pendingCertificates = 0;
            $stateSubmissions = 0;
            
            try {
                // Use fully qualified class name to ensure we get the right Certificate model
                $certificatesIssued = \App\Models\Certificate::count();
                $pendingCertificates = \App\Models\Certificate::where('status', 'generated')->count();
                $stateSubmissions = \App\Models\Certificate::where('is_sent_to_state', true)->count();
            } catch (\Exception $e) {
                // Try different certificate tables if the main one fails
                try {
                    $certificatesIssued = \DB::table('certificates')->count();
                    $pendingCertificates = \DB::table('certificates')->where('status', 'generated')->count();
                    $stateSubmissions = \DB::table('certificates')->where('is_sent_to_state', 1)->count();
                } catch (\Exception $e2) {
                    try {
                        // Try florida_certificates table
                        $certificatesIssued = \DB::table('florida_certificates')->count();
                        $pendingCertificates = \DB::table('florida_certificates')->where('status', 'generated')->count();
                        $stateSubmissions = \DB::table('florida_certificates')->where('is_sent_to_state', 1)->count();
                    } catch (\Exception $e3) {
                        try {
                            // Try dicds_certificates table (use DicdsCertificate model)
                            $certificatesIssued = \App\Models\DicdsCertificate::count();
                            $pendingCertificates = \App\Models\DicdsCertificate::where('status', 'Issued')->count();
                            // dicds_certificates doesn't have is_sent_to_state, so use state_transmissions
                            $stateSubmissions = \DB::table('state_transmissions')->where('status', 'success')->count();
                        } catch (\Exception $e4) {
                            // All certificate tables failed, keep defaults at 0
                        }
                    }
                }
            }
            
            $stats = [
                'total_students' => User::where('role_id', '!=', 1)->count(),
                'total_courses' => Course::count(),
                'total_enrollments' => UserCourseEnrollment::count(),
                'completed_courses' => $completedCourses,
                'total_revenue' => Payment::where('status', 'completed')->sum('amount') ?? 0,
                'certificates_issued' => $certificatesIssued,
                'pending_certificates' => $pendingCertificates,
                'state_submissions' => $stateSubmissions,
                
                // Debug info to help identify the right completion criteria
                'debug_completion_methods' => [
                    'by_completed_at' => $completedByDate,
                    'by_status_completed' => $completedByStatus,
                    'by_progress_100' => $completedByProgress,
                    'certificates_count' => $certificatesIssued,
                ],
            ];
        } catch (\Exception $e) {
            // Fallback stats if everything fails
            $stats = [
                'total_students' => User::where('role_id', '!=', 1)->count(),
                'total_courses' => Course::count(),
                'total_enrollments' => UserCourseEnrollment::count(),
                'completed_courses' => 0,
                'total_revenue' => 0,
                'certificates_issued' => 0,
                'pending_certificates' => 0,
                'state_submissions' => 0,
                'error' => 'Some stats could not be calculated: ' . $e->getMessage(),
            ];
        }

        // Monthly enrollment data for chart
        $monthlyEnrollments = UserCourseEnrollment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as count')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Fill missing months with 0
        $enrollmentChart = [];
        for ($i = 1; $i <= 12; $i++) {
            $enrollmentChart[] = $monthlyEnrollments[$i] ?? 0;
        }

        // Revenue by month
        $monthlyRevenue = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(amount) as total')
        )
            ->where('status', 'completed')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();

        $revenueChart = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenueChart[] = $monthlyRevenue[$i] ?? 0;
        }

        // Course completion rates
        $courseStats = Course::select('courses.*')
            ->withCount([
                'enrollments',
                'enrollments as completed_count' => function ($query) {
                    $query->whereNotNull('completed_at');
                },
            ])
            ->get()
            ->map(function ($course) {
                $course->completion_rate = $course->enrollments_count > 0
                    ? round(($course->completed_count / $course->enrollments_count) * 100, 2)
                    : 0;

                return $course;
            });

        // Recent activities
        $recentEnrollments = UserCourseEnrollment::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentPayments = Payment::with(['user', 'enrollment.course'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'stats' => $stats,
            'charts' => [
                'enrollments' => $enrollmentChart,
                'revenue' => $revenueChart,
            ],
            'course_stats' => $courseStats,
            'recent_activities' => [
                'enrollments' => $recentEnrollments,
                'payments' => $recentPayments,
            ],
        ]);
    }

    public function getUserStats()
    {
        $user = auth()->user();

        $stats = [
            'total_enrollments' => $user->enrollments()->count(),
            'completed_courses' => $user->enrollments()->whereNotNull('completed_at')->count(),
            'in_progress' => $user->enrollments()->whereNull('completed_at')->count(),
            'certificates_earned' => Certificate::whereHas('enrollment', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
            'total_spent' => Payment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        // Progress data
        $enrollments = $user->enrollments()->with(['course', 'progress'])->get();

        $progressData = $enrollments->map(function ($enrollment) {
            $totalChapters = $enrollment->course->chapters()->count();
            $completedChapters = $enrollment->progress()->count();

            return [
                'course_title' => $enrollment->course->title,
                'progress_percentage' => $totalChapters > 0 ? round(($completedChapters / $totalChapters) * 100, 2) : 0,
                'completed_chapters' => $completedChapters,
                'total_chapters' => $totalChapters,
                'enrollment_date' => $enrollment->created_at->format('M d, Y'),
                'completion_date' => $enrollment->completed_at ? $enrollment->completed_at->format('M d, Y') : null,
            ];
        });

        return response()->json([
            'stats' => $stats,
            'progress_data' => $progressData,
        ]);
    }
}
