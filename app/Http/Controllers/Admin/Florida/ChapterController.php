<?php

namespace App\Http\Controllers\Admin\Florida;

use App\Http\Controllers\Controller;
use App\Models\FloridaCourse;
use App\Models\Chapter;
use App\Models\FileUpload;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function index(Request $request)
    {
        $query = Chapter::with(['course'])
            ->whereHas('course', function($q) {
                // Only Florida courses (assuming florida_courses table)
                $q->whereRaw('1=1'); // Placeholder - adjust based on your schema
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
        $courses = FloridaCourse::orderBy('title')->get();

        return view('admin.florida.chapters.index', compact('chapters', 'courses'));
    }

    public function create(Request $request)
    {
        $courseId = $request->get('course_id');
        $course = $courseId ? FloridaCourse::findOrFail($courseId) : null;
        $courses = FloridaCourse::orderBy('title')->get();

        return view('admin.florida.chapters.create', compact('course', 'courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:florida_courses,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'order_index' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // Auto-assign order_index if not provided
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

        return redirect()->route('admin.florida.chapters.show', $chapter)
            ->with('success', 'Chapter created successfully.');
    }

    public function show(Chapter $chapter)
    {
        $chapter->load(['course', 'questions', 'media']);
        
        $stats = [
            'total_questions' => $chapter->questions->count(),
            'total_media' => FileUpload::where('chapter_id', $chapter->id)->count(),
            'estimated_duration' => $chapter->duration_minutes ?? 'Not set',
        ];

        return view('admin.florida.chapters.show', compact('chapter', 'stats'));
    }

    public function edit(Chapter $chapter)
    {
        $courses = FloridaCourse::orderBy('title')->get();
        return view('admin.florida.chapters.edit', compact('chapter', 'courses'));
    }

    public function update(Request $request, Chapter $chapter)
    {
        $request->validate([
            'course_id' => 'required|exists:florida_courses,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'order_index' => 'nullable|integer|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $chapter->update([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'content' => $request->content,
            'order_index' => $request->order_index ?? $chapter->order_index,
            'duration_minutes' => $request->duration_minutes,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.florida.chapters.show', $chapter)
            ->with('success', 'Chapter updated successfully.');
    }

    public function destroy(Chapter $chapter)
    {
        $chapter->delete();

        return redirect()->route('admin.florida.chapters.index')
            ->with('success', 'Chapter deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'chapters' => 'required|array',
            'chapters.*.id' => 'required|integer|exists:chapters,id',
            'chapters.*.order_index' => 'required|integer|min:0',
        ]);

        foreach ($request->chapters as $chapterData) {
            Chapter::where('id', $chapterData['id'])
                ->update(['order_index' => $chapterData['order_index']]);
        }

        return response()->json(['success' => true]);
    }

    public function uploadMedia(Request $request, Chapter $chapter)
    {
        $request->validate([
            'files.*' => 'required|file|max:102400',
            'file_type' => 'required|in:video,document,image,audio',
        ]);

        $uploadedFiles = [];

        foreach ($request->file('files') as $file) {
            // Use the FileUploadController logic
            $fileUpload = app(\App\Http\Controllers\Admin\FileUploadController::class)
                ->processFileUpload($file, $request->merge([
                    'state' => 'florida',
                    'chapter_id' => $chapter->id,
                    'course_id' => $chapter->course_id,
                ]));

            $uploadedFiles[] = $fileUpload;
        }

        return redirect()->route('admin.florida.chapters.show', $chapter)
            ->with('success', count($uploadedFiles) . ' files uploaded successfully.');
    }
}