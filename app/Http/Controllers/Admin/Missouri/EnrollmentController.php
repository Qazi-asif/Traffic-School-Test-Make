<?php

namespace App\Http\Controllers\Admin\Missouri;

use App\Http\Controllers\Controller;
use App\Models\UserCourseEnrollment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request)
    {
        $query = UserCourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function($q) {
                $q->where('state', 'missouri');
            })
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
        $courses = Course::where('state', 'missouri')->orderBy('title')->get();

        $stats = [
            'total' => UserCourseEnrollment::whereHas('course', function($q) {
                $q->where('state', 'missouri');
            })->count(),
            'active' => UserCourseEnrollment::whereHas('course', function($q) {
                $q->where('state', 'missouri');
            })->where('status', 'active')->count(),
            'completed' => UserCourseEnrollment::whereHas('course', function($q) {
                $q->where('state', 'missouri');
            })->whereNotNull('completed_at')->count(),
            'pending_payment' => UserCourseEnrollment::whereHas('course', function($q) {
                $q->where('state', 'missouri');
            })->where('payment_status', 'pending')->count(),
        ];

        return view('admin.missouri.enrollments.index', compact('enrollments', 'courses', 'stats'));
    }

    public function create()
    {
        $users = User::orderBy('first_name')->orderBy('last_name')->get();
        $courses = Course::where('state', 'missouri')->where('is_active', true)->orderBy('title')->get();

        return view('admin.missouri.enrollments.create', compact('users', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'citation_number' => 'nullable|string',
            'court_date' => 'nullable|date',
            'status' => 'required|in:active,completed,expired,cancelled',
        ]);

        // Verify course is Missouri
        $course = Course::findOrFail($request->course_id);
        if ($course->state !== 'missouri') {
            return redirect()->back()->with('error', 'Invalid course selection.');
        }

        $enrollment = UserCourseEnrollment::create([
            'user_id' => $request->user_id,
            'course_id' => $request->course_id,
            'course_table' => 'courses',
            'payment_status' => $request->payment_status,
            'amount_paid' => $request->amount_paid,
            'payment_method' => $request->payment_method,
            'citation_number' => $request->citation_number,
            'court_date' => $request->court_date,
            'status' => $request->status,
            'enrolled_at' => now(),
        ]);

        return redirect()->route('admin.missouri.enrollments.show', $enrollment)
            ->with('success', 'Missouri enrollment created successfully.');
    }

    public function show(UserCourseEnrollment $enrollment)
    {
        $enrollment->load(['user', 'course', 'progress', 'quizAttempts', 'payments']);

        $stats = [
            'progress_percentage' => $enrollment->progress_percentage,
            'total_time_spent' => $enrollment->total_time_spent,
            'quiz_attempts' => $enrollment->quizAttempts->count(),
            'average_quiz_score' => $enrollment->quizAttempts->avg('score') ?? 0,
            'total_payments' => $enrollment->payments->sum('amount'),
        ];

        return view('admin.missouri.enrollments.show', compact('enrollment', 'stats'));
    }

    public function edit(UserCourseEnrollment $enrollment)
    {
        $users = User::orderBy('first_name')->orderBy('last_name')->get();
        $courses = Course::where('state', 'missouri')->orderBy('title')->get();

        return view('admin.missouri.enrollments.edit', compact('enrollment', 'users', 'courses'));
    }

    public function update(Request $request, UserCourseEnrollment $enrollment)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'amount_paid' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'citation_number' => 'nullable|string',
            'court_date' => 'nullable|date',
            'status' => 'required|in:active,completed,expired,cancelled',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        // Verify course is Missouri
        $course = Course::findOrFail($request->course_id);
        if ($course->state !== 'missouri') {
            return redirect()->back()->with('error', 'Invalid course selection.');
        }

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

        return redirect()->route('admin.missouri.enrollments.show', $enrollment)
            ->with('success', 'Missouri enrollment updated successfully.');
    }

    public function destroy(UserCourseEnrollment $enrollment)
    {
        $enrollment->delete();

        return redirect()->route('admin.missouri.enrollments.index')
            ->with('success', 'Missouri enrollment deleted successfully.');
    }

    public function resetProgress(UserCourseEnrollment $enrollment)
    {
        $enrollment->update([
            'progress_percentage' => 0,
            'total_time_spent' => 0,
            'started_at' => null,
            'completed_at' => null,
        ]);

        $enrollment->progress()->delete();
        $enrollment->quizAttempts()->delete();

        return redirect()->route('admin.missouri.enrollments.show', $enrollment)
            ->with('success', 'Missouri enrollment progress reset successfully.');
    }

    public function markCompleted(UserCourseEnrollment $enrollment)
    {
        $enrollment->update([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ]);

        return redirect()->route('admin.missouri.enrollments.show', $enrollment)
            ->with('success', 'Missouri enrollment marked as completed.');
    }
}