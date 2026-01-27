<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FreeResponseQuizPlacement;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FreeResponseQuizPlacementController extends Controller
{
    /**
     * Display quiz placements for a course
     */
    public function index(Request $request)
    {
        $courseId = $request->get('course_id');
        
        // Get available courses
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        // If no course selected, default to first available course
        if (!$courseId && $courses->isNotEmpty()) {
            $courseId = $courses->first()->id;
        }

        // Get quiz placements for the course
        $placements = FreeResponseQuizPlacement::where('course_id', $courseId)
            ->orderBy('order_index')
            ->get();

        // Get chapters for the course
        $chapters = Chapter::where('course_id', $courseId)
            ->orderBy('order_index')
            ->get();

        return view('admin.free-response-quiz-placements.index', compact(
            'placements', 
            'courses', 
            'courseId',
            'chapters'
        ));
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $courseId = $request->get('course_id');

        // Get available courses
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        // Get chapters for the selected course
        $chapters = collect();
        if ($courseId) {
            $chapters = Chapter::where('course_id', $courseId)
                ->orderBy('order_index')
                ->get();
        }

        return view('admin.free-response-quiz-placements.create', compact('courses', 'courseId', 'chapters'));
    }

    /**
     * Store a new quiz placement
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'after_chapter_id' => 'nullable|integer',
            'quiz_title' => 'required|string|max:255',
            'quiz_description' => 'nullable|string|max:1000',
            'is_mandatory' => 'nullable|boolean',
            'order_index' => 'required|integer|min:1',
        ]);

        try {
            $validated['is_mandatory'] = $request->has('is_mandatory') ? true : false;

            FreeResponseQuizPlacement::create($validated);

            return redirect()->route('admin.free-response-quiz-placements.index', ['course_id' => $validated['course_id']])
                ->with('success', 'Quiz placement created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating quiz placement: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to create quiz placement: ' . $e->getMessage()]);
        }
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $placement = FreeResponseQuizPlacement::findOrFail($id);

        // Get available courses
        $regularCourses = DB::table('courses')
            ->select('id', 'title', 'state_code', DB::raw("'courses' as table_type"))
            ->orderBy('title')
            ->get();

        $floridaCourses = DB::table('florida_courses')
            ->select('id', 'title', 'state_code', DB::raw("'florida_courses' as table_type"))
            ->orderBy('title')
            ->get();

        $courses = $regularCourses->concat($floridaCourses)->sortBy('title');

        // Get chapters for the course
        $chapters = Chapter::where('course_id', $placement->course_id)
            ->orderBy('order_index')
            ->get();

        return view('admin.free-response-quiz-placements.edit', compact('placement', 'courses', 'chapters'));
    }

    /**
     * Update quiz placement
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'course_id' => 'required|integer',
            'after_chapter_id' => 'nullable|integer',
            'quiz_title' => 'required|string|max:255',
            'quiz_description' => 'nullable|string|max:1000',
            'is_mandatory' => 'nullable|boolean',
            'order_index' => 'required|integer|min:1',
        ]);

        try {
            $placement = FreeResponseQuizPlacement::findOrFail($id);
            $validated['is_mandatory'] = $request->has('is_mandatory') ? true : false;

            $placement->update($validated);

            return redirect()->route('admin.free-response-quiz-placements.index', ['course_id' => $validated['course_id']])
                ->with('success', 'Quiz placement updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating quiz placement: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update quiz placement: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete quiz placement
     */
    public function destroy($id)
    {
        try {
            $placement = FreeResponseQuizPlacement::findOrFail($id);
            $courseId = $placement->course_id;
            $placement->delete();

            return redirect()->route('admin.free-response-quiz-placements.index', ['course_id' => $courseId])
                ->with('success', 'Quiz placement deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting quiz placement: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete quiz placement: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        try {
            $placement = FreeResponseQuizPlacement::findOrFail($id);
            $placement->update(['is_active' => !$placement->is_active]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz placement status updated successfully',
                'is_active' => $placement->is_active
            ]);
        } catch (\Exception $e) {
            Log::error('Error toggling placement status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Failed to update placement status'
            ], 500);
        }
    }
}