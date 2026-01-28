<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCourseEnrollment;
use App\Models\Payment;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\FileUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $states = $admin->isSuperAdmin() ? ['florida', 'missouri', 'texas', 'delaware'] : $admin->state_access;

        // Get dashboard statistics
        $stats = $this->getDashboardStats($states);
        
        // Get recent activities
        $recentEnrollments = $this->getRecentEnrollments($states);
        $recentPayments = $this->getRecentPayments($states);
        $recentFiles = $this->getRecentFiles($states);
        
        // Get chart data
        $enrollmentChart = $this->getEnrollmentChartData($states);
        $revenueChart = $this->getRevenueChartData($states);
        $stateComparisonChart = $this->getStateComparisonData($states);

        // Get quick stats for cards
        $quickStats = $this->getQuickStats($states);

        return view('admin.dashboard', compact(
            'stats',
            'recentEnrollments', 
            'recentPayments',
            'recentFiles',
            'enrollmentChart',
            'revenueChart',
            'stateComparisonChart',
            'quickStats',
            'states'
        ));
    }

    private function getDashboardStats($states)
    {
        $stats = [];

        foreach ($states as $state) {
            // Get users for this state
            $stateUsers = User::where('state_code', $state)->count();
            
            // Get enrollments - handle different table structures
            $enrollmentQuery = UserCourseEnrollment::query();
            
            if ($state === 'florida') {
                $enrollmentQuery->where('course_table', 'florida_courses');
            } else {
                $enrollmentQuery->whereHas('course', function($q) use ($state) {
                    $q->where('state', $state);
                });
            }

            $activeEnrollments = (clone $enrollmentQuery)->where('status', 'active')->count();
            $completedEnrollments = (clone $enrollmentQuery)->whereNotNull('completed_at')->count();
            $totalEnrollments = (clone $enrollmentQuery)->count();

            // Get payments for this state
            $paymentQuery = Payment::where('status', 'completed');
            
            if ($state === 'florida') {
                $paymentQuery->whereHas('enrollment', function($q) {
                    $q->where('course_table', 'florida_courses');
                });
            } else {
                $paymentQuery->whereHas('enrollment.course', function($q) use ($state) {
                    $q->where('state', $state);
                });
            }

            $totalRevenue = (clone $paymentQuery)->sum('amount');
            $monthlyRevenue = (clone $paymentQuery)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount');

            // Get courses count
            $coursesCount = $state === 'florida' 
                ? FloridaCourse::count()
                : Course::where('state', $state)->count();

            // Get files count
            $filesCount = FileUpload::where('state', $state)->count();

            $stats[$state] = [
                'total_students' => $stateUsers,
                'total_enrollments' => $totalEnrollments,
                'active_enrollments' => $activeEnrollments,
                'completed_enrollments' => $completedEnrollments,
                'completion_rate' => $totalEnrollments > 0 ? round(($completedEnrollments / $totalEnrollments) * 100, 1) : 0,
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
                'average_revenue_per_student' => $stateUsers > 0 ? round($totalRevenue / $stateUsers, 2) : 0,
                'courses_count' => $coursesCount,
                'files_count' => $filesCount,
            ];
        }

        return $stats;
    }

    private function getRecentEnrollments($states)
    {
        $query = UserCourseEnrollment::with(['user', 'course'])
            ->orderBy('created_at', 'desc')
            ->limit(10);

        // Filter by accessible states
        if (!in_array('all', $states)) {
            $query->where(function($q) use ($states) {
                foreach ($states as $state) {
                    if ($state === 'florida') {
                        $q->orWhere('course_table', 'florida_courses');
                    } else {
                        $q->orWhereHas('course', function($q2) use ($state) {
                            $q2->where('state', $state);
                        });
                    }
                }
            });
        }

        return $query->get();
    }

    private function getRecentPayments($states)
    {
        $query = Payment::with(['enrollment.user', 'enrollment.course'])
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->limit(10);

        // Filter by accessible states
        if (!in_array('all', $states)) {
            $query->whereHas('enrollment', function($q) use ($states) {
                $q->where(function($q2) use ($states) {
                    foreach ($states as $state) {
                        if ($state === 'florida') {
                            $q2->orWhere('course_table', 'florida_courses');
                        } else {
                            $q2->orWhereHas('course', function($q3) use ($state) {
                                $q3->where('state', $state);
                            });
                        }
                    }
                });
            });
        }

        return $query->get();
    }

    private function getRecentFiles($states)
    {
        $query = FileUpload::with('uploader')
            ->orderBy('created_at', 'desc')
            ->limit(5);

        if (!in_array('all', $states)) {
            $query->whereIn('state', $states);
        }

        return $query->get();
    }

    private function getEnrollmentChartData($states)
    {
        $data = [];
        $labels = [];

        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $count = UserCourseEnrollment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            // Filter by accessible states
            if (!in_array('all', $states)) {
                $count->where(function($q) use ($states) {
                    foreach ($states as $state) {
                        if ($state === 'florida') {
                            $q->orWhere('course_table', 'florida_courses');
                        } else {
                            $q->orWhereHas('course', function($q2) use ($state) {
                                $q2->where('state', $state);
                            });
                        }
                    }
                });
            }

            $data[] = $count->count();
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getRevenueChartData($states)
    {
        $data = [];
        $labels = [];

        // Get last 12 months
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');
            
            $revenue = Payment::where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            // Filter by accessible states
            if (!in_array('all', $states)) {
                $revenue->whereHas('enrollment', function($q) use ($states) {
                    $q->where(function($q2) use ($states) {
                        foreach ($states as $state) {
                            if ($state === 'florida') {
                                $q2->orWhere('course_table', 'florida_courses');
                            } else {
                                $q2->orWhereHas('course', function($q3) use ($state) {
                                    $q3->where('state', $state);
                                });
                            }
                        }
                    });
                });
            }

            $data[] = (float) $revenue->sum('amount');
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getStateComparisonData($states)
    {
        $data = [];
        $labels = [];
        $enrollmentData = [];
        $revenueData = [];

        foreach ($states as $state) {
            $labels[] = ucfirst($state);
            
            // Get enrollment count
            $enrollmentQuery = UserCourseEnrollment::query();
            if ($state === 'florida') {
                $enrollmentQuery->where('course_table', 'florida_courses');
            } else {
                $enrollmentQuery->whereHas('course', function($q) use ($state) {
                    $q->where('state', $state);
                });
            }
            $enrollmentData[] = $enrollmentQuery->count();

            // Get revenue
            $paymentQuery = Payment::where('status', 'completed');
            if ($state === 'florida') {
                $paymentQuery->whereHas('enrollment', function($q) {
                    $q->where('course_table', 'florida_courses');
                });
            } else {
                $paymentQuery->whereHas('enrollment.course', function($q) use ($state) {
                    $q->where('state', $state);
                });
            }
            $revenueData[] = (float) $paymentQuery->sum('amount');
        }

        return [
            'labels' => $labels,
            'enrollments' => $enrollmentData,
            'revenue' => $revenueData
        ];
    }

    private function getQuickStats($states)
    {
        $totalUsers = 0;
        $totalEnrollments = 0;
        $totalRevenue = 0;
        $totalFiles = 0;

        foreach ($states as $state) {
            $totalUsers += User::where('state_code', $state)->count();
            
            $enrollmentQuery = UserCourseEnrollment::query();
            if ($state === 'florida') {
                $enrollmentQuery->where('course_table', 'florida_courses');
            } else {
                $enrollmentQuery->whereHas('course', function($q) use ($state) {
                    $q->where('state', $state);
                });
            }
            $totalEnrollments += $enrollmentQuery->count();

            $paymentQuery = Payment::where('status', 'completed');
            if ($state === 'florida') {
                $paymentQuery->whereHas('enrollment', function($q) {
                    $q->where('course_table', 'florida_courses');
                });
            } else {
                $paymentQuery->whereHas('enrollment.course', function($q) use ($state) {
                    $q->where('state', $state);
                });
            }
            $totalRevenue += $paymentQuery->sum('amount');

            $totalFiles += FileUpload::where('state', $state)->count();
        }

        return [
            'total_users' => $totalUsers,
            'total_enrollments' => $totalEnrollments,
            'total_revenue' => $totalRevenue,
            'total_files' => $totalFiles,
            'average_revenue_per_enrollment' => $totalEnrollments > 0 ? round($totalRevenue / $totalEnrollments, 2) : 0,
        ];
    }

    public function getStats(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $states = $admin->isSuperAdmin() ? ['florida', 'missouri', 'texas', 'delaware'] : $admin->state_access;
        
        $state = $request->get('state');
        if ($state && in_array($state, $states)) {
            $states = [$state];
        }

        $stats = $this->getDashboardStats($states);
        $quickStats = $this->getQuickStats($states);

        return response()->json([
            'stats' => $stats,
            'quick_stats' => $quickStats,
        ]);
    }

    public function getChartData(Request $request)
    {
        $admin = Auth::guard('admin')->user();
        $states = $admin->isSuperAdmin() ? ['florida', 'missouri', 'texas', 'delaware'] : $admin->state_access;
        
        $type = $request->get('type', 'enrollment');
        $period = $request->get('period', '12months');

        switch ($type) {
            case 'revenue':
                $data = $this->getRevenueChartData($states);
                break;
            case 'comparison':
                $data = $this->getStateComparisonData($states);
                break;
            default:
                $data = $this->getEnrollmentChartData($states);
                break;
        }

        return response()->json($data);
    }
}