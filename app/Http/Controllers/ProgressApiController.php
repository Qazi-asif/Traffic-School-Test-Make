<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProgressApiController extends Controller
{
    public function getProgress(Request $request, $enrollmentId)
    {
        try {
            $user = Auth::user();
            
            $enrollment = DB::table("user_course_enrollments")
                ->where("id", $enrollmentId)
                ->where("user_id", $user->id)
                ->first();
            
            if (!$enrollment) {
                return response()->json(["error" => "Enrollment not found"], 404);
            }
            
            // Check final exam status
            $finalExamResult = DB::table("final_exam_results")
                ->where("enrollment_id", $enrollmentId)
                ->orderBy("created_at", "desc")
                ->first();
            
            $finalExamPassed = $finalExamResult && $finalExamResult->passed;
            
            // If final exam is passed, progress should be 100%
            if ($finalExamPassed) {
                // Update progress if not already 100%
                if ($enrollment->progress_percentage < 100) {
                    DB::table("user_course_enrollments")
                        ->where("id", $enrollmentId)
                        ->update([
                            "progress_percentage" => 100,
                            "status" => "completed",
                            "completed_at" => $finalExamResult->created_at ?? now()
                        ]);
                }
                
                return response()->json([
                    "enrollment_id" => $enrollmentId,
                    "progress_percentage" => 100,
                    "status" => "completed",
                    "final_exam_passed" => true,
                    "final_exam_score" => $finalExamResult->score ?? null
                ]);
            }
            
            // Calculate progress based on chapters
            $totalChapters = DB::table("chapters")
                ->where("course_id", $enrollment->course_id)
                ->where("course_table", $enrollment->course_table)
                ->where("is_active", true)
                ->count();
            
            $completedChapters = DB::table("user_course_progress")
                ->where("enrollment_id", $enrollmentId)
                ->where("is_completed", true)
                ->distinct("chapter_id")
                ->count("chapter_id");
            
            $chapterProgress = $totalChapters > 0 ? ($completedChapters / $totalChapters) * 90 : 0;
            $examProgress = $finalExamResult ? 10 : 0;
            $totalProgress = min(100, $chapterProgress + $examProgress);
            
            // Update progress in database
            DB::table("user_course_enrollments")
                ->where("id", $enrollmentId)
                ->update(["progress_percentage" => $totalProgress]);
            
            return response()->json([
                "enrollment_id" => $enrollmentId,
                "progress_percentage" => $totalProgress,
                "status" => $enrollment->status,
                "completed_chapters" => $completedChapters,
                "total_chapters" => $totalChapters,
                "final_exam_passed" => false,
                "final_exam_attempted" => $finalExamResult ? true : false,
                "final_exam_score" => $finalExamResult->score ?? null
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Progress API error: " . $e->getMessage());
            return response()->json(["error" => "Failed to get progress"], 500);
        }
    }
}