<?php

namespace App\Http\Controllers\Admin\Delaware;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\FileUpload;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        $query = Chapter::with(['course'])
            ->whereHas('course', function($q) {
                $q->where('state', 'delaware');
            })
            ->orderBy('order_index')
            ->orderBy('created_at', 'desc');

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $chapters = $query->paginate(20);
        $courses = Course::where('state', 'delaware')->orderBy('title')->get();

        return view('admin.delaware.chapters.index', compact('chapters', 'courses'));
    }

    public function create(Request $request)
    {
        $courseId = $request->get('course_id');
        $course = $courseId ? Course::findOrFail($courseId) : null;
        $courses = Course::where('state', 'delaware')->orderBy('title')->get();

        return view('admin.delaware.chapters.create', compact('course', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'order_index' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $course = Course::findOrFail($request->course_id);
        if ($course->state !== 'delaware') {
            return redirect()->back()->with('error', 'Invalid course selection.');
        }

        $orderIndex = $request->order_index;
        if (is_null($orderIndex)) {
            $orderIndex = Chapter::where('course_id', $request->course_id)->max('order_index') + 1;
        }

        $chapter = Chapter::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'content' => $request->content,
            'order_index' => $orderIndex,
            'duration_minutes' => $request->duration_minutes,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.delaware.chapters.show', $chapter)
            ->with('success', 'Delaware chapter created successfully.');
    }

    public function show(Chapter $chapter)
    {
        $chapter->load(['course', 'questions', 'media']);
        
        $stats = [
            'total_questions' => $chapter->questions->count(),
            'total_media' => FileUpload::where('chapter_id', $chapter->id)->count(),
            'estimated_duration' => $chapter->duration_minutes ?? 'Not set',
        ];

        return view('admin.delaware.chapters.show', compact('chapter', 'stats'));
    }

    public function edit(Chapter $chapter)
    {
        $courses = Course::where('state', 'delaware')->orderBy('title')->get();
        return view('admin.delaware.chapters.edit', compact('chapter', 'courses'));
    }

    public function update(Request $request, Chapter $chapter)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'order_index' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $course = Course::findOrFail($request->course_id);
        if ($course->state !== 'delaware') {
            return redirect()->back()->with('error', 'Invalid course selection.');
        }

        $chapter->update([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'content' => $request->content,
            'order_index' => $request->order_index ?? $chapter->order_index,
            'duration_minutes' => $request->duration_minutes,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.delaware.chapters.show', $chapter)
            ->with('success', 'Delaware chapter updated successfully.');
    }

    public function destroy(Chapter $chapter)
    {
        $chapter->delete();

        return redirect()->route('admin.delaware.chapters.index')
            ->with('success', 'Delaware chapter deleted successfully.');
    }
}