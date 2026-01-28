<?php

namespace App\Http\Controllers\Admin\Florida;

use App\Http\Controllers\Controller;
use App\Models\UserCourseEnrollment;
use App\Models\User;
use App\Models\FloridaCourse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->where('course_table', 'florida_courses')
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $enrollments = $query->paginate(20);
        $courses = FloridaCourse::orderBy('title')->get();

        $stats = [
            'total' => UserCourseEnrollment::where('course_table', 'florida_courses')->count(),
            'active' => UserCourseEnrollment::where('course_table', 'florida_courses')->where('status', 'active')->count(),
            'completed' => UserCourseEnrollment::where('course_table', 'florida_courses')->whereNotNull('completed_at')->count(),
            'pending_payment' => UserCourseEnrollment::where('course_table', 'florida_courses')->where('payment_status', 'pending')->count(),
        ];

        return view('admin.florida.enrollments.index', compact('enrollments', 'courses', 'stats'));
    }

    public function create()
    {
        $users = User::orderBy('first_name')->orderBy('last_name')->get();
        $courses = FloridaCourse::where('is_active', true)->orderBy('title')->get();

        return view('admin.florida.enrollments.create', compact('users', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:florida_courses,id',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'citation_number' => 'nullable|string',
            'court_date' => 'nullable|date',
            'status' => 'required|in:active,completed,expired,cancelled',
        ]);

        $enrollment = UserCourseEnrollment::create([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'course_table' => 'florida_courses',
            'payment_status' => $request->payment_status,
            'amount_paid' => $request->amount_paid,
            'payment_method' => $request->payment_method,
            'citation_number' => $request->citation_number,
            'court_date' => $request->court_date,
            'status' => $request->status,
            'enrolled_at' => now(),
        ]);

        return redirect()->route('admin.florida.enrollments.show', $enrollment)
            ->with('success', 'Enrollment created successfully.');
    }

    public function show(UserCourseEnrollment $enrollment)
    {
        $enrollment->load(['user', 'floridaCourse', 'progress', 'quizAttempts', 'payments', 'floridaCertificate']);

        $stats = [
            'progress_percentage' => $enrollment->progress_percentage,
            'total_time_spent' => $enrollment->total_time_spent,
            'quiz_attempts' => $enrollment->quizAttempts->count(),
            'average_quiz_score' => $enrollment->quizAttempts->avg('score') ?? 0,
            'total_payments' => $enrollment->payments->sum('amount'),
            'certificate_generated' => $enrollment->floridaCertificate ? 'Yes' : 'No',
        ];

        return view('admin.florida.enrollments.show', compact('enrollment', 'stats'));
    }

    public function edit(UserCourseEnrollment $enrollment)
    {
        $users = User::orderBy('first_name')->orderBy('last_name')->get();
        $courses = FloridaCourse::orderBy('title')->get();

        return view('admin.florida.enrollments.edit', compact('enrollment', 'users', 'courses'));
    }

    public function update(Request $request, UserCourseEnrollment $enrollment)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:florida_courses,id',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'citation_number' => 'nullable|string',
            'court_date' => 'nullable|date',
            'status' => 'required|in:active,completed,expired,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        $enrollment->update([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'payment_status' => $request->payment_status,
            'amount_paid' => $request->amount_paid,
            'payment_method' => $request->payment_method,
            'citation_number' => $request->citation_number,
            'court_date' => $request->court_date,
            'status' => $request->status,
            'progress_percentage' => $request->progress_percentage ?? $enrollment->progress_percentage,
        ]);

        return redirect()->route('admin.florida.enrollments.show', $enrollment)
            ->with('success', 'Enrollment updated successfully.');
    }

    public function destroy(UserCourseEnrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()->route('admin.florida.enrollments.index')
            ->with('success', 'Enrollment deleted successfully.');
    }

    public function resetProgress(UserCourseEnrollment $enrollment)
    {
        $enrollment->update([
            'progress_percentage' => 0,
            'total_time_spent' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);

        // Delete related progress and quiz attempts
        $enrollment->progress()->delete();
        $enrollment->quizAttempts()->delete();

        return redirect()->route('admin.florida.enrollments.show', $enrollment)
            ->with('success', 'Enrollment progress reset successfully.');
    }

    public function markCompleted(UserCourseEnrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        return redirect()->route('admin.florida.enrollments.show', $enrollment)
            ->with('success', 'Enrollment marked as completed.');
    }

    public function revokeAccess(UserCourseEnrollment $enrollment)
    {
        $enrollment->update([
            'access_revoked' => true,
            'access_revoked_at' => now(),
        ]);

        return redirect()->route('admin.florida.enrollments.show', $enrollment)
            ->with('success', 'Access revoked successfully.');
    }

    public function restoreAccess(UserCourseEnrollment $enrollment)
    {
        $enrollment->update([
            'access_revoked' => false,
            'access_revoked_at' => null,
        ]);

        return redirect()->route('admin.florida.enrollments.show', $enrollment)
            ->with('success', 'Access restored successfully.');
    }

    public function export(Request $request)
    {
        $query = UserCourseEnrollment::with(['user', 'floridaCourse'])
            ->where('course_table', 'florida_courses');

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $enrollments = $query->get();

        $filename = 'florida_enrollments_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($enrollments) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Student Name',
                'Email',
                'Course',
                'Status',
                'Payment Status',
                'Amount Paid',
                'Progress %',
                'Enrolled Date',
                'Completed Date',
                'Citation Number',
                'Court Date'
            ]);

            // Data rows
            foreach ($enrollments as $enrollment) {
                fputcsv($file, [
                    $enrollment->user->first_name . ' ' . $enrollment->user->last_name,
                    $enrollment->user->email,
                    $enrollment->floridaCourse->title ?? 'N/A',
                    $enrollment->status,
                    $enrollment->payment_status,
                    $enrollment->amount_paid,
                    $enrollment->progress_percentage,
                    $enrollment->enrolled_at?->format('Y-m-d H:i:s'),
                    $enrollment->completed_at?->format('Y-m-d H:i:s'),
                    $enrollment->citation_number,
                    $enrollment->court_date?->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}