<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseContentController extends Controller
{
    public function index()
    {
        $courses = Course::with(['chapters' => function($query) {
            $query->orderBy('chapter_number');
        }])->get();

        return view('admin.course-content.index', compact('courses'));
    }

    public function show(Course $course)
    {
        $course->load(['chapters' => function($query) {
            $query->orderBy('chapter_number');
        }]);

        return view('admin.course-content.show', compact('course'));
    }

    public function createChapter(Course $course)
    {
        return view('admin.course-content.create-chapter', compact('course'));
    }

    public function storeChapter(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'chapter_number' => 'required|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_quiz' => 'boolean',
            'quiz_questions' => 'nullable|json',
            'passing_score' => 'nullable|integer|min:0|max:100',
        ]);

        $validated['course_id'] = $course->id;
        
        Chapter::create($validated);

        return redirect()->route('admin.course-content.show', $course)
                        ->with('success', 'Chapter created successfully');
    }

    public function editChapter(Course $course, Chapter $chapter)
    {
        return view('admin.course-content.edit-chapter', compact('course', 'chapter'));
    }

    public function updateChapter(Request $request, Course $course, Chapter $chapter)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'chapter_number' => 'required|integer|min:1',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_quiz' => 'boolean',
            'quiz_questions' => 'nullable|json',
            'passing_score' => 'nullable|integer|min:0|max:100',
        ]);

        $chapter->update($validated);

        return redirect()->route('admin.course-content.show', $course)
                        ->with('success', 'Chapter updated successfully');
    }

    public function destroyChapter(Course $course, Chapter $chapter)
    {
        $chapter->delete();

        return redirect()->route('admin.course-content.show', $course)
                        ->with('success', 'Chapter deleted successfully');
    }

    public function reorderChapters(Request $request, Course $course)
    {
        $validated = $request->validate([
            'chapters' => 'required|array',
            'chapters.*.id' => 'required|exists:chapters,id',
            'chapters.*.chapter_number' => 'required|integer|min:1',
        ]);

        foreach ($validated['chapters'] as $chapterData) {
            Chapter::where('id', $chapterData['id'])
                   ->update(['chapter_number' => $chapterData['chapter_number']]);
        }

        return response()->json(['success' => true]);
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif|max:5120',
        ]);

        $path = $request->file('image')->store('course-content', 'public');
        $url = Storage::url($path);

        return response()->json([
            'success' => true,
            'url' => $url,
            'path' => $path
        ]);
    }
}