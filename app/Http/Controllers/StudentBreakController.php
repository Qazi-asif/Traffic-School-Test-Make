<?php

namespace App\Http\Controllers;

use App\Models\ChapterBreak;
use App\Models\StudentBreakSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentBreakController extends Controller
{
    /**
     * Check if student needs to take a break after completing a chapter
     */
    public function checkBreakRequired(Request $request)
    {
        $enrollmentId = $request->get('enrollment_id');
        $chapterId = $request->get('chapter_id');
        
        if (!$enrollmentId || !$chapterId) {
            return response()->json(['break_required' => false]);
        }

        // Get enrollment details
        $enrollment = DB::table('user_course_enrollments as e')
            ->leftJoin('courses as c', 'e.course_id', '=', 'c.id')
            ->leftJoin('florida_courses as fc', 'e.course_id', '=', 'fc.id')
            ->select(
                'e.*',
                DB::raw('COALESCE(c.id, fc.id) as course_id'),
                DB::raw('CASE WHEN c.id IS NOT NULL THEN "courses" ELSE "florida_courses" END as course_type')
            )
            ->where('e.id', $enrollmentId)
            ->where('e.user_id', Auth::id())
            ->first();

        if (!$enrollment) {
            return response()->json(['break_required' => false]);
        }

        // Check if there's an active break after this chapter
        $chapterBreak = ChapterBreak::where('course_id', $enrollment->course_id)
            ->where('course_type', $enrollment->course_type)
            ->where('after_chapter_id', $chapterId)
            ->where('is_active', true)
            ->first();

        if (!$chapterBreak) {
            return response()->json(['break_required' => false]);
        }

        // Check if student already has an active break session for this break
        $existingSession = StudentBreakSession::where('user_id', Auth::id())
            ->where('enrollment_id', $enrollmentId)
            ->where('chapter_break_id', $chapterBreak->id)
            ->where('is_completed', false)
            ->where('was_skipped', false)
            ->first();

        if ($existingSession) {
            return response()->json([
                'break_required' => true,
                'break_session_id' => $existingSession->id,
                'redirect_url' => route('student.break.show', $existingSession->id)
            ]);
        }

        // Create new break session
        $breakSession = StudentBreakSession::create([
            'user_id' => Auth::id(),
            'enrollment_id' => $enrollmentId,
            'chapter_break_id' => $chapterBreak->id,
            'break_started_at' => Carbon::now(),
            'break_ends_at' => Carbon::now()->addMinutes($chapterBreak->total_duration_minutes),
        ]);

        return response()->json([
            'break_required' => true,
            'break_session_id' => $breakSession->id,
            'redirect_url' => route('student.break.show', $breakSession->id)
        ]);
    }

    /**
     * Show the break screen to student
     */
    public function show($sessionId)
    {
        $breakSession = StudentBreakSession::with(['chapterBreak', 'enrollment'])
            ->where('id', $sessionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // If break is already completed or skipped, redirect to course
        if ($breakSession->is_completed || $breakSession->was_skipped) {
            return redirect()->route('course.continue', [
                'enrollment_id' => $breakSession->enrollment_id
            ])->with('info', 'Break already completed.');
        }

        return view('student.break.show', compact('breakSession'));
    }

    /**
     * Get break status (for AJAX polling)
     */
    public function status($sessionId)
    {
        $breakSession = StudentBreakSession::where('id', $sessionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return response()->json([
            'is_completed' => $breakSession->is_completed,
            'was_skipped' => $breakSession->was_skipped,
            'is_expired' => $breakSession->is_expired,
            'remaining_minutes' => $breakSession->remaining_minutes,
            'formatted_remaining_time' => $breakSession->formatted_remaining_time,
            'can_continue' => $breakSession->is_expired || $breakSession->is_completed || $breakSession->was_skipped,
        ]);
    }

    /**
     * Complete the break (when time is up or student chooses to continue)
     */
    public function complete($sessionId)
    {
        $breakSession = StudentBreakSession::where('id', $sessionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check if break time has elapsed or if it's optional
        if (!$breakSession->is_expired && $breakSession->chapterBreak->is_mandatory) {
            return response()->json([
                'success' => false,
                'message' => 'Break time has not elapsed yet.'
            ]);
        }

        $breakSession->markCompleted();

        return response()->json([
            'success' => true,
            'redirect_url' => route('course.continue', [
                'enrollment_id' => $breakSession->enrollment_id
            ])
        ]);
    }

    /**
     * Skip the break (only if it's optional)
     */
    public function skip($sessionId)
    {
        $breakSession = StudentBreakSession::where('id', $sessionId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Check if break is skippable
        if ($breakSession->chapterBreak->is_mandatory) {
            return response()->json([
                'success' => false,
                'message' => 'This break is mandatory and cannot be skipped.'
            ]);
        }

        $breakSession->markSkipped();

        return response()->json([
            'success' => true,
            'redirect_url' => route('course.continue', [
                'enrollment_id' => $breakSession->enrollment_id
            ])
        ]);
    }

    /**
     * Get active break sessions for a user (admin view)
     */
    public function adminIndex(Request $request)
    {
        $query = StudentBreakSession::with(['user', 'chapterBreak', 'enrollment'])
            ->orderBy('break_started_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'completed':
                    $query->completed();
                    break;
                case 'expired':
                    $query->where('is_completed', false)
                          ->where('was_skipped', false)
                          ->where('break_ends_at', '<', Carbon::now());
                    break;
            }
        }

        $breakSessions = $query->paginate(20);

        return view('admin.break-sessions.index', compact('breakSessions'));
    }
}