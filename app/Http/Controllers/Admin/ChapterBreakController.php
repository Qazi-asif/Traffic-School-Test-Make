<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChapterBreak;
use App\Models\Course;
use App\Models\FloridaCourse;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChapterBreakController extends Controller
{
    /**
     * Display breaks for a specific course
     */
    public function index(Request $request, $courseType, $courseId)
    {
        // Validate course type
        if (!in_array($courseType, ['courses', 'florida-courses'])) {
            return redirect()->back()->with('error', 'Invalid course type.');
        }

        $courseTable = $courseType === 'florida-courses' ? 'florida_courses' : 'courses';
        
        // Get course details
        $course = DB::table($courseTable)->where('id', $courseId)->first();
        if (!$course) {
            return redirect()->back()->with('error', 'Course not found.');
        }

        // Get chapters for this course
        $chapters = DB::table('chapters')
            ->where('course_id', $courseId)
            ->where('course_table', $courseTable)
            ->orderBy('order_index')
            ->get();

        // Get existing breaks for this course
        $breaks = ChapterBreak::where('course_id', $courseId)
            ->where('course_type', $courseTable)
            ->with('chapter')
            ->orderBy('after_chapter_id')
            ->get();

        return view('admin.chapter-breaks.index', compact(
            'course',
            'courseType',
            'courseId',
            'chapters',
            'breaks'
        ));
    }

    /**
     * Show form to create a new break
     */
    public function create($courseType, $courseId)
    {
        // Validate course type
        if (!in_array($courseType, ['courses', 'florida-courses'])) {
            return redirect()->back()->with('error', 'Invalid course type.');
        }

        $courseTable = $courseType === 'florida-courses' ? 'florida_courses' : 'courses';
        
        // Get course details
        $course = DB::table($courseTable)->where('id', $courseId)->first();
        if (!$course) {
            return redirect()->back()->with('error', 'Course not found.');
        }

        // Get chapters for this course
        $chapters = DB::table('chapters')
            ->where('course_id', $courseId)
            ->where('course_table', $courseTable)
            ->orderBy('order_index')
            ->get();

        return view('admin.chapter-breaks.create', compact(
            'course',
            'courseType',
            'courseId',
            'chapters'
        ));
    }

    /**
     * Store a new break
     */
    public function store(Request $request, $courseType, $courseId)
    {
        $request->validate([
            'after_chapter_id' => 'required|integer',
            'break_title' => 'required|string|max:255',
            'break_message' => 'nullable|string|max:1000',
            'break_duration_hours' => 'required|integer|min:0|max:24',
            'break_duration_minutes' => 'required|integer|min:0|max:59',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Validate that we have at least some duration
        if ($request->break_duration_hours == 0 && $request->break_duration_minutes == 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Break duration must be at least 1 minute.');
        }

        $courseTable = $courseType === 'florida-courses' ? 'florida_courses' : 'courses';

        // Check if break already exists for this chapter
        $existingBreak = ChapterBreak::where('course_id', $courseId)
            ->where('course_type', $courseTable)
            ->where('after_chapter_id', $request->after_chapter_id)
            ->first();

        if ($existingBreak) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A break already exists after this chapter.');
        }

        ChapterBreak::create([
            'course_id' => $courseId,
            'course_type' => $courseTable,
            'after_chapter_id' => $request->after_chapter_id,
            'break_title' => $request->break_title,
            'break_message' => $request->break_message,
            'break_duration_hours' => $request->break_duration_hours,
            'break_duration_minutes' => $request->break_duration_minutes,
            'is_mandatory' => $request->has('is_mandatory'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.chapter-breaks.index', [$courseType, $courseId])
            ->with('success', 'Chapter break created successfully!');
    }

    /**
     * Show form to edit a break
     */
    public function edit($courseType, $courseId, $breakId)
    {
        $courseTable = $courseType === 'florida-courses' ? 'florida_courses' : 'courses';
        
        $break = ChapterBreak::where('course_id', $courseId)
            ->where('course_type', $courseTable)
            ->findOrFail($breakId);

        // Get course details
        $course = DB::table($courseTable)->where('id', $courseId)->first();
        
        // Get chapters for this course
        $chapters = DB::table('chapters')
            ->where('course_id', $courseId)
            ->where('course_table', $courseTable)
            ->orderBy('order_index')
            ->get();

        return view('admin.chapter-breaks.edit', compact(
            'course',
            'courseType',
            'courseId',
            'chapters',
            'break'
        ));
    }

    /**
     * Update a break
     */
    public function update(Request $request, $courseType, $courseId, $breakId)
    {
        $request->validate([
            'after_chapter_id' => 'required|integer',
            'break_title' => 'required|string|max:255',
            'break_message' => 'nullable|string|max:1000',
            'break_duration_hours' => 'required|integer|min:0|max:24',
            'break_duration_minutes' => 'required|integer|min:0|max:59',
            'is_mandatory' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Validate that we have at least some duration
        if ($request->break_duration_hours == 0 && $request->break_duration_minutes == 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Break duration must be at least 1 minute.');
        }

        $courseTable = $courseType === 'florida-courses' ? 'florida_courses' : 'courses';
        
        $break = ChapterBreak::where('course_id', $courseId)
            ->where('course_type', $courseTable)
            ->findOrFail($breakId);

        // Check if break already exists for this chapter (excluding current break)
        $existingBreak = ChapterBreak::where('course_id', $courseId)
            ->where('course_type', $courseTable)
            ->where('after_chapter_id', $request->after_chapter_id)
            ->where('id', '!=', $breakId)
            ->first();

        if ($existingBreak) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A break already exists after this chapter.');
        }

        $break->update([
            'after_chapter_id' => $request->after_chapter_id,
            'break_title' => $request->break_title,
            'break_message' => $request->break_message,
            'break_duration_hours' => $request->break_duration_hours,
            'break_duration_minutes' => $request->break_duration_minutes,
            'is_mandatory' => $request->has('is_mandatory'),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.chapter-breaks.index', [$courseType, $courseId])
            ->with('success', 'Chapter break updated successfully!');
    }

    /**
     * Delete a break
     */
    public function destroy($courseType, $courseId, $breakId)
    {
        $courseTable = $courseType === 'florida-courses' ? 'florida_courses' : 'courses';
        
        $break = ChapterBreak::where('course_id', $courseId)
            ->where('course_type', $courseTable)
            ->findOrFail($breakId);

        $break->delete();

        return redirect()->route('admin.chapter-breaks.index', [$courseType, $courseId])
            ->with('success', 'Chapter break deleted successfully!');
    }

    /**
     * Toggle break active status
     */
    public function toggleActive($courseType, $courseId, $breakId)
    {
        $courseTable = $courseType === 'florida-courses' ? 'florida_courses' : 'courses';
        
        $break = ChapterBreak::where('course_id', $courseId)
            ->where('course_type', $courseTable)
            ->findOrFail($breakId);

        $break->update(['is_active' => !$break->is_active]);

        $status = $break->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Chapter break {$status} successfully!");
    }
}