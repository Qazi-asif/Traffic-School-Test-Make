<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentSegment;
use App\Models\FloridaCourse;
use App\Services\CustomerSegmentService;
use Illuminate\Http\Request;

class CustomerSegmentController extends Controller
{
    protected $segmentService;

    public function __construct(CustomerSegmentService $segmentService)
    {
        $this->segmentService = $segmentService;
    }

    public function index()
    {
        $counts = $this->segmentService->getSegmentCounts();
        $trend = $this->segmentService->getMonthlyCompletionTrend(6);
        $savedSegments = EnrollmentSegment::where('is_system', false)->latest()->get();

        return view('admin.customers.segments.index', compact('counts', 'trend', 'savedSegments'));
    }

    public function completedMonthly(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $filters = $request->only(['state', 'course_id', 'date_from', 'date_to']);

        $enrollments = $this->segmentService->getCompletedMonthly($year, $month, $filters);
        $courses = FloridaCourse::select('id', 'title', 'state')->get();

        $stats = [
            'total' => $enrollments->total(),
            'by_state' => $enrollments->groupBy('course.state')->map->count(),
            'by_course' => $enrollments->groupBy('course.title')->map->count(),
        ];

        return view('admin.customers.segments.completed-monthly', compact('enrollments', 'courses', 'year', 'month', 'stats', 'filters'));
    }

    public function paidIncomplete(Request $request)
    {
        $filters = $request->only(['state', 'course_id', 'progress_min', 'progress_max']);

        $enrollments = $this->segmentService->getPaidIncomplete($filters);
        $courses = FloridaCourse::select('id', 'title', 'state')->get();

        return view('admin.customers.segments.paid-incomplete', compact('enrollments', 'courses', 'filters'));
    }

    public function inProgress(Request $request)
    {
        $filters = $request->only(['state', 'course_id', 'progress_min', 'progress_max']);

        $enrollments = $this->segmentService->getInProgress($filters);
        $courses = FloridaCourse::select('id', 'title', 'state')->get();

        return view('admin.customers.segments.in-progress', compact('enrollments', 'courses', 'filters'));
    }

    public function abandoned(Request $request)
    {
        $daysInactive = $request->get('days_inactive', 30);
        $filters = $request->only(['state', 'course_id']);

        $enrollments = $this->segmentService->getAbandoned($daysInactive, $filters);
        $courses = FloridaCourse::select('id', 'title', 'state')->get();

        return view('admin.customers.segments.abandoned', compact('enrollments', 'courses', 'daysInactive', 'filters'));
    }

    public function expiringSoon(Request $request)
    {
        $days = $request->get('days', 7);
        $filters = $request->only(['state', 'course_id']);

        $enrollments = $this->segmentService->getExpiringSoon($days, $filters);
        $courses = FloridaCourse::select('id', 'title', 'state')->get();

        return view('admin.customers.segments.expiring-soon', compact('enrollments', 'courses', 'days', 'filters'));
    }

    public function expired(Request $request)
    {
        $daysAgo = $request->get('days_ago', 30);
        $filters = $request->only(['state', 'course_id']);

        $enrollments = $this->segmentService->getExpired($daysAgo, $filters);
        $courses = FloridaCourse::select('id', 'title', 'state')->get();

        return view('admin.customers.segments.expired', compact('enrollments', 'courses', 'daysAgo', 'filters'));
    }

    public function neverStarted(Request $request)
    {
        $filters = $request->only(['state', 'course_id']);

        $enrollments = $this->segmentService->getNeverStarted($filters);
        $courses = FloridaCourse::select('id', 'title', 'state')->get();

        return view('admin.customers.segments.never-started', compact('enrollments', 'courses', 'filters'));
    }

    public function struggling(Request $request)
    {
        $failedAttempts = $request->get('failed_attempts', 3);
        $filters = $request->only(['state', 'course_id']);

        $enrollments = $this->segmentService->getStruggling($failedAttempts, $filters);
        $courses = FloridaCourse::select('id', 'title', 'state')->get();

        return view('admin.customers.segments.struggling', compact('enrollments', 'courses', 'failedAttempts', 'filters'));
    }

    public function bulkRemind(Request $request)
    {
        $enrollmentIds = $request->input('enrollment_ids', []);
        $templateKey = $request->input('template', 'reminder');

        $enrollments = \App\Models\UserCourseEnrollment::whereIn('id', $enrollmentIds)->get();
        $count = $this->segmentService->sendReminder($enrollments, $templateKey);

        return back()->with('success', "Reminders sent to {$count} students.");
    }

    public function bulkExtend(Request $request)
    {
        $enrollmentIds = $request->input('enrollment_ids', []);
        $days = $request->input('days', 7);

        $enrollments = \App\Models\UserCourseEnrollment::whereIn('id', $enrollmentIds)->get();
        $count = $this->segmentService->extendExpiration($enrollments, $days);

        return back()->with('success', "Extended expiration for {$count} enrollments by {$days} days.");
    }

    public function bulkExport(Request $request)
    {
        $enrollmentIds = $request->input('enrollment_ids', []);

        $enrollments = \App\Models\UserCourseEnrollment::with(['user', 'course'])
            ->whereIn('id', $enrollmentIds)
            ->get();

        $filename = $this->segmentService->exportToCSV($enrollments);

        return response()->download(storage_path('app/exports/'.$filename))->deleteFileAfterSend();
    }

    public function saveSegment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'filters' => 'required|array',
        ]);

        $segment = $this->segmentService->saveSegment(
            $request->name,
            $request->filters,
            auth()->id()
        );

        return back()->with('success', 'Segment saved successfully.');
    }

    public function deleteSegment(EnrollmentSegment $segment)
    {
        if ($segment->is_system) {
            return back()->with('error', 'Cannot delete system segments.');
        }

        $segment->delete();

        return back()->with('success', 'Segment deleted successfully.');
    }
}
