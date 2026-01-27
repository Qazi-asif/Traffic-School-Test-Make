<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinalExamResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinalExamGradingController extends Controller
{
    /**
     * Display list of exams needing grading
     */
    public function index(Request $request)
    {
        $query = FinalExamResult::with(['user', 'course', 'enrollment'])
            ->orderBy('exam_completed_at', 'desc');

        // Filter by grading status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'pending':
                    $query->needingGrading();
                    break;
                case 'expired':
                    $query->gradingExpired();
                    break;
                case 'completed':
                    $query->where('grading_completed', true);
                    break;
            }
        }

        // Filter by course
        if ($request->has('course_id') && !empty($request->course_id)) {
            $query->where('course_id', $request->course_id);
        }

        // Search by student name or email
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                  ->orWhere('last_name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $results = $query->paginate(20);

        // Get courses for filter dropdown
        $courses = DB::table('courses')
            ->select('id', 'title', 'state_code')
            ->union(
                DB::table('florida_courses')
                    ->select('id', 'title', 'state_code')
            )
            ->orderBy('title')
            ->get();

        // Get statistics
        $stats = [
            'pending_grading' => FinalExamResult::needingGrading()->count(),
            'expired_grading' => FinalExamResult::gradingExpired()->count(),
            'completed_grading' => FinalExamResult::where('grading_completed', true)->count(),
            'total_results' => FinalExamResult::count(),
        ];

        return view('admin.final-exam-grading.index', compact('results', 'courses', 'stats'));
    }

    /**
     * Show detailed grading view for a specific result
     */
    public function show($resultId)
    {
        $result = FinalExamResult::with([
            'user', 
            'course', 
            'enrollment', 
            'questionResults.question',
            'gradedBy'
        ])->findOrFail($resultId);

        // Get component scores breakdown
        $componentScores = $this->getComponentScores($result);

        // Get course details
        $courseDetails = $this->getCourseDetails($result);

        return view('admin.final-exam-grading.show', compact(
            'result',
            'componentScores',
            'courseDetails'
        ));
    }

    /**
     * Update grading for a result
     */
    public function updateGrading(Request $request, $resultId)
    {
        $request->validate([
            'instructor_notes' => 'nullable|string|max:2000',
            'override_score' => 'nullable|numeric|min:0|max:100',
            'override_status' => 'nullable|in:passed,failed,under_review',
            'generate_certificate' => 'boolean',
        ]);

        $result = FinalExamResult::findOrFail($resultId);

        // Check if grading period is still active or user is admin
        if (!$result->is_grading_period_active && !Auth::user()->hasRole('super-admin')) {
            return redirect()->back()->with('error', 'Grading period has expired.');
        }

        $updates = [
            'instructor_notes' => $request->instructor_notes,
            'graded_by' => Auth::id(),
            'graded_at' => Carbon::now(),
            'grading_completed' => true,
        ];

        // Handle score override
        if ($request->has('override_score') && $request->override_score !== null) {
            $updates['overall_score'] = $request->override_score;
            $updates['grade_letter'] = $this->getGradeLetter($request->override_score);
            $updates['is_passing'] = $request->override_score >= $result->passing_threshold;
        }

        // Handle status override
        if ($request->has('override_status') && $request->override_status !== null) {
            $updates['status'] = $request->override_status;
            $updates['is_passing'] = $request->override_status === 'passed';
        }

        // Handle certificate generation
        if ($request->has('generate_certificate') && $request->generate_certificate && $updates['is_passing']) {
            $updates['certificate_generated'] = true;
            $updates['certificate_number'] = $this->generateCertificateNumber($result);
            $updates['certificate_issued_at'] = Carbon::now();
        }

        $result->update($updates);

        return redirect()->route('admin.final-exam-grading.index')
            ->with('success', 'Grading completed successfully!');
    }

    /**
     * Bulk update multiple results
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'result_ids' => 'required|array',
            'result_ids.*' => 'integer|exists:final_exam_results,id',
            'bulk_action' => 'required|in:approve_all,mark_review,complete_grading',
            'bulk_notes' => 'nullable|string|max:1000',
        ]);

        $results = FinalExamResult::whereIn('id', $request->result_ids)->get();
        $updated = 0;

        foreach ($results as $result) {
            $updates = [
                'graded_by' => Auth::id(),
                'graded_at' => Carbon::now(),
                'grading_completed' => true,
            ];

            if ($request->bulk_notes) {
                $updates['instructor_notes'] = $request->bulk_notes;
            }

            switch ($request->bulk_action) {
                case 'approve_all':
                    $updates['status'] = 'passed';
                    $updates['is_passing'] = true;
                    if (!$result->certificate_generated) {
                        $updates['certificate_generated'] = true;
                        $updates['certificate_number'] = $this->generateCertificateNumber($result);
                        $updates['certificate_issued_at'] = Carbon::now();
                    }
                    break;
                    
                case 'mark_review':
                    $updates['status'] = 'under_review';
                    $updates['grading_completed'] = false; // Keep under review
                    break;
                    
                case 'complete_grading':
                    // Just mark as grading completed, keep existing status
                    break;
            }

            $result->update($updates);
            $updated++;
        }

        return redirect()->back()->with('success', "Updated {$updated} exam results.");
    }

    /**
     * Quick grade a result (AJAX)
     */
    public function quickGrade(Request $request, $resultId)
    {
        $request->validate([
            'status' => 'required|in:passed,failed,under_review',
        ]);

        $result = FinalExamResult::findOrFail($resultId);

        // Check if grading period is still active or user is admin
        if (!$result->is_grading_period_active && !Auth::user()->hasRole('super-admin')) {
            return response()->json(['success' => false, 'error' => 'Grading period has expired.']);
        }

        $updates = [
            'status' => $request->status,
            'is_passing' => $request->status === 'passed',
            'graded_by' => Auth::id(),
            'graded_at' => Carbon::now(),
            'grading_completed' => $request->status !== 'under_review',
        ];

        // Generate certificate if passing
        if ($request->status === 'passed' && !$result->certificate_generated) {
            $updates['certificate_generated'] = true;
            $updates['certificate_number'] = $this->generateCertificateNumber($result);
            $updates['certificate_issued_at'] = Carbon::now();
        }

        $result->update($updates);

        return response()->json(['success' => true]);
    }

    /**
     * Get component scores breakdown
     */
    private function getComponentScores($result)
    {
        return [
            'quiz_average' => [
                'score' => $result->quiz_average ?? 0,
                'weight' => 30,
                'weighted_score' => ($result->quiz_average ?? 0) * 0.3
            ],
            'free_response' => [
                'score' => $result->free_response_score ?? 0,
                'weight' => 20,
                'weighted_score' => ($result->free_response_score ?? 0) * 0.2
            ],
            'final_exam' => [
                'score' => $result->final_exam_score ?? 0,
                'weight' => 50,
                'weighted_score' => ($result->final_exam_score ?? 0) * 0.5
            ]
        ];
    }

    /**
     * Get course details
     */
    private function getCourseDetails($result)
    {
        if ($result->course_type === 'florida_courses') {
            return DB::table('florida_courses')->where('id', $result->course_id)->first();
        } else {
            return DB::table('courses')->where('id', $result->course_id)->first();
        }
    }

    /**
     * Get grade letter based on score
     */
    private function getGradeLetter($score)
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'F';
    }

    /**
     * Generate certificate number
     */
    private function generateCertificateNumber($result)
    {
        $prefix = strtoupper($result->course_type === 'florida_courses' ? 'FL' : 'GEN');
        $timestamp = Carbon::now()->format('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$timestamp}-{$random}";
    }
}