<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\ChapterTimer;
use App\Models\Course;
use App\Models\FloridaCourse;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    public function list()
    {
        try {
            $timers = ChapterTimer::orderBy('chapter_id')->get();

            $result = $timers->map(function ($timer) {
                // Get chapter and course info
                $chapter = Chapter::find($timer->chapter_id);

                if ($chapter) {
                    // Try to get course from both tables
                    $course = Course::find($chapter->course_id);
                    if (! $course) {
                        $course = FloridaCourse::find($chapter->course_id);
                    }

                    return [
                        'id' => $timer->id,
                        'chapter_id' => $timer->chapter_id,
                        'chapter_type' => $timer->chapter_type,
                        'required_time_minutes' => $timer->required_time_minutes,
                        'is_enabled' => $timer->is_enabled,
                        'allow_pause' => $timer->allow_pause,
                        'bypass_for_admin' => $timer->bypass_for_admin,
                        'chapter' => [
                            'id' => $chapter->id,
                            'title' => $chapter->title,
                            'course_id' => $chapter->course_id,
                            'course' => $course ? [
                                'id' => $course->id,
                                'title' => $course->title,
                            ] : null,
                            'course_name' => $course ? $course->title : 'Unknown',
                        ],
                    ];
                }

                return null;
            })->filter();

            return response()->json($result->values());
        } catch (\Exception $e) {
            \Log::error('Error loading timers: '.$e->getMessage());

            return response()->json(['error' => 'Failed to load timers'], 500);
        }
    }

    public function configure(Request $request)
    {
        try {
            \Log::info('Timer configure request:', $request->all());

            $validated = $request->validate([
                'chapter_id' => 'required|integer',
                'chapter_type' => 'required|string',
                'required_time_minutes' => 'required|integer|min:1',
                'is_enabled' => 'boolean',
                'allow_pause' => 'boolean',
                'bypass_for_admin' => 'boolean',
            ]);

            // Ensure chapter_type is valid
            if (! in_array($validated['chapter_type'], ['chapters', 'florida_chapters'])) {
                $validated['chapter_type'] = 'chapters';
            }

            // Check if timer already exists for this chapter
            $timer = ChapterTimer::where('chapter_id', $validated['chapter_id'])
                ->where('chapter_type', $validated['chapter_type'])
                ->first();

            if ($timer) {
                $timer->update($validated);
            } else {
                $timer = ChapterTimer::create($validated);
            }

            return response()->json([
                'success' => true,
                'timer' => $timer,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Timer validation error: '.json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error configuring timer: '.$e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => 'Failed to configure timer',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function toggle($id)
    {
        try {
            $timer = ChapterTimer::findOrFail($id);
            $timer->is_enabled = ! $timer->is_enabled;
            $timer->save();

            return response()->json([
                'success' => true,
                'timer' => $timer,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error toggling timer: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to toggle timer',
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $timer = ChapterTimer::findOrFail($id);
            $timer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Timer deleted successfully',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting timer: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete timer',
            ], 500);
        }
    }

    public function getForChapter($chapterId, Request $request)
    {
        try {
            $chapterType = $request->input('type', 'chapters');

            $timer = ChapterTimer::where('chapter_id', $chapterId)
                ->where('chapter_type', $chapterType)
                ->where('is_enabled', true)
                ->first();

            if (! $timer) {
                return response()->json(['timer' => null]);
            }

            return response()->json(['timer' => $timer]);
        } catch (\Exception $e) {
            \Log::error('Error getting timer for chapter: '.$e->getMessage());

            return response()->json(['error' => 'Failed to get timer'], 500);
        }
    }
}
